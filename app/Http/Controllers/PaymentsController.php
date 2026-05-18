<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentsController extends Controller
{
    public function index(): View
    {
        $role = strtolower(Auth::user()->role?->role_name ?? 'member');
        $isTreasuryStaff = in_array($role, ['admin', 'treasurer'], true);

        $payments = Payment::query()
            ->with(['member', 'recorder'])
            ->when(! $isTreasuryStaff, fn ($query) => $query->where('user_id', Auth::id()))
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->get();

        $members = User::with('role')->orderBy('full_name')->get();
        $payableLoans = Loan::where('user_id', Auth::id())
            ->where('approval_status', 'approved')
            ->where('repayment_status', 'active')
            ->orderByDesc('created_at')
            ->get();

        $showPaymentModal = request()->boolean('make_payment') || optional(session('errors'))->any();

        return view('pages.payments.index', [
            'payments' => $payments,
            'members' => $members,
            'isTreasuryStaff' => $isTreasuryStaff,
            'payableLoans' => $payableLoans,
            'showPaymentModal' => $showPaymentModal,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = strtolower(Auth::user()->role?->role_name ?? 'member');
        $isTreasuryStaff = in_array($role, ['admin', 'treasurer'], true);

        $allowedCategories = $isTreasuryStaff
            ? ['contribution', 'loan_repayment', 'loan_disbursement', 'investment', 'expense']
            : ['contribution', 'loan_repayment', 'investment'];

        $data = $request->validate([
            'payment_type' => [$isTreasuryStaff ? 'required' : 'nullable', 'nullable', Rule::in(['inbound', 'outbound'])],
            'category' => ['required', Rule::in($allowedCategories)],
            'user_id' => [$isTreasuryStaff ? 'nullable' : 'prohibited', 'nullable', 'integer', 'exists:users,id'],
            'loan_id' => $isTreasuryStaff
                ? ['nullable', 'integer', 'exists:loans,id']
                : ['required_if:category,loan_repayment', 'nullable', 'integer', Rule::exists('loans', 'id')->where(fn ($query) => $query
                    ->where('user_id', Auth::id())
                    ->where('approval_status', 'approved')
                    ->where('repayment_status', 'active'))],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['mpesa', 'bank', 'card', 'cash'])],
            'reference_number' => ['required', 'string', 'max:255', 'unique:payments,reference_number', 'unique:loan_repayments,reference_number'],
            'transaction_date' => ['required', 'date'],
            'status' => [$isTreasuryStaff ? 'required' : 'nullable', 'nullable', Rule::in(['pending', 'completed', 'failed'])],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $isTreasuryStaff && $data['category'] === 'loan_repayment') {
            if ($data['payment_method'] !== 'cash') {
                return back()
                    ->withErrors(['payment_method' => 'Loan repayments must be submitted as cash payments.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($data, $isTreasuryStaff): void {
            Payment::create([
                'payment_type' => $isTreasuryStaff ? $data['payment_type'] : 'inbound',
                'category' => $data['category'],
                'user_id' => $isTreasuryStaff ? ($data['user_id'] ?? null) : Auth::id(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'],
                'transaction_date' => $data['transaction_date'],
                'status' => $isTreasuryStaff ? $data['status'] : 'pending',
                'notes' => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            if (! $isTreasuryStaff && $data['category'] === 'loan_repayment') {
                LoanRepayment::create([
                    'loan_id' => $data['loan_id'],
                    'user_id' => Auth::id(),
                    'amount_paid' => $data['amount'],
                    'payment_date' => $data['transaction_date'],
                    'payment_method' => 'cash',
                    'reference_number' => $data['reference_number'],
                    'recorded_by' => Auth::id(),
                    'status' => 'pending',
                ]);
            }
        });

        return back()->with('success', 'Payment submitted successfully.');
    }
}
