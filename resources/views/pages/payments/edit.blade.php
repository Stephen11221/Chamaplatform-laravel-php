<x-app-layout>
    <div class="space-y-8">
        <section class="premium-card px-6 py-8 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="section-label">Edit payment</p>
                    <h1 class="section-heading">Edit payment {{ $payment->reference_number }}</h1>
                </div>
            </div>
        </section>

        <x-table-card title="Payment details">
            <form method="POST" action="{{ route('payments.update', $payment) }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Reference</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number', $payment->reference_number) }}" class="premium-input" required />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Member</label>
                    <select name="user_id" class="premium-select">
                        <option value="">(none)</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ (string) old('user_id', $payment->user_id) === (string) $member->id ? 'selected' : '' }}>{{ $member->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Category</label>
                    <select name="category" class="premium-select" required>
                        <option value="contribution" {{ old('category', $payment->category) === 'contribution' ? 'selected' : '' }}>Contribution</option>
                        <option value="loan_repayment" {{ old('category', $payment->category) === 'loan_repayment' ? 'selected' : '' }}>Loan repayment</option>
                        <option value="loan_disbursement" {{ old('category', $payment->category) === 'loan_disbursement' ? 'selected' : '' }}>Loan disbursement</option>
                        <option value="investment" {{ old('category', $payment->category) === 'investment' ? 'selected' : '' }}>Investment</option>
                        <option value="expense" {{ old('category', $payment->category) === 'expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Amount</label>
                    <input type="number" name="amount" value="{{ old('amount', $payment->amount) }}" min="1" step="0.01" class="premium-input" required />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Channel</label>
                    <select name="payment_method" class="premium-select" required>
                        <option value="mpesa" {{ old('payment_method', $payment->payment_method) === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="bank" {{ old('payment_method', $payment->payment_method) === 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="card" {{ old('payment_method', $payment->payment_method) === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="cash" {{ old('payment_method', $payment->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Transaction date</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', optional($payment->transaction_date)->toDateString()) }}" class="premium-input" required />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" class="premium-select" required>
                        <option value="pending" {{ old('status', $payment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ old('status', $payment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ old('status', $payment->status) === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="4" class="premium-input">{{ old('notes', $payment->notes) }}</textarea>
                </div>

                <div class="sm:col-span-2 flex justify-end gap-3">
                    <x-button variant="secondary" type="button" onclick="window.location='{{ route('payments.manage') }}'">Cancel</x-button>
                    <x-button type="submit">Save changes</x-button>
                </div>
            </form>
        </x-table-card>
    </div>
</x-app-layout>