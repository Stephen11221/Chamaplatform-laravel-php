<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Investment;
use App\Models\Loan;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $role = strtolower($user->role?->role_name ?? 'member');
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $upcomingMeetings = Meeting::where('meeting_date', '>=', today())
            ->orderBy('meeting_date')
            ->orderBy('meeting_time')
            ->limit(5)
            ->get();

        $data = [
            'today' => now()->format('l, d F Y'),
            'role' => $role,
            'members_count' => User::count(),
            'meetings_count' => Meeting::where('meeting_date', '>=', today())->count(),
            'reports_count' => $this->tableCount('reports'),
            'notifications_count' => $this->tableCount('notifications'),
            'upcoming_meetings' => $upcomingMeetings,
        ];

        if ($role === 'admin') {
            $data = array_merge($data, $this->adminData($startOfMonth, $endOfMonth));
        } elseif ($role === 'treasurer') {
            $data = array_merge($data, $this->treasurerData($startOfMonth, $endOfMonth));
        } elseif ($role === 'secretary') {
            $data = array_merge($data, $this->secretaryData($startOfMonth, $endOfMonth));
        } else {
            $data = array_merge($data, $this->memberData($user->id));
        }

        $view = match ($role) {
            'admin' => 'pages.dashboards.admin',
            'treasurer' => 'pages.dashboards.treasurer',
            'secretary' => 'pages.dashboards.secretary',
            default => 'pages.dashboards.member',
        };

        return view($view, $data);
    }

    private function adminData(string $startOfMonth, string $endOfMonth): array
    {
        $recentActivity = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.module', 'activity_logs.activity', 'users.full_name as actor', 'activity_logs.created_at')
            ->orderByDesc('activity_logs.created_at')
            ->limit(5)
            ->get();

        return [
            'platform_modules_count' => 8,
            'active_members_count' => User::where('status', 'active')->count(),
            'total_balance' => $this->money($this->totalBalance()),
            'contributions_total' => $this->money(Contribution::where('status', 'paid')->sum('amount')),
            'monthly_contributions_total' => $this->money(
                Contribution::where('status', 'paid')
                    ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                    ->sum('amount')
            ),
            'loans_count' => Loan::count(),
            'active_loans_count' => Loan::where('approval_status', 'approved')
                ->where('repayment_status', 'active')
                ->count(),
            'pending_loans_count' => Loan::where('approval_status', 'pending')->count(),
            'audit_events_count' => $this->tableCount('activity_logs'),
            'investments_total' => $this->money($this->investmentTotal('amount_invested', 'initial_amount')),
            'recent_activity' => $recentActivity,
        ];
    }

    private function treasurerData(string $startOfMonth, string $endOfMonth): array
    {
        $paymentQueue = DB::table('payments')
            ->leftJoin('users', 'payments.user_id', '=', 'users.id')
            ->select('payments.category', 'payments.amount', 'payments.status', 'payments.transaction_date', 'users.full_name as member_name')
            ->where('payments.status', 'pending')
            ->whereNull('payments.deleted_at')
            ->orderBy('payments.transaction_date')
            ->limit(5)
            ->get();

        return [
            'collections_total' => $this->money(
                Contribution::where('status', 'paid')
                    ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                    ->sum('amount')
            ),
            'payouts_total' => $this->money(
                DB::table('payments')
                    ->where('payment_type', 'outbound')
                    ->where('status', 'completed')
                    ->whereNull('deleted_at')
                    ->sum('amount')
            ),
            'due_payments_count' => DB::table('payments')
                ->where('status', 'pending')
                ->whereNull('deleted_at')
                ->count(),
            'active_loans_count' => Loan::where('approval_status', 'approved')
                ->where('repayment_status', 'active')
                ->count(),
            'monthly_collections_total' => $this->money(
                Contribution::where('status', 'paid')
                    ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                    ->sum('amount')
            ),
            'reports_ready_count' => $this->tableCount('reports'),
            'payment_queue' => $paymentQueue,
        ];
    }

    private function secretaryData(string $startOfMonth, string $endOfMonth): array
    {
        $latestNotifications = DB::table('notifications')
            ->join('users', 'notifications.user_id', '=', 'users.id')
            ->select('notifications.title', 'notifications.notification_type', 'notifications.is_read', 'notifications.created_at', 'users.full_name as member_name')
            ->orderByDesc('notifications.created_at')
            ->limit(5)
            ->get();

        return [
            'active_members_count' => User::where('status', 'active')->count(),
            'new_members_this_month_count' => User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'completed_meetings_count' => Meeting::where('status', 'completed')->count(),
            'meetings_this_month_count' => Meeting::whereBetween('meeting_date', [$startOfMonth, $endOfMonth])->count(),
            'unread_notifications_count' => DB::table('notifications')->where('is_read', false)->count(),
            'latest_notifications' => $latestNotifications,
        ];
    }

    private function memberData(int $userId): array
    {
        $approvedLoanIds = Loan::where('user_id', $userId)
            ->where('approval_status', 'approved')
            ->where('repayment_status', 'active')
            ->pluck('id');

        $loanBalance = Loan::whereIn('id', $approvedLoanIds)->sum('total_repayable')
            - DB::table('loan_repayments')
                ->whereIn('loan_id', $approvedLoanIds)
                ->where('status', 'completed')
                ->whereNull('deleted_at')
                ->sum('amount_paid');

        $contributionActivities = Contribution::where('user_id', $userId)
            ->orderByDesc('payment_date')
            ->limit(3)
            ->get()
            ->map(fn (Contribution $contribution) => [
                'date' => $contribution->payment_date,
                'activity' => 'Contribution: '.$this->money($contribution->amount),
                'status' => ucfirst($contribution->status),
            ]);

        $loanActivities = Loan::where('user_id', $userId)
            ->where('approval_status', 'pending')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(fn (Loan $loan) => [
                'date' => $loan->created_at,
                'activity' => 'Loan application: '.$this->money($loan->loan_amount),
                'status' => ucfirst($loan->approval_status),
            ]);

        $paymentActivities = DB::table('payments')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderByDesc('transaction_date')
            ->limit(3)
            ->get()
            ->map(fn ($payment) => [
                'date' => $payment->transaction_date,
                'activity' => ucfirst(str_replace('_', ' ', $payment->category)).': '.$this->money($payment->amount),
                'status' => ucfirst($payment->status),
            ]);

        return [
            'my_savings_total' => $this->money(Contribution::where('user_id', $userId)->where('status', 'paid')->sum('amount')),
            'loan_balance' => $this->money(max($loanBalance, 0)),
            'my_contributions_count' => Contribution::where('user_id', $userId)->count(),
            'my_payments_count' => DB::table('payments')->where('user_id', $userId)->whereNull('deleted_at')->count(),
            'my_meetings_count' => Meeting::where('meeting_date', '>=', today())->count(),
            'my_notifications_count' => DB::table('notifications')->where('user_id', $userId)->where('is_read', false)->count(),
            'payable_loans' => Loan::where('user_id', $userId)
                ->where('approval_status', 'approved')
                ->where('repayment_status', 'active')
                ->orderByDesc('created_at')
                ->get(),
            'recent_personal_activity' => $contributionActivities
                ->concat($loanActivities)
                ->concat($paymentActivities)
                ->sortByDesc('date')
                ->take(5)
                ->values(),
        ];
    }

    private function tableCount(string $table): int
    {
        return DB::table($table)->count();
    }

    private function money(float|int|string|null $amount): string
    {
        return 'KES '.number_format((float) $amount, 2);
    }

    private function investmentTotal(string ...$preferredColumns): float
    {
        foreach ($preferredColumns as $column) {
            if (Schema::hasColumn('investments', $column)) {
                return (float) Investment::query()->sum($column);
            }
        }

        return 0.0;
    }

    private function totalBalance(): float
    {
        $contributions = (float) Contribution::where('status', 'paid')->sum('amount');
        $outboundPayments = (float) DB::table('payments')
            ->where('payment_type', 'outbound')
            ->where('status', 'completed')
            ->whereNull('deleted_at')
            ->sum('amount');

        return $contributions + $this->investmentBookValue() - $outboundPayments;
    }

    private function investmentBookValue(): float
    {
        if (Schema::hasColumn('investments', 'current_value') && Schema::hasColumn('investments', 'initial_amount')) {
            return (float) DB::table('investments')
                ->whereNull('deleted_at')
                ->sum(DB::raw('COALESCE(current_value, initial_amount)'));
        }

        return $this->investmentTotal('amount_invested', 'initial_amount');
    }
}
