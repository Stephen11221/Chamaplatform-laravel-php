<x-app-layout>
    <div x-data="{ recordPayment: false, editMpesa: false, showMpesaMenu: false }" class="space-y-8">
        <section class="premium-card px-6 py-8 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="section-label">Payments</p>
                    <h1 class="section-heading">Manage payments</h1>
                    <p class="text-sm text-slate-500">Admin and treasurer can review and edit payment records.</p>
                </div>
                <x-button icon="fa-circle-plus" x-on:click="recordPayment = true">New payment</x-button>
            </div>
                <div class="relative">
                    @if ($mpesaPaybill)
                        <div class="text-right">
                            <p class="text-xs text-emerald-700">M-Pesa: <strong>{{ $mpesaPaybill }}</strong></p>
                            <p class="text-xs text-slate-600">{{ $mpesaAccountName }}</p>
                        </div>
                        <div class="mt-2 flex justify-end">
                            <div class="relative" x-data>
                                <button x-on:click="showMpesaMenu = !showMpesaMenu" class="rounded-3xl border border-slate-200 bg-white px-3 py-1 text-xs font-semibold">Edit M-Pesa</button>
                                <div x-show="showMpesaMenu" x-cloak x-on:click.away="showMpesaMenu = false" class="absolute right-0 mt-2 w-44 rounded-md border bg-white shadow-lg">
                                    <a href="#" x-on:click.prevent="editMpesa = true; showMpesaMenu = false" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Edit paybill & account</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <x-modal open="recordPayment" title="Create payment" maxWidth="2xl">
            <form method="POST" action="{{ route('payments.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Member</label>
                    <select name="user_id" class="premium-select">
                        <option value="">Group account</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Payment type</label>
                    <select name="payment_type" class="premium-select" required>
                        <option value="inbound">Inbound</option>
                        <option value="outbound">Outbound</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Category</label>
                    <select name="category" class="premium-select" required>
                        <option value="contribution">Contribution</option>
                        <option value="loan_repayment">Loan repayment</option>
                        <option value="loan_disbursement">Loan disbursement</option>
                        <option value="investment">Investment</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Amount</label>
                    <input type="number" name="amount" min="1" step="0.01" class="premium-input" required />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Channel</label>
                    <select name="payment_method" class="premium-select" required>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank</option>
                        <option value="card">Card</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Transaction date</label>
                    <input type="date" name="transaction_date" value="{{ now()->toDateString() }}" class="premium-input" required />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Reference</label>
                    <input type="text" name="reference_number" class="premium-input" required />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" class="premium-select" required>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="4" class="premium-input"></textarea>
                </div>
                <div class="sm:col-span-2 flex justify-end gap-3">
                    <x-button variant="secondary" type="button" x-on:click="recordPayment = false">Cancel</x-button>
                    <x-button type="submit">Save payment</x-button>
                </div>
            </form>
        </x-modal>

        <x-modal open="editMpesa" title="Update M-Pesa Details" maxWidth="md">
            <form method="POST" action="{{ route('payments.update-mpesa-paybill') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Paybill Number</label>
                    <input type="text" name="mpesa_paybill" value="{{ old('mpesa_paybill', $mpesaPaybill) }}" class="premium-input" placeholder="e.g., 123456" required />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Account Name</label>
                    <input type="text" name="mpesa_account_name" value="{{ old('mpesa_account_name', $mpesaAccountName) }}" class="premium-input" placeholder="e.g., CHAMA GROUP" required />
                </div>
                <div class="flex flex-wrap justify-end gap-3">
                    <x-button variant="secondary" type="button" x-on:click="editMpesa = false">Cancel</x-button>
                    <x-button type="submit">Update Details</x-button>
                </div>
            </form>
        </x-modal>

        <x-table-card title="Payment ledger" subtitle="All recorded payments">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Member</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Recorded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->reference_number }}</td>
                            <td>{{ $payment->member?->full_name ?? '—' }}</td>
                            <td>{{ ucfirst($payment->category) }}</td>
                            <td class="font-semibold">KES {{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td>{{ ucfirst($payment->status) }}</td>
                            <td>{{ $payment->transaction_date?->format('Y-m-d') }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="{{ route('payments.edit', $payment) }}" class="rounded-3xl border border-slate-200 bg-white px-3 py-1 text-xs font-semibold">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500">No payments recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-card>
    </div>
</x-app-layout>