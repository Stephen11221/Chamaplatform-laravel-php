<x-app-layout>
    <div class="space-y-8">
        <section class="premium-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.3fr_0.7fr] lg:px-8 lg:py-10">
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-badge variant="info" icon="fa-wallet">Treasurer dashboard</x-badge>
                        <span class="text-sm text-slate-500">{{ $today }}</span>
                    </div>

                    <div class="max-w-3xl space-y-4">
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                            Welcome back, {{ Auth::user()->name }}.
                        </h1>
                        <p class="text-base leading-8 text-slate-600">
                            Keep collections, payouts, and loan activity moving with a finance-first workspace.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-button href="{{ route('contributions') }}" icon="fa-wallet">Record contribution</x-button>
                        <x-button href="{{ route('payments') }}" variant="secondary" icon="fa-receipt">Record payment</x-button>
                        <x-button href="{{ route('loans') }}" variant="dark" icon="fa-hand-holding-dollar">Review loans</x-button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <x-stat-card title="Collections" :value="$collections_total" subtitle="Paid contributions this month" icon="fa-coins" tone="emerald" />
                    <x-stat-card title="Payouts" :value="$payouts_total" subtitle="Completed outbound payments" icon="fa-receipt" tone="blue" />
                </div>
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Due payments" :value="$due_payments_count" subtitle="Pending cash flow" icon="fa-calendar-check" tone="gold" />
            <x-stat-card title="Active loans" :value="$active_loans_count" subtitle="Loan portfolio" icon="fa-hand-holding-dollar" tone="slate" />
            <x-stat-card title="Monthly collections" :value="$monthly_collections_total" subtitle="Treasury momentum" icon="fa-wallet" tone="emerald" />
            <x-stat-card title="Reports ready" :value="$reports_ready_count" subtitle="Finance summaries" icon="fa-chart-pie" tone="blue" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <x-table-card title="Payment queue" subtitle="The next records to reconcile">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($payment_queue as $payment)
                            <tr>
                                <td>{{ $payment->member_name ?? 'Group account' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->category)) }}</td>
                                <td>KES {{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No queued payments yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-card>

            <div class="space-y-6">
                <x-chart-card title="Cash flow" subtitle="Collections versus payouts">
                    <div class="h-[260px]">
                        <div class="flex h-full items-center justify-center rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 text-center">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Treasury data connected</p>
                                <p class="mt-1 text-sm text-slate-500">Use the live totals to track collections and payouts.</p>
                            </div>
                        </div>
                    </div>
                </x-chart-card>

                <div class="premium-card-muted p-6">
                    <p class="section-label">Treasury focus</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        This view keeps the emphasis on money movement, reconciliations, and loan readiness.
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
