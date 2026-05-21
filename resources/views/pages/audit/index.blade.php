<x-app-layout>
    <div class="space-y-8">
        <section class="premium-card px-6 py-8 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <p class="section-label">Audit Logs</p>
                    <h1 class="section-heading">Operational audit trail</h1>
                    <p class="text-sm leading-7 text-slate-600">Capture and review audit events from platform activity.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-button variant="secondary" icon="fa-filter">Filter logs</x-button>
                    <x-button icon="fa-download">Export CSV</x-button>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="premium-card p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-lg font-semibold text-slate-900">Record audit data</p>
                        <p class="mt-1 text-sm text-slate-500">Submit event details to build your audit trail.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('audit.store') }}" class="mt-8 grid gap-5">
                    @csrf

                    <div>
                        <label for="module" class="mb-2 block text-sm font-semibold text-slate-700">Module</label>
                        <select id="module" name="module" class="premium-input" required>
                            <option value="" disabled {{ old('module') ? '' : 'selected' }}>Select module</option>
                            @foreach($modules as $moduleOption)
                                <option value="{{ $moduleOption }}" {{ old('module') === $moduleOption ? 'selected' : '' }}>{{ $moduleOption }}</option>
                            @endforeach
                        </select>
                        @error('module')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="activity" class="mb-2 block text-sm font-semibold text-slate-700">Action</label>
                        <textarea id="activity" name="activity" rows="4" class="premium-input" placeholder="Describe the audit event" required>{{ old('activity') }}</textarea>
                        @error('activity')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="ip_address" class="mb-2 block text-sm font-semibold text-slate-700">IP address</label>
                            <input id="ip_address" name="ip_address" type="text" value="{{ old('ip_address', $ipAddress) }}" class="premium-input" placeholder="e.g. 203.0.113.45" readonly />
                            @error('ip_address')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="device_info" class="mb-2 block text-sm font-semibold text-slate-700">Device info</label>
                            <input id="device_info" name="device_info" type="text" value="{{ old('device_info', $deviceInfo) }}" class="premium-input" placeholder="e.g. Chrome on Windows 11" readonly />
                            @error('device_info')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-button type="submit">Record audit event</x-button>
                        <x-button variant="secondary" href="{{ route('audit') }}">Reset form</x-button>
                    </div>
                </form>
            </div>

            <div class="space-y-5">
                <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-2">
                    <x-stat-card title="Events Today" value="{{ $eventsToday }}" subtitle="Recorded in the last 24 hours" icon="fa-shield-halved" tone="emerald" />
                    <x-stat-card title="Critical Events" value="{{ $criticalEvents }}" subtitle="Potential security events" icon="fa-triangle-exclamation" tone="gold" />
                    <x-stat-card title="Approvals" value="{{ $approvalEvents }}" subtitle="Approval-related actions" icon="fa-check-double" tone="blue" />
                    <x-stat-card title="User Actions" value="{{ $userActions }}" subtitle="Distinct actors" icon="fa-user-pen" tone="slate" />
                </section>

                <x-chart-card title="Event overview" subtitle="Audit activity by module">
                    <div class="flex h-[280px] items-center justify-center rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 text-center">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Audit event summary</p>
                            <p class="mt-1 text-sm text-slate-500">Recent module and action counts will appear here once events are recorded.</p>
                        </div>
                    </div>
                </x-chart-card>
            </div>
        </section>

        <x-table-card title="Recent audit events" subtitle="Latest recorded events in the audit trail">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Actor</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>IP address</th>
                        <th>Recorded at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($recentEvents as $event)
                        <tr>
                            <td>{{ $event->user?->full_name ?? 'System' }}</td>
                            <td>{{ $event->module }}</td>
                            <td>{{ $event->activity }}</td>
                            <td>{{ $event->ip_address ?: '—' }}</td>
                            <td>{{ optional($event->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                No audit events recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-card>
    </div>
</x-app-layout>
