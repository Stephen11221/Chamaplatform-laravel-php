<x-app-layout>
    <div
        x-data="{
            recordPayment: @json($errors->any()),
            paymentCategory: @json(old('category', '')),
        }"
        class="space-y-8"
    >
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="premium-card px-6 py-8 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <p class="section-label">Payments</p>
                    <h1 class="section-heading">Payment rail and reconciliation</h1>
                    <p class="text-sm leading-7 text-slate-600">
                        {{ $isTreasuryStaff ? 'Monitor settlement and reconciliation records across members.' : 'Submit payments and track your payment history from your member account.' }}
                    </p>
                </div>
                <x-button icon="fa-circle-plus" x-on:click="recordPayment = true">
                    {{ $isTreasuryStaff ? 'New payment' : 'Make payment' }}
                </x-button>
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Today" value="KES {{ number_format($payments->filter(fn ($payment) => optional($payment->transaction_date)->isSameDay(now()))->sum(fn ($payment) => (float) $payment->amount), 2) }}" subtitle="Payments recorded today" icon="fa-money-bill-transfer" tone="emerald" />
            <x-stat-card title="Successful" value="{{ $payments->where('status', 'completed')->count() }}" subtitle="Completed records" icon="fa-circle-check" tone="blue" />
            <x-stat-card title="Pending" value="{{ $payments->where('status', 'pending')->count() }}" subtitle="Awaiting reconciliation" icon="fa-hourglass-half" tone="gold" />
            <x-stat-card title="Failed" value="{{ $payments->where('status', 'failed')->count() }}" subtitle="Failed records" icon="fa-triangle-exclamation" tone="slate" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <div class="premium-card-muted p-6">
                <p class="section-label">Channels</p>
                <div class="mt-5 space-y-4">
                    @forelse ($payments->groupBy('payment_method') as $method => $items)
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-white px-4 py-3 text-sm shadow-sm">
                            <span class="font-semibold text-slate-700">{{ ucfirst($method) }}</span>
                            <span class="text-slate-500">{{ $items->count() }} payments</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No payment channels recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <x-table-card title="Payment ledger" subtitle="Latest payment records">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Member</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Channel</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->reference_number }}</td>
                                <td>{{ $payment->member?->name ?? 'Group account' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->category)) }}</td>
                                <td class="font-semibold text-slate-900">KES {{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $payment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($payment->status === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No payment records yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-card>
        </section>

        <x-modal open="recordPayment" title="{{ $isTreasuryStaff ? 'Create payment' : 'Make payment' }}" maxWidth="2xl">
            <form method="POST" action="{{ route('payments.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                @if ($isTreasuryStaff)
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Member</label>
                        <select name="user_id" class="premium-select">
                            <option value="">Group account</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" {{ (string) old('user_id') === (string) $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ $member->role?->role_name ?? 'Member' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Payment type</label>
                        <select name="payment_type" class="premium-select" required>
                            <option value="inbound" {{ old('payment_type') === 'inbound' ? 'selected' : '' }}>Inbound</option>
                            <option value="outbound" {{ old('payment_type') === 'outbound' ? 'selected' : '' }}>Outbound</option>
                        </select>
                    </div>
                @endif
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Category</label>
                    <select name="category" x-model="paymentCategory" class="premium-select" required>
                        <option value="">Select category</option>
                        <option value="contribution" {{ old('category') === 'contribution' ? 'selected' : '' }}>Contribution</option>
                        <option value="loan_repayment" {{ old('category') === 'loan_repayment' ? 'selected' : '' }}>Loan repayment</option>
                        @if ($isTreasuryStaff)
                            <option value="loan_disbursement" {{ old('category') === 'loan_disbursement' ? 'selected' : '' }}>Loan disbursement</option>
                            <option value="expense" {{ old('category') === 'expense' ? 'selected' : '' }}>Expense</option>
                        @endif
                        <option value="investment" {{ old('category') === 'investment' ? 'selected' : '' }}>Investment</option>
                    </select>
                    @error('category')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @if (! $isTreasuryStaff)
                    <div x-show="paymentCategory === 'loan_repayment'">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Loan</label>
                        <select name="loan_id" class="premium-select" x-bind:required="paymentCategory === 'loan_repayment'">
                            <option value="">Select approved loan</option>
                            @foreach ($payableLoans as $loan)
                                <option value="{{ $loan->id }}" {{ (string) old('loan_id') === (string) $loan->id ? 'selected' : '' }}>
                                    KES {{ number_format((float) $loan->loan_amount, 2) }} - {{ $loan->purpose }}
                                </option>
                            @endforeach
                        </select>
                        @error('loan_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if ($payableLoans->isEmpty())
                            <p class="mt-2 text-sm text-slate-500">No approved active loan is available for repayment.</p>
                        @endif
                    </div>
                @endif
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Amount</label>
                    <input type="number" name="amount" min="1" step="0.01" value="{{ old('amount') }}" class="premium-input" placeholder="Enter amount" required />
                    @error('amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @if ($isTreasuryStaff)
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Channel</label>
                        <select name="payment_method" class="premium-select" required>
                            <option value="">Select channel</option>
                            <option value="mpesa" {{ old('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div x-show="paymentCategory === 'loan_repayment'">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Channel</label>
                        <input type="hidden" name="payment_method" value="cash" x-bind:disabled="paymentCategory !== 'loan_repayment'" />
                        <input type="text" value="Cash" class="premium-input bg-slate-100 text-slate-500" readonly />
                        <p class="mt-2 text-sm text-slate-500">Cash repayments stay pending until the treasurer verifies them.</p>
                        @error('payment_method')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-show="paymentCategory !== 'loan_repayment'">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Channel</label>
                        <select name="payment_method" class="premium-select" x-bind:required="paymentCategory !== 'loan_repayment'" x-bind:disabled="paymentCategory === 'loan_repayment'">
                            <option value="">Select channel</option>
                            <option value="mpesa" {{ old('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Transaction date</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="premium-input" required />
                    @error('transaction_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Reference</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="premium-input" placeholder="Enter reference" required />
                    @error('reference_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @if ($isTreasuryStaff)
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="premium-select" required>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ old('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="4" class="premium-input" placeholder="Optional payment notes or reconciliation memo">{{ old('notes') }}</textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-3 sm:col-span-2">
                    <x-button variant="secondary" type="button" x-on:click="recordPayment = false">Cancel</x-button>
                    <x-button type="submit">Save payment</x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
