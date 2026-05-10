@php
    $mobile = $mobile ?? false;
    $user = auth()->user();
    $role = strtolower($user->role?->role_name ?? 'member');
    $initials = collect(explode(' ', $user->name ?? 'CU'))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');
    $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : null;

    $sectionsByRole = [
        'admin' => [
            [
                'title' => 'Overview',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fa-chart-line', 'active' => 'dashboard'],
                ],
            ],
            [
                'title' => 'Operations',
                'items' => [
                    ['label' => 'Members', 'route' => 'members', 'icon' => 'fa-users', 'active' => 'members'],
                    ['label' => 'Meetings', 'route' => 'meetings', 'icon' => 'fa-calendar-days', 'active' => 'meetings'],
                    ['label' => 'Contributions', 'route' => 'contributions', 'icon' => 'fa-wallet', 'active' => 'contributions'],
                    ['label' => 'Loans', 'route' => 'loans', 'icon' => 'fa-hand-holding-dollar', 'active' => 'loans'],
                    ['label' => 'Investments', 'route' => 'investments', 'icon' => 'fa-building-columns', 'active' => 'investments'],
                    ['label' => 'Payments', 'route' => 'payments', 'icon' => 'fa-receipt', 'active' => 'payments'],
                ],
            ],
            [
                'title' => 'Insights',
                'items' => [
                    ['label' => 'Reports', 'route' => 'reports', 'icon' => 'fa-chart-pie', 'active' => 'reports'],
                    ['label' => 'Meetings', 'route' => 'meetings', 'icon' => 'fa-calendar-days', 'active' => 'meetings'],
                    ['label' => 'Notifications', 'route' => 'notifications', 'icon' => 'fa-bell', 'active' => 'notifications'],
                    ['label' => 'Audit Logs', 'route' => 'audit', 'icon' => 'fa-shield-halved', 'active' => 'audit'],
                ],
            ],
            [
                'title' => 'Account',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'fa-user-gear', 'active' => 'profile.*'],
                    ['label' => 'Settings', 'route' => 'settings', 'icon' => 'fa-gears', 'active' => 'settings'],
                ],
            ],
        ],
        'treasurer' => [
            [
                'title' => 'Treasury',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fa-chart-line', 'active' => 'dashboard'],
                    ['label' => 'Contributions', 'route' => 'contributions', 'icon' => 'fa-wallet', 'active' => 'contributions'],
                    ['label' => 'Payments', 'route' => 'payments', 'icon' => 'fa-receipt', 'active' => 'payments'],
                    ['label' => 'Loans', 'route' => 'loans', 'icon' => 'fa-hand-holding-dollar', 'active' => 'loans'],
                    ['label' => 'Reports', 'route' => 'reports', 'icon' => 'fa-chart-pie', 'active' => 'reports'],
                ],
            ],
            [
                'title' => 'Coordination',
                'items' => [
                    ['label' => 'Meetings', 'route' => 'meetings', 'icon' => 'fa-calendar-days', 'active' => 'meetings'],
                    ['label' => 'Notifications', 'route' => 'notifications', 'icon' => 'fa-bell', 'active' => 'notifications'],
                ],
            ],
            [
                'title' => 'Account',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'fa-user-gear', 'active' => 'profile.*'],
                ],
            ],
        ],
        'secretary' => [
            [
                'title' => 'Secretariat',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fa-chart-line', 'active' => 'dashboard'],
                    ['label' => 'Meetings', 'route' => 'meetings', 'icon' => 'fa-calendar-days', 'active' => 'meetings'],
                    ['label' => 'Members', 'route' => 'members', 'icon' => 'fa-users', 'active' => 'members'],
                    ['label' => 'Notifications', 'route' => 'notifications', 'icon' => 'fa-bell', 'active' => 'notifications'],
                    ['label' => 'Reports', 'route' => 'reports', 'icon' => 'fa-chart-pie', 'active' => 'reports'],
                ],
            ],
            [
                'title' => 'Account',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'fa-user-gear', 'active' => 'profile.*'],
                    ['label' => 'Settings', 'route' => 'settings', 'icon' => 'fa-gears', 'active' => 'settings'],
                ],
            ],
        ],
        'member' => [
            [
                'title' => 'Member Space',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fa-chart-line', 'active' => 'dashboard'],
                    ['label' => 'Contributions', 'route' => 'contributions', 'icon' => 'fa-wallet', 'active' => 'contributions'],
                    ['label' => 'Loans', 'route' => 'loans', 'icon' => 'fa-hand-holding-dollar', 'active' => 'loans'],
                    ['label' => 'Payments', 'route' => 'payments', 'icon' => 'fa-receipt', 'active' => 'payments'],
                    ['label' => 'Meetings', 'route' => 'meetings', 'icon' => 'fa-calendar-days', 'active' => 'meetings'],
                    ['label' => 'Notifications', 'route' => 'notifications', 'icon' => 'fa-bell', 'active' => 'notifications'],
                ],
            ],
            [
                'title' => 'Account',
                'items' => [
                    ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'fa-user-gear', 'active' => 'profile.*'],
                ],
            ],
        ],
    ];

    $sections = $sectionsByRole[$role] ?? $sectionsByRole['member'];
    $roleLabel = match ($role) {
        'admin' => 'Administrator',
        'treasurer' => 'Treasurer',
        'secretary' => 'Secretary',
        default => 'Member',
    };
    $heroTitle = match ($role) {
        'admin' => 'Admin console',
        'treasurer' => 'Treasury desk',
        'secretary' => 'Secretariat hub',
        default => 'Member portal',
    };
    $roleCopy = match ($role) {
        'admin' => 'Full platform oversight and user management.',
        'treasurer' => 'Money movement, reconciliations, and treasury reporting.',
        'secretary' => 'Meetings, member coordination, and communication.',
        default => 'Your contributions, loans, payments, and updates.',
    };
    $heroSubtitle = match ($role) {
        'admin' => 'Govern the platform, manage users, and monitor operations.',
        'treasurer' => 'Track collections, payments, and loan performance.',
        'secretary' => 'Coordinate members, meetings, and communication.',
        default => 'See your savings, loans, and payment activity at a glance.',
    };
