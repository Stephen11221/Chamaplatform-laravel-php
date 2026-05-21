<x-app-layout>
    <div x-data="memberData()" class="space-y-8">
        <script>
            function memberData() {
                return {
                    openCreateModal: false,
                    openEditModal: false,
                    openSmsModal: false,
                    loanDeductionOpen: false,
                    selectedMembers: [],
                    selectAll: false,
                    smsMessage: '',
                    deductMember: null,
                    deductLoanId: null,
                    deductAmount: null,
                    deductPaymentMethod: 'cash',
                    deductReference: '',
                    deductTransactionDate: '{{ now()->toDateString() }}',
                    deductStatus: 'completed',
                    editingMember: {
                        id: null,
                        full_name: '',
                        national_id: '',
                        email: '',
                        phone_number: '',
                        role: 'Member',
                        location: '',
                        status: 'active',
                    },
                    membersData: @json($membersData),
                    openEdit(memberId) {
                        const member = this.membersData.find((item) => item.id === memberId);
                        if (!member) {
                            return;
                        }
                        this.editingMember = { ...member };
                        this.openEditModal = true;
                    },
                    openLoanDeduction(memberId) {
                        const member = this.membersData.find((item) => item.id === memberId);
                        if (!member || member.active_loans_count === 0) {
                            return;
                        }
                        this.deductMember = member;
                        this.deductLoanId = member.active_loans[0]?.id || null;
                        this.deductAmount = null;
                        this.deductPaymentMethod = 'cash';
                        this.deductReference = '';
                        this.deductStatus = 'completed';
                        this.loanDeductionOpen = true;
                    },
                };
            }
        </script>
        <section class="premium-card px-6 py-8 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <p class="section-label">Members</p>
                    <h1 class="section-heading">Premium member management</h1>
                    <p class="text-sm leading-7 text-slate-600">Create member login accounts, track roles, and keep all member records in the same secure user table.</p>
                </div>

                @if(Auth::user()->role?->role_name === 'Admin')
                    <div class="flex flex-wrap items-center gap-3">
                        <x-button icon="fa-user-plus" x-on:click="openCreateModal = true">Add member</x-button>
                        <x-button icon="fa-sms" variant="secondary" x-bind:disabled="selectedMembers.length === 0" x-on:click="openSmsModal = true">Send SMS</x-button>
                    </div>
                @endif
            </div>
        </section>

        @if (session('success'))
            <div class="premium-card-muted border-emerald-200 px-6 py-4 text-sm text-emerald-700">
                <i class="fa-solid fa-circle-check mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Members" value="{{ $totalMembers }}" subtitle="Registered user accounts" icon="fa-users" tone="emerald" />
            <x-stat-card title="Active Members" value="{{ $activeMembers }}" subtitle="Active login access" icon="fa-user-check" tone="blue" />
            <x-stat-card title="Admins" value="{{ $adminCount }}" subtitle="Platform administrators" icon="fa-user-shield" tone="gold" />
            <x-stat-card title="Leadership Roles" value="{{ $leaderCount }}" subtitle="Treasurer, Secretary, Admin" icon="fa-user-tie" tone="slate" />
        </section>

        <section class="premium-card-muted p-6">
            <div class="space-y-4">
                <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr_0.6fr]">
                    <label class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="search" placeholder="Search members by name, email or phone" class="premium-input pl-12" />
                    </label>
                    <select class="premium-select">
                        <option>All roles</option>
                        @foreach ($roles as $role)
                            <option>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                    <select class="premium-select">
                        <option>All statuses</option>
                        <option>Active</option>
                        <option>Inactive</option>
                        <option>Suspended</option>
                    </select>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Selected members: <strong x-text="selectedMembers.length"></strong></p>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 hover:bg-slate-50" x-on:click="selectAll = !selectAll; selectedMembers = selectAll ? membersData.map(member => member.id) : []">
                            <span x-text="selectAll ? 'Unselect all' : 'Select all'"></span>
                        </button>
                        <button type="button" class="rounded-3xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500" x-bind:disabled="selectedMembers.length === 0" x-on:click="openSmsModal = true">
                            Send SMS to selected
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="premium-card p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-950 text-left text-xs uppercase tracking-[0.24em] text-slate-300">
                        <tr>
                            <th class="px-6 py-4 font-semibold">
                                <input type="checkbox" x-model="selectAll" x-on:click="selectedMembers = selectAll ? membersData.map(member => member.id) : []" class="h-4 w-4 rounded border-slate-300 text-emerald-600" />
                            </th>
                            <th class="px-6 py-4 font-semibold">Member</th>
                            <th class="px-6 py-4 font-semibold">Email</th>
                            <th class="px-6 py-4 font-semibold">Phone</th>
                            <th class="px-6 py-4 font-semibold">Role</th>
                            <th class="px-6 py-4 font-semibold">Location</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Active loans</th>
                            <th class="px-6 py-4 font-semibold">Joined</th>
                            <th class="px-6 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-slate-50">
                        @forelse ($members as $member)
                            <tr>
                                <td class="px-6 py-4">
                                    <input type="checkbox" x-model="selectedMembers" value="{{ $member->id }}" class="h-4 w-4 rounded border-slate-300 text-emerald-600" />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500 text-sm font-semibold text-white">
                                            {{ strtoupper(substr($member->full_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $member->full_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $member->national_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $member->email }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $member->phone_number }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $member->role?->role_name ?? 'Member' }}</td>
                                <td class="px-6 py-4 text-slate-700">
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ ucfirst($member->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700" x-text="membersData.find(item => item.id === {{ $member->id }})?.active_loans_count ?? 0">
                                        0
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $member->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" x-on:click="openEdit({{ $member->id }})" class="rounded-3xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                                        @if(in_array(Auth::user()->role?->role_name, ['Admin', 'Treasurer'], true))
                                            <button
                                                type="button"
                                                x-on:click="openLoanDeduction({{ $member->id }})"
                                                class="rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                                x-bind:disabled="!membersData.find(item => item.id === {{ $member->id }})?.active_loans_count"
                                            >
                                                Loan deduction
                                            </button>
                                        @endif
                                        <form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-3xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-sm text-slate-500">No members have been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <x-modal open="loanDeductionOpen" title="Record loan deduction" maxWidth="2xl">
            <form method="POST" action="{{ route('payments.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                <input type="hidden" name="payment_type" value="inbound" />
                <input type="hidden" name="category" value="loan_repayment" />
                <input type="hidden" name="user_id" x-bind:value="deductMember?.id" />

                <div class="sm:col-span-2">
                    <p class="text-sm font-semibold text-slate-900">Member</p>
                    <p class="mt-1 text-sm text-slate-500" x-text="deductMember ? deductMember.full_name : 'Select a member first'">Member name</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Loan</label>
                    <select name="loan_id" class="premium-select" x-model="deductLoanId" required>
                        <option value="">Select active loan</option>
                        <template x-for="loan in deductMember?.active_loans ?? []" :key="loan.id">
                            <option x-bind:value="loan.id" x-text="`KES ${loan.loan_amount} - ${loan.purpose}`"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Amount</label>
                    <input type="number" name="amount" min="1" step="0.01" x-model="deductAmount" class="premium-input" placeholder="Enter deduction amount" required />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Channel</label>
                    <select name="payment_method" class="premium-select" x-model="deductPaymentMethod" required>
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank</option>
                        <option value="card">Card</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" class="premium-select" x-model="deductStatus" required>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Transaction date</label>
                    <input type="date" name="transaction_date" x-model="deductTransactionDate" class="premium-input" required />
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Reference</label>
                    <input type="text" name="reference_number" x-model="deductReference" class="premium-input" placeholder="Enter reference" required />
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
                    <textarea name="notes" rows="4" class="premium-input" placeholder="Optional notes"></textarea>
                </div>

                <div class="flex flex-wrap justify-end gap-3 sm:col-span-2">
                    <x-button variant="secondary" type="button" x-on:click="loanDeductionOpen = false">Cancel</x-button>
                    <x-button type="submit" x-bind:disabled="!deductMember || !deductLoanId || !deductAmount">Record deduction</x-button>
                </div>
            </form>
        </x-modal>

        @if(Auth::user()->role?->role_name === 'Admin')
        <x-modal open="openCreateModal" title="Create member account" maxWidth="2xl">
            <form method="POST" action="{{ route('members.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf

                <div class="sm:col-span-2">
                    <label for="full_name" class="mb-2 block text-sm font-semibold text-slate-700">Full Name</label>
                    <input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" class="premium-input" required />
                    @error('full_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="national_id" class="mb-2 block text-sm font-semibold text-slate-700">National ID</label>
                    <input id="national_id" name="national_id" type="text" value="{{ old('national_id') }}" class="premium-input" required />
                    @error('national_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="premium-input" required />
                    @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone_number" class="mb-2 block text-sm font-semibold text-slate-700">Phone</label>
                    <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" class="premium-input" required />
                    @error('phone_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
                    <select id="role" name="role" class="premium-select" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->role_name }}" {{ old('role') === $role->role_name ? 'selected' : '' }}>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="location" class="mb-2 block text-sm font-semibold text-slate-700">Location</label>
                    <input id="location" name="location" type="text" value="{{ old('location') }}" class="premium-input" />
                    @error('location')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input id="password" name="password" type="password" class="premium-input" required />
                    @error('password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="premium-input" required />
                </div>

                <div class="flex flex-wrap justify-end gap-3 sm:col-span-2">
                    <x-button variant="secondary" type="button" x-on:click="openCreateModal = false">Cancel</x-button>
                    <x-button type="submit">Create member</x-button>
                </div>
            </form>
        </x-modal>

        <x-modal open="openEditModal" title="Update member" maxWidth="2xl">
            <form method="POST" x-bind:action="editingMember ? '{{ url('/members') }}/' + editingMember.id : '#'" class="grid gap-5 sm:grid-cols-2">
                @csrf
                @method('PATCH')

                <div class="sm:col-span-2">
                    <label for="edit_full_name" class="mb-2 block text-sm font-semibold text-slate-700">Full Name</label>
                    <input id="edit_full_name" name="full_name" type="text" x-model="editingMember.full_name" class="premium-input" required />
                </div>

                <div>
                    <label for="edit_national_id" class="mb-2 block text-sm font-semibold text-slate-700">National ID</label>
                    <input id="edit_national_id" name="national_id" type="text" x-model="editingMember.national_id" class="premium-input" required />
                </div>

                <div>
                    <label for="edit_email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <input id="edit_email" name="email" type="email" x-model="editingMember.email" class="premium-input" required />
                </div>

                <div>
                    <label for="edit_phone_number" class="mb-2 block text-sm font-semibold text-slate-700">Phone</label>
                    <input id="edit_phone_number" name="phone_number" type="tel" x-model="editingMember.phone_number" class="premium-input" required />
                </div>

                <div>
                    <label for="edit_role" class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
                    <select id="edit_role" name="role" x-model="editingMember.role" class="premium-select" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->role_name }}">{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit_status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                    <select id="edit_status" name="status" x-model="editingMember.status" class="premium-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

                <div>
                    <label for="edit_location" class="mb-2 block text-sm font-semibold text-slate-700">Location</label>
                    <input id="edit_location" name="location" type="text" x-model="editingMember.location" class="premium-input" />
                </div>

                <div class="sm:col-span-2">
                    <label for="edit_password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input id="edit_password" name="password" type="password" class="premium-input" placeholder="Leave blank to keep current password" />
                </div>

                <div class="flex flex-wrap justify-end gap-3 sm:col-span-2">
                    <x-button variant="secondary" type="button" x-on:click="openEditModal = false">Cancel</x-button>
                    <x-button type="submit">Update member</x-button>
                </div>
            </form>
        </x-modal>

        <x-modal open="openSmsModal" title="Send SMS to selected members" maxWidth="2xl">
            <form method="POST" action="{{ route('members.sms') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="channel" value="free" />
                <template x-for="memberId in selectedMembers" :key="memberId">
                    <input type="hidden" name="member_ids[]" :value="memberId" />
                </template>

                <div>
                    <label for="sms_message" class="mb-2 block text-sm font-semibold text-slate-700">Message</label>
                    <textarea id="sms_message" name="message" x-model="smsMessage" rows="5" class="premium-input" placeholder="Write your SMS here" required></textarea>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                    <p><strong>Channel:</strong> Free SMS channel</p>
                    <p class="mt-2">This will log the message and phone numbers for selected members. You can replace this with a real SMS gateway later.</p>
                </div>

                <div class="flex flex-wrap justify-end gap-3">
                    <x-button variant="secondary" type="button" x-on:click="openSmsModal = false">Cancel</x-button>
                    <x-button type="submit">Send SMS</x-button>
                </div>
            </form>
        </x-modal>
        @endif
    </div>
</x-app-layout>
