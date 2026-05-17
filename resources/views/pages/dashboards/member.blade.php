<x-app-layout>
    <div class="space-y-8">
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
                        <x-button href="{{ route('contributions') }}" icon="fa-wallet">View contributions</x-button>
                        <x-button href="{{ route('payments') }}" variant="secondary" icon="fa-receipt">Check payments</x-button>
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
    </div>
</x-app-layout>