@endphp

@if ($mobile)
    <div class="fixed inset-0 z-50 lg:hidden" x-show="sidebarOpen" x-cloak>
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" x-on:click="sidebarOpen = false"></div>

        <aside class="relative z-10 flex h-full w-80 max-w-[90vw] flex-col border-r border-white/10 bg-gradient-to-b from-[#1e3a5f] via-[#12253d] to-[#0f172a] text-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30">
                        <i class="fa-solid fa-piggy-bank"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Chama Platform</p>
                        <p class="text-xs text-slate-300">{{ $roleLabel }}</p>
                        <p class="text-xs text-slate-300">{{ $heroTitle }}</p>
                    </div>
                </div>
                <button type="button" x-on:click="sidebarOpen = false" class="rounded-2xl border border-white/10 bg-white/10 px-3 py-2 text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-5">
                <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-300">Signed in as</p>
                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/15 font-semibold text-white">
                            @if ($photo)
                                <img src="{{ $photo }}" alt="{{ $user->name ?? 'Member' }}" class="h-full w-full object-cover" />
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold">{{ $user->name ?? 'Member' }}</p>
                            <p class="text-xs text-slate-300">{{ $user->role?->role_name ?? 'Member' }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-300">{{ $roleCopy }}</p>
                    <p class="mt-4 text-sm text-slate-300">{{ $heroSubtitle }}</p>
                </div>

                <div class="mt-5 space-y-5">
                    @foreach ($sections as $section)
                        <div>
                            <p class="px-2 text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">{{ $section['title'] }}</p>

                            <nav class="mt-2 space-y-2">
                                @foreach ($section['items'] as $item)
                                    <a href="{{ route($item['route']) }}" class="sidebar-link {{ request()->routeIs($item['active']) ? 'sidebar-link-active' : '' }}">
                                        <i class="fa-solid {{ $item['icon'] }} w-5 text-center text-slate-300"></i>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-white/10 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="premium-button premium-button-primary w-full">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>
    </div>
@else
    <aside class="sticky top-0 hidden h-screen w-80 shrink-0 border-r border-white/10 bg-gradient-to-b from-[#1e3a5f] via-[#12253d] to-[#0f172a] text-white shadow-[0_30px_100px_-45px_rgba(15,23,42,0.9)] lg:fixed lg:left-0 lg:top-0 lg:z-40 lg:flex lg:flex-col">
        <div class="flex items-center gap-3 border-b border-white/10 px-8 py-6">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30">
                <i class="fa-solid fa-piggy-bank"></i>
            </div>
            <div>
                <p class="text-base font-semibold tracking-tight">Chama Platform</p>
                <p class="text-xs text-slate-300">{{ $roleLabel }}</p>
                <p class="text-xs text-slate-300">{{ $heroTitle }}</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-6">
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-300">Welcome back</p>
                <div class="mt-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/15 font-semibold text-white">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $user->name ?? 'Member' }}" class="h-full w-full object-cover" />
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $user->name ?? 'Member' }}</p>
                        <p class="text-xs text-slate-300">{{ $user->role?->role_name ?? 'Member' }}</p>
                    </div>
                </div>
                <p class="mt-4 text-sm text-slate-300">{{ $roleCopy }}</p>
                <p class="mt-4 text-sm text-slate-300">{{ $heroSubtitle }}</p>
            </div>

            <div class="mt-6 space-y-5">
                @foreach ($sections as $section)
                    <div>
                        <p class="px-2 text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">{{ $section['title'] }}</p>
                        <p class="px-2 text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                            {{ $section['title'] }}
                        </p>

                        <nav class="mt-2 space-y-2">
                            @foreach ($section['items'] as $item)
                                <a href="{{ route($item['route']) }}" class="sidebar-link {{ request()->routeIs($item['active']) ? 'sidebar-link-active' : '' }}">
                                    <i class="fa-solid {{ $item['icon'] }} w-5 text-center text-slate-300"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-white/10 p-5">
            <div class="rounded-[1.5rem] border border-emerald-400/20 bg-emerald-500/10 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-100">Workspace</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ $roleLabel }}</p>
                <p class="mt-1 text-sm text-slate-300">{{ $roleCopy }}</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ $heroTitle }}</p>
                <p class="mt-1 text-sm text-slate-300">{{ $heroSubtitle }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="premium-button premium-button-primary w-full">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Sign out
                </button>
            </form>
        </div>
    </aside>
@endif
