<x-app-layout>
    @php
        $role = strtolower(Auth::user()->role?->role_name ?? 'member');
        $isTreasuryStaff = $isTreasuryStaff ?? in_array($role, ['admin', 'treasurer'], true);
    @endphp

    <div
        x-data="{
            applyLoan: false,
            loanAmount: 0,
            durationMonths: 12,
            fixedRate: 2,
            get monthlyInterest() {
                return this.loanAmount * (this.fixedRate / 100);
            },
            get totalInterest() {
                return this.monthlyInterest * this.durationMonths;
            },
            get totalRepayable() {
                return this.loanAmount + this.totalInterest;
            },
            get monthlyPayment() {
                return this.durationMonths ? this.totalRepayable / this.durationMonths : 0;
            }
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
                    <p class="section-label">Loans</p>
                    <h1 class="section-heading">{{ $isTreasuryStaff ? 'Loan portfolio management' : 'Pending loan requests' }}</h1>
                    <p class="text-sm leading-7 text-slate-600">
                        {{ $isTreasuryStaff ? 'Members can apply for loans up to KES 50,000. Treasurer and Admin approvals count toward final approval.' : 'You can only view loan applications that are still pending review.' }}
                    </p>
                </div>
                <x-button icon="fa-hand-holding-dollar" x-on:click="applyLoan = true">
                    {{ $role === 'member' ? 'Apply for loan' : 'New loan application' }}
                </x-button>
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="{{ $isTreasuryStaff ? 'Active Loans' : 'Pending Loans' }}" value="{{ $isTreasuryStaff ? $loans->where('repayment_status', 'active')->count() : $loans->count() }}" subtitle="{{ $isTreasuryStaff ? 'Current portfolio' : 'Your applications in review' }}" icon="fa-hand-holding-dollar" tone="emerald" />
            <x-stat-card title="{{ $isTreasuryStaff ? 'Outstanding Balance' : 'Requested Amount' }}" value="KES {{ number_format($loans->sum(fn ($loan) => (float) ($isTreasuryStaff ? $loan->total_repayable : $loan->loan_amount)), 2) }}" subtitle="{{ $isTreasuryStaff ? 'Approved and active exposure' : 'Total pending principal' }}" icon="fa-scale-balanced" tone="blue" />
            <x-stat-card title="{{ $isTreasuryStaff ? 'Repayment Rate' : 'Approval Progress' }}" value="{{ $isTreasuryStaff ? ($loans->count() ? round(($loans->where('repayment_status', 'completed')->count() / $loans->count()) * 100) : 0).'%' : ($loans->count() ? round(($loans->sum('approvals_count') / ($loans->count() * 2)) * 100) : 0).'%' }}" subtitle="{{ $isTreasuryStaff ? 'Completed loans' : 'Average pending approval progress' }}" icon="fa-arrow-trend-up" tone="gold" />
            <x-stat-card title="Pending Requests" value="{{ $loans->where('approval_status', 'pending')->count() }}" subtitle="Awaiting approvals" icon="fa-clipboard-list" tone="slate" />
        </section>

        @if ($isTreasuryStaff)
            <x-table-card title="Cash loan repayments" subtitle="Pending member cash repayments awaiting verification">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Loan</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($pendingRepayments as $repayment)
                            <tr>
                                <td>{{ $repayment->member?->name ?? 'Member' }}</td>
                                <td>{{ $repayment->loan?->purpose ?? 'Loan repayment' }}</td>
                                <td class="font-semibold text-slate-900">KES {{ number_format((float) $repayment->amount_paid, 2) }}</td>
                                <td>{{ optional($repayment->payment_date)->format('Y-m-d') }}</td>
                                <td>{{ $repayment->reference_number }}</td>
                                <td>
                                    <form method="POST" action="{{ route('loan-repayments.verify', $repayment) }}">
                                        @csrf
                                        <x-button type="submit" variant="secondary" icon="fa-check">Verify cash</x-button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No cash loan repayments are waiting for verification.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-card>
        @endif

        <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <x-chart-card title="Loan approval progress" subtitle="Track how many approvals each request has gathered">
                <div class="space-y-4">
                    @forelse ($loans->take(5) as $loan)
                        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $loan->borrower?->name ?? 'Member' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $loan->purpose }}</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">{{ $loan->approvals_count }}/2</span>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(($loan->approvals_count / 2) * 100, 100) }}%"></div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">
                                {{ $loan->approval_status === 'approved' ? 'Approved' : 'Awaiting Treasurer/Admin approvals' }}
                            </p>
                        </div>
                    @empty
                        <div class="flex h-[280px] items-center justify-center rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 text-center">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">No loan applications yet</p>
                                <p class="mt-1 text-sm text-slate-500">Applications will show here once members start applying.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </x-chart-card>

            <div class="premium-card-muted p-6">
                <p class="section-label">Quick controls</p>
                <div class="mt-5 space-y-3">
                    <x-button href="{{ route('contributions') }}" variant="secondary" class="w-full" icon="fa-receipt">View contributions</x-button>
                    <x-button href="{{ route('payments') }}" variant="secondary" class="w-full" icon="fa-bell">Send repayment reminder</x-button>
                    <x-button href="{{ route('reports') }}" variant="secondary" class="w-full" icon="fa-file-invoice">Generate loan statement</x-button>
                </div>
                <div class="mt-6 rounded-[1.5rem] bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Loan limit</p>
                    <p class="mt-3 text-sm text-slate-700">Maximum application amount: KES 50,000.</p>
                    <p class="mt-2 text-sm text-slate-500">A loan is marked approved after 2 approvals from Treasurer/Admin users.</p>
                </div>
            </div>
        </section>

        <x-table-card title="Loan applications" subtitle="{{ $isTreasuryStaff ? 'All applications and approval progress' : 'Your pending applications and approval progress' }}">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Borrower</th>
                        <th>Purpose</th>
                        <th>Amount</th>
                        <th>Approvals</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($loans as $loan)
                        <tr>
                            <td>{{ $loan->borrower?->name ?? 'Member' }}</td>
                            <td class="max-w-[22rem] whitespace-normal">{{ $loan->purpose }}</td>
                            <td class="font-semibold text-slate-900">KES {{ number_format((float) $loan->loan_amount, 2) }}</td>
                            <td>
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-slate-900">{{ $loan->approvals_count }}/2</p>
                                    <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(($loan->approvals_count / 2) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $loan->approval_status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($loan->approval_status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ ucfirst($loan->approval_status) }}
                                </span>
                            </td>
                            <td>
                                @if ($isTreasuryStaff && $loan->approval_status === 'pending')
                                    @if (! $loan->approvals->contains('user_id', Auth::id()))
                                        <form method="POST" action="{{ route('loans.approve', $loan) }}">
                                            @csrf
                                            <x-button type="submit" variant="secondary" icon="fa-check">Approve</x-button>
                                        </form>
                                    @else
                                        <span class="text-sm text-slate-500">You already approved</span>
                                    @endif
                                @else
                                    <span class="text-sm text-slate-500">
                                        {{ $loan->approval_status === 'approved' ? 'Approved' : 'Waiting for review' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                                No loan applications yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-card>

        <x-modal open="applyLoan" title="Apply for loan" maxWidth="2xl">
            <form method="POST" action="{{ route('loans.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Loan amount</label>
                    <input type="number" name="loan_amount" min="1" max="50000" step="0.01" x-model.number="loanAmount" class="premium-input" placeholder="Enter amount up to 50000" required />
                    @error('loan_amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Duration months</label>
                    <input type="number" name="duration_months" min="1" max="60" x-model.number="durationMonths" class="premium-input" placeholder="12" required />
                    @error('duration_months')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Interest rate</label>
                    <input type="text" class="premium-input bg-slate-100 text-slate-500" value="2% per month" readonly />
                </div>
                <div class="sm:col-span-2 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Monthly interest</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">KES <span x-text="monthlyInterest.toFixed(2)"></span></p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Total interest</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">KES <span x-text="totalInterest.toFixed(2)"></span></p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Monthly payment</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">KES <span x-text="monthlyPayment.toFixed(2)"></span></p>
                    </div>
                </div>
                <div class="sm:col-span-2 rounded-3xl border border-emerald-100 bg-emerald-50/70 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Preview before submit</p>
                    <p class="mt-2 text-sm text-slate-600">
                        Total repayable:
                        <span class="font-semibold text-slate-900">KES <span x-text="totalRepayable.toFixed(2)"></span></span>
                        at 2% per month over
                        <span class="font-semibold text-slate-900"><span x-text="durationMonths"></span> months</span>.
                    </p>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Guarantor</label>
                    <select name="guarantor_id" class="premium-select" required>
                        <option value="">Select guarantor</option>
                        @foreach ($members as $member)
                            @if ($member->id !== Auth::id())
                                <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->role?->role_name ?? 'Member' }})</option>
                            @endif
                        @endforeach
                    </select>
                    @error('guarantor_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Purpose</label>
                    <textarea name="purpose" rows="4" class="premium-input" placeholder="Explain why you need the loan and how you will repay it." required></textarea>
                    @error('purpose')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2 flex flex-wrap justify-end gap-3">
                    <x-button variant="secondary" type="button" x-on:click="applyLoan = false">Cancel</x-button>
                    <x-button type="submit">Submit application</x-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
