<x-app-layout>
    <div x-data="{ openCreateModal: false }" class="space-y-8">
        <section class="premium-card px-6 py-8 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <p class="section-label">Members</p>
                    <h1 class="section-heading">Premium member management</h1>
                    <p class="text-sm leading-7 text-slate-600">Create member login accounts, track roles, and keep all member records in the same secure user table.</p>
                </div>

                @if(Auth::user()->role?->role_name === 'Admin')
                    <x-button icon="fa-user-plus" x-on:click="openCreateModal = true">Add member</x-button>
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
        </section>

        <section class="premium-card p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-950 text-left text-xs uppercase tracking-[0.24em] text-slate-300">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Member</th>
                            <th class="px-6 py-4 font-semibold">Email</th>
                            <th class="px-6 py-4 font-semibold">Phone</th>
                            <th class="px-6 py-4 font-semibold">Role</th>
                            <th class="px-6 py-4 font-semibold">Location</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-slate-50">
                        @forelse ($members as $member)
                            <tr>
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
                                <td class="px-6 py-4 text-slate-700">{{ $member->location ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ ucfirst($member->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $member->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">No members have been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

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
        @endif
    </div>
</x-app-layout>
