<x-app-layout>
    <div class="space-y-8">
        <section class="premium-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.3fr_0.7fr] lg:px-8 lg:py-10">
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-badge variant="gold" icon="fa-shield-halved">Admin dashboard</x-badge>
                        <span class="text-sm text-slate-500">{{ $today }}</span>
                    </div>

                    <div class="max-w-3xl space-y-4">
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                            Welcome back, {{ Auth::user()->name }}.
                        </h1>
                        <p class="text-base leading-8 text-slate-600">
                            You can manage users, monitor activity, and keep the entire platform aligned from one control center.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-button href="{{ route('members') }}" icon="fa-users">Manage members</x-button>
                        <x-button href="{{ route('audit') }}" variant="secondary" icon="fa-shield-halved">Review audit logs</x-button>
                        <x-button href="{{ route('settings') }}" variant="dark" icon="fa-gears">Open settings</x-button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <x-stat-card title="Total balance" :value="$total_balance" subtitle="Paid contributions plus investment value" icon="fa-coins" tone="emerald" />
                    <x-stat-card title="Pending loans" :value="$pending_loans_count" subtitle="Applications awaiting review" icon="fa-triangle-exclamation" tone="gold" />
                </div>
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Members" :value="$members_count" subtitle="{{ $active_members_count }} active accounts" icon="fa-users" tone="blue" />
            <x-stat-card title="Contributions" :value="$contributions_total" subtitle="{{ $monthly_contributions_total }} this month" icon="fa-wallet" tone="emerald" />
            <x-stat-card title="Loans" :value="$loans_count" subtitle="{{ $active_loans_count }} active loans" icon="fa-hand-holding-dollar" tone="slate" />
            <x-stat-card title="Investments" :value="$investments_total" subtitle="Current investment book" icon="fa-building-columns" tone="gold" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <x-table-card title="Recent activity" subtitle="Administrative events and system actions">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Actor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($recent_activity as $activity)
                            <tr>
                                <td>{{ ucfirst($activity->module) }}</td>
                                <td>{{ $activity->activity }}</td>
                                <td>{{ $activity->actor ?? 'System' }}</td>
                                <td>Recorded</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No recent audit data yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-card>

            <div class="space-y-6">
                <x-chart-card title="System health" subtitle="Services and operational readiness">
                    <div class="h-[260px]">
                        <div class="flex h-full items-center justify-center rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 text-center">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $audit_events_count }} audit events recorded</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $pending_loans_count }} loans currently need admin review.</p>
                            </div>
                        </div>
                    </div>
                </x-chart-card>

                <div class="premium-card-muted p-6">
                    <p class="section-label">Admin focus</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        This dashboard is centered on permissions, oversight, and keeping the platform trustworthy for every role.
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
