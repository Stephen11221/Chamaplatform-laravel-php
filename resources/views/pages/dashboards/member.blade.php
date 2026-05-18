<x-app-layout>
    <div
        x-data="{
            makePayment: @json($errors->any()),
            paymentCategory: @json(old('category', '')),
        }"
        class="space-y-8"
    >
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="premium-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.3fr_0.7fr] lg:px-8 lg:py-10">
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-badge variant="dark" icon="fa-user">Member dashboard</x-badge>
                        <span class="text-sm text-slate-500">{{ $today }}</span>
                    </div>

                    <div class="max-w-3xl space-y-4">
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                            Welcome back, {{ Auth::user()->name }}.
                        </h1>
                        <p class="text-base leading-8 text-slate-600">
                            Follow your savings, loan status, and payment activity from a personal home base.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-button icon="fa-receipt" x-on:click="makePayment = true">Make payment</x-button>
                        <x-button href="{{ route('contributions') }}" icon="fa-wallet">View contributions</x-button>
                        <x-button href="{{ route('payments') }}" variant="secondary" icon="fa-list-check">Payment history</x-button>
                        <x-button href="{{ route('loans') }}" variant="dark" icon="fa-hand-holding-dollar">Loan status</x-button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <x-stat-card title="My savings" :value="$my_savings_total" subtitle="Personal contribution summary" icon="fa-wallet" tone="emerald" />
                    <x-stat-card title="Loan balance" :value="$loan_balance" subtitle="Current repayment position" icon="fa-hand-holding-dollar" tone="gold" />
                </div>
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Contributions" :value="$my_contributions_count" subtitle="Your deposits" icon="fa-coins" tone="emerald" />
            <x-stat-card title="Payments" :value="$my_payments_count" subtitle="Recent transactions" icon="fa-receipt" tone="blue" />
            <x-stat-card title="Meetings" :value="$my_meetings_count" subtitle="Member sessions" icon="fa-calendar-days" tone="slate" />
            <x-stat-card title="Notifications" :value="$my_notifications_count" subtitle="Unread updates for you" icon="fa-bell" tone="gold" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <x-table-card title="My recent activity" subtitle="Latest actions on your account">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($recent_personal_activity as $activity)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($activity['date'])->format('M d, Y') }}</td>
                                <td>{{ $activity['activity'] }}</td>
                                <td>{{ $activity['status'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No personal activity yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-card>

            <div class="space-y-6">
                <x-chart-card title="Savings trend" subtitle="Your contribution history">
                    <div class="h-[260px]">
                        <div class="flex h-full items-center justify-center rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 text-center">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $my_savings_total }} saved</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $my_contributions_count }} contribution records on your account.</p>
                            </div>
                        </div>
                    </div>
                </x-chart-card>

                <div class="premium-card-muted p-6">
                    <p class="section-label">Member focus</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        This view keeps personal financial activity simple, clear, and easy to follow.
                    </p>
                </div>
            </div>
        </section>

        <x-modal open="makePayment" title="Make payment" maxWidth="lg">
            <form method="POST" action="{{ route('payments.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Payment for</label>
                    <select name="category" x-model="paymentCategory" class="premium-select" required>
                        <option value="">Select category</option>
                        <option value="contribution" {{ old('category') === 'contribution' ? 'selected' : '' }}>Contribution</option>
                        <option value="loan_repayment" {{ old('category') === 'loan_repayment' ? 'selected' : '' }}>Loan repayment</option>
                        <option value="investment" {{ old('category') === 'investment' ? 'selected' : '' }}>Investment</option>
                    </select>
                    @error('category')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div x-show="paymentCategory === 'loan_repayment'">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Loan</label>
                    <select name="loan_id" class="premium-select" x-bind:required="paymentCategory === 'loan_repayment'">
                        <option value="">Select approved loan</option>
                        @foreach ($payable_loans as $loan)
                            <option value="{{ $loan->id }}" {{ (string) old('loan_id') === (string) $loan->id ? 'selected' : '' }}>
                                KES {{ number_format((float) $loan->loan_amount, 2) }} - {{ $loan->purpose }}
                            </option>
                        @endforeach
                    </select>
                    @error('loan_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if ($payable_loans->isEmpty())
                        <p class="mt-2 text-sm text-slate-500">No approved active loan is available for repayment.</p>
                    @endif
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Amount</label>
                    <input type="number" name="amount" min="1" step="0.01" value="{{ old('amount') }}" class="premium-input" placeholder="Enter amount" required />
                    @error('amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div x-show="paymentCategory === 'loan_repayment'">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Method</label>
                    <input type="hidden" name="payment_method" value="cash" x-bind:disabled="paymentCategory !== 'loan_repayment'" />
                    <input type="text" value="Cash" class="premium-input bg-slate-100 text-slate-500" readonly />
                    <p class="mt-2 text-sm text-slate-500">Cash repayments stay pending until the treasurer verifies them.</p>
                    @error('payment_method')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div x-show="paymentCategory !== 'loan_repayment'">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Method</label>
                    <select name="payment_method" class="premium-select" x-bind:required="paymentCategory !== 'loan_repayment'" x-bind:disabled="paymentCategory === 'loan_repayment'">
                        <option value="">Select method</option>
                        <option value="mpesa" {{ old('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Payment date</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="premium-input" required />
                    @error('transaction_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Reference number</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="premium-input" placeholder="M-Pesa, bank, or receipt reference" required />
                    @error('reference_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="4" class="premium-input" placeholder="Optional payment note">{{ old('notes') }}</textarea>
                </div>
                <div class="sm:col-span-2 flex flex-wrap justify-end gap-3">
                    <x-button variant="secondary" type="button" x-on:click="makePayment = false">Cancel</x-button>
                    <x-button type="submit">Submit payment</x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
