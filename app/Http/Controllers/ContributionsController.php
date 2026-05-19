<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContributionsController extends Controller
{
    public function index(): View
    {
        $role = strtolower(Auth::user()->role?->role_name ?? 'member');

        $contributions = Contribution::query()
            ->with(['member', 'recorder'])
            ->when(! in_array($role, ['admin', 'treasurer'], true), fn ($query) => $query->where('user_id', Auth::id()))
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->get();

        $members = User::with('role')
            ->orderBy('full_name')
            ->get();

        // Get monthly contribution data
        $monthlyData = $this->getMonthlyContributionData();
        $trendData = $this->getContributionTrendData();

        return view('pages.contributions.index', [
            'contributions' => $contributions,
            'members' => $members,
            'isTreasuryStaff' => in_array($role, ['admin', 'treasurer'], true),
            'monthlyData' => $monthlyData,
            'trendData' => $trendData,
        ]);
    }

    private function getMonthlyContributionData(): array
    {
        $last12Months = collect(range(11, 0))->map(function ($i) {
            return now()->subMonths($i)->startOfMonth();
        });

        $data = [];
        foreach ($last12Months as $month) {
            $count = Contribution::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->count();
            $data[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
            ];
        }

        return $data;
    }

    private function getContributionTrendData(): array
    {
        $last12Months = collect(range(11, 0))->map(function ($i) {
            return now()->subMonths($i)->startOfMonth();
        });

        $data = [];
        foreach ($last12Months as $month) {
            $total = Contribution::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');
            $data[] = [
                'month' => $month->format('M Y'),
                'amount' => (float) $total,
            ];
        }

        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array(strtolower(Auth::user()->role?->role_name ?? ''), ['admin', 'treasurer'], true), 403);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'contribution_month' => ['required', 'date_format:Y-m'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['mpesa', 'bank', 'card', 'cash'])],
            'reference_number' => ['required', 'string', 'max:255', 'unique:contributions,reference_number'],
            'status' => ['required', Rule::in(['paid', 'pending', 'late'])],
            'notes' => ['nullable', 'string'],
        ]);

        Contribution::create([
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'contribution_month' => $data['contribution_month'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'],
            'status' => $data['status'],
            'recorded_by' => Auth::id(),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('contributions')->with('success', 'Contribution recorded successfully.');
    }
}
