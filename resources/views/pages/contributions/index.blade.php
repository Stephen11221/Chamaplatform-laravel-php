<x-app-layout>
    @php
        $role = strtolower(Auth::user()->role?->role_name ?? 'member');
        $isTreasuryStaff = $isTreasuryStaff ?? in_array($role, ['admin', 'treasurer'], true);
    @endphp

    <div x-data="{ recordPayment: false }" class="space-y-8">
        <section class="premium-card px-6 py-8 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <p class="section-label">Contributions</p>
                    <h1 class="section-heading">Contribution intelligence</h1>
                    <p class="text-sm leading-7 text-slate-600">
                        Track member payments in a real contribution table and record new payments for treasury staff.
                    </p>
                </div>
                @if ($isTreasuryStaff)
                    <x-button icon="fa-receipt" x-on:click="recordPayment = true">Record contribution</x-button>
                @endif
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Collected" value="KES {{ number_format($contributions->sum(fn ($item) => (float) $item->amount), 2) }}" subtitle="Connected contribution records" icon="fa-sack-dollar" tone="emerald" />
            <x-stat-card title="Paid Members" value="{{ $contributions->where('status', 'paid')->pluck('user_id')->unique()->count() }}" subtitle="Members with paid entries" icon="fa-user-check" tone="blue" />
            <x-stat-card title="Pending" value="{{ $contributions->where('status', 'pending')->count() }}" subtitle="Awaiting reconciliation" icon="fa-clock" tone="slate" />
            <x-stat-card title="Late Payments" value="{{ $contributions->where('status', 'late')->count() }}" subtitle="Overdue records" icon="fa-triangle-exclamation" tone="gold" />
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <x-chart-card title="Monthly collection" subtitle="Contributions by month">
                <canvas id="monthlyChart" class="max-h-[300px]"></canvas>
            </x-chart-card>

            <x-chart-card title="Collection trend" subtitle="Payment volume over time">
                <canvas id="trendChart" class="max-h-[300px]"></canvas>
            </x-chart-card>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
                const trendCtx = document.getElementById('trendChart')?.getContext('2d');

                if (monthlyCtx) {
                    const monthlyData = @json($monthlyData);
                    new Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: monthlyData.map(d => d.month),
                            datasets: [{
                                label: 'Contributions',
                                data: monthlyData.map(d => d.count),
                                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                borderWidth: 2,
                                borderRadius: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                    }
                                }
                            }
                        }
                    });
                }

                if (trendCtx) {
                    const trendData = @json($trendData);
                    new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: trendData.map(d => d.month),
                            datasets: [{
                                label: 'Payment Volume (KES)',
                                data: trendData.map(d => d.amount),
                                borderColor: 'rgba(59, 130, 246, 1)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'KES ' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>

        <x-table-card title="Contribution table" subtitle="Latest contribution records">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Month</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($contributions as $item)
                        <tr>
                            <td>{{ $item->member?->name ?? 'Member' }}</td>
                            <td class="font-semibold text-slate-900">KES {{ number_format((float) $item->amount, 2) }}</td>
                            <td>{{ $item->contribution_month }}</td>
                            <td>{{ optional($item->payment_date)->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($item->payment_method) }}</td>
                            <td>{{ $item->reference_number }}</td>
                            <td>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $item->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($item->status === 'late' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">
                                No contribution records yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-card>

        @if ($isTreasuryStaff)
            <x-modal open="recordPayment" title="Record contribution" maxWidth="2xl">
                <form method="POST" action="{{ route('contributions.store') }}" class="grid gap-5 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Member</label>
                        <select name="user_id" class="premium-select" required>
                            <option value="">Select member</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->role?->role_name ?? 'Member' }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Amount</label>
                        <input type="number" name="amount" min="1" step="0.01" class="premium-input" placeholder="Enter amount" required />
                        @error('amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Method</label>
                        <select name="payment_method" class="premium-select" required>
                            <option value="">Select method</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="bank">Bank</option>
                            <option value="card">Card</option>
                            <option value="cash">Cash</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Reference</label>
                        <input type="text" name="reference_number" class="premium-input" placeholder="Enter reference" required />
                        @error('reference_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Contribution month</label>
                        <input type="month" name="contribution_month" class="premium-input" required />
                        @error('contribution_month')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Payment date</label>
                        <input type="date" name="payment_date" class="premium-input" required />
                        @error('payment_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="premium-select" required>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="late">Late</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
                        <textarea name="notes" rows="4" class="premium-input" placeholder="Optional notes"></textarea>
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap justify-end gap-3">
                        <x-button variant="secondary" type="button" x-on:click="recordPayment = false">Cancel</x-button>
                        <x-button type="submit">Save contribution</x-button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</x-app-layout>
