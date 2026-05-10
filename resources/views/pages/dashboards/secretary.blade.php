<x-app-layout>
    <div class="space-y-8">
        <section class="premium-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.3fr_0.7fr] lg:px-8 lg:py-10">
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-badge variant="success" icon="fa-calendar-days">Secretary dashboard</x-badge>
                        <span class="text-sm text-slate-500">{{ $today ?? now()->format('l, d F Y') }}</span>
                    </div>
                    <div class="max-w-3xl space-y-4">
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Welcome back, {{ Auth::user()->name }}.</h1>
                        <p class="text-base leading-8 text-slate-600">Manage meetings, communication, and member coordination from one organized workspace.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <x-button href="{{ route('meetings') }}" icon="fa-calendar-days">Plan meeting</x-button>
                        <x-button href="{{ route('members') }}" variant="secondary" icon="fa-users">View members</x-button>
                        <x-button href="{{ route('notifications') }}" variant="dark" icon="fa-bell">Send notice</x-button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <x-stat-card title="Upcoming meetings" :value="$meetings_count" subtitle="Calendar overview" icon="fa-calendar-days" tone="emerald" />
                    <x-stat-card title="Member notices" :value="$notifications_count" subtitle="Communication queue" icon="fa-bell" tone="blue" />
                </div>
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Members" :value="$members_count" subtitle="Directory management" icon="fa-users" tone="slate" />
            <x-stat-card title="Meetings" :value="$meetings_count" subtitle="Upcoming sessions" icon="fa-calendar-days" tone="emerald" />
            <x-stat-card title="Reports" :value="$reports_count" subtitle="Records ready to review" icon="fa-chart-pie" tone="gold" />
            <x-stat-card title="Notifications" :value="$notifications_count" subtitle="Messages to send" icon="fa-bell" tone="blue" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <x-table-card title="Meeting agenda" subtitle="What needs attention next">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Agenda</th>
                            <th>Venue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($upcoming_meetings as $meeting)
                            <tr>
                                <td>{{ $meeting->meeting_date->format('M d') }} at {{ $meeting->meeting_time }}</td>
                                <td>{{ $meeting->title }}</td>
                                <td>{{ $meeting->location }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No meetings scheduled yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-card>

            <div class="space-y-6">
                <x-chart-card title="Communication" subtitle="Announcements and reminders">
                    <div class="h-[260px]">
                        <div class="flex h-full items-center justify-center rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 text-center">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">No communication chart yet</p>
                                <p class="mt-1 text-sm text-slate-500">Connect messaging stats for response tracking.</p>
                            </div>
                        </div>
                    </div>
                </x-chart-card>

                <div class="premium-card-muted p-6">
                    <p class="section-label">Secretary focus</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        This dashboard keeps meetings, member coordination, and announcements front and center.
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
