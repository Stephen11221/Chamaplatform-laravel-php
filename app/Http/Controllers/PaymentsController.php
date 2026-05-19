<?php

namespace App\Http\Controllers;

use App\Models\ChamaSetting;
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
        $chamaSettings = ChamaSetting::getInstance();

        return view('pages.payments.index', [
            'payments' => $payments,
            'members' => $members,
            'isTreasuryStaff' => $isTreasuryStaff,
            'payableLoans' => $payableLoans,
            'showPaymentModal' => $showPaymentModal,
            'mpesaPaybill' => $chamaSettings->mpesa_paybill,
            'mpesaAccountName' => $chamaSettings->mpesa_account_name,
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

    public function updateMpesaPaybill(Request $request): RedirectResponse
    {
        abort_unless(in_array(strtolower(Auth::user()->role?->role_name ?? ''), ['admin', 'treasurer'], true), 403);

        $data = $request->validate([
            'mpesa_paybill' => ['required', 'string', 'max:20'],
            'mpesa_account_name' => ['required', 'string', 'max:255'],
        ]);

        $settings = ChamaSetting::getInstance();
        $settings->update($data);

        return back()->with('success', 'M-Pesa details updated successfully.');
    }

    public function manage(): View
    {
        abort_unless(in_array(strtolower(Auth::user()->role?->role_name ?? ''), ['admin', 'treasurer'], true), 403);

        $payments = Payment::with(['member', 'recorder'])->orderByDesc('transaction_date')->orderByDesc('created_at')->get();
        $chamaSettings = ChamaSetting::getInstance();
        $members = User::orderBy('full_name')->get();

        return view('pages.payments.manage', [
            'payments' => $payments,
            'mpesaPaybill' => $chamaSettings->mpesa_paybill,
            'mpesaAccountName' => $chamaSettings->mpesa_account_name,
            'members' => $members,
        ]);
    }

    public function edit(Payment $payment): View
    {
        abort_unless(in_array(strtolower(Auth::user()->role?->role_name ?? ''), ['admin', 'treasurer'], true), 403);

        $payment->load(['member']);
        $members = User::orderBy('full_name')->get();

        return view('pages.payments.edit', [
            'payment' => $payment,
            'members' => $members,
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless(in_array(strtolower(Auth::user()->role?->role_name ?? ''), ['admin', 'treasurer'], true), 403);

        $data = $request->validate([
            'payment_type' => ['required', Rule::in(['inbound', 'outbound'])],
            'category' => ['required', Rule::in(['contribution', 'loan_repayment', 'loan_disbursement', 'investment', 'expense'])],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['mpesa', 'bank', 'card', 'cash'])],
            'reference_number' => ['required', 'string', 'max:255', Rule::unique('payments', 'reference_number')->ignore($payment->id)],
            'transaction_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['pending', 'completed', 'failed'])],
            'notes' => ['nullable', 'string'],
        ]);

        $payment->fill([
            'payment_type' => $data['payment_type'],
            'category' => $data['category'],
            'user_id' => $data['user_id'] ?? null,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'],
            'transaction_date' => $data['transaction_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        $payment->save();

        return redirect()->route('payments.manage')->with('success', 'Payment updated successfully.');
    }
}
