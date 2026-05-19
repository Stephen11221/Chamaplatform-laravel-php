<x-app-layout>
    <div x-data="{ createMeeting: false, replyTo: null, selectedMeeting: {{ $selectedMeeting?->id ?? 'null' }} }" class="space-y-8">
        <section class="premium-card px-6 py-8 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <p class="section-label">Meetings & chat</p>
                    <h1 class="section-heading">Meeting operations</h1>
                    <p class="text-sm leading-7 text-slate-600">
                        Admins can create meetings and send the first announcement. Everyone can reply in the meeting thread.
                    </p>
                </div>

                @if ($canCreateMeeting)
                    <x-button icon="fa-calendar-plus" x-on:click="createMeeting = true">Create meeting</x-button>
                @endif
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Meetings" value="{{ $meetings->count() }}" subtitle="All scheduled sessions" icon="fa-calendar-day" tone="emerald" />
            <x-stat-card title="Messages" value="{{ $meetings->sum('messages_count') }}" subtitle="Threaded replies" icon="fa-comments" tone="blue" />
            <x-stat-card title="Next meeting" value="{{ optional(optional($meetings->first())->meeting_date)->format('d M') ?? '—' }}" subtitle="Upcoming session" icon="fa-clock" tone="gold" />
            <x-stat-card title="Your role" value="{{ ucfirst($role = strtolower(Auth::user()->role?->role_name ?? 'member')) }}" subtitle="Current access level" icon="fa-user-shield" tone="slate" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <x-table-card title="Upcoming meetings" subtitle="Choose a meeting to view the chat thread">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Meeting</th>
                            <th>Venue</th>
                            <th>Chat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($meetings as $meeting)
                            <tr class="{{ $selectedMeeting?->id === $meeting->id ? 'bg-emerald-50/60' : '' }}">
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $meeting->meeting_date?->format('d M Y') }}</td>
                                <td class="px-6 py-4">
                                    <button type="button" class="text-left font-semibold text-slate-900 transition hover:text-emerald-600" x-on:click="selectedMeeting = {{ $meeting->id }}; replyTo = null; window.location = '{{ route('meetings', ['meeting' => $meeting->id]) }}'">
                                        {{ $meeting->title }}
                                    </button>
                                    <p class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-500">{{ $meeting->meeting_type }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $meeting->location }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                        {{ $meeting->messages_count }} messages
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No meetings created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-card>

            <div class="space-y-6">
                <div class="premium-card-muted p-6">
                    @if ($selectedMeeting)
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="section-label">Selected meeting</p>
                                <h2 class="mt-3 text-2xl font-semibold text-slate-900">{{ $selectedMeeting->title }}</h2>
                                <p class="mt-2 text-sm text-slate-500">{{ $selectedMeeting->agenda }}</p>
                            </div>
                            <x-badge variant="success" icon="fa-comments">{{ $selectedMeeting->messages_count }} replies</x-badge>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Date</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $selectedMeeting->meeting_date?->format('l, d F Y') }}</p>
                            </div>
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Time</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $selectedMeeting->meeting_time }}</p>
                            </div>
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Location</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $selectedMeeting->location }}</p>
                            </div>
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Created by</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $selectedMeeting->creator?->name ?? 'Admin' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="section-label">Selected meeting</p>
                        <p class="mt-4 text-sm text-slate-500">Pick a meeting from the table to open the discussion thread.</p>
                    @endif
                </div>

                <div class="premium-card-muted overflow-hidden">
                    <div class="border-b border-slate-200/80 px-6 py-5">
                        <p class="section-label">Chat thread</p>
                        <p class="mt-2 text-sm text-slate-500">Admin announcements appear first, then member replies underneath.</p>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        @if ($selectedMeeting)
                            @forelse ($selectedMeeting->messages->where('parent_id', null) as $message)
                                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-semibold text-slate-900">{{ $message->user?->name ?? 'User' }}</p>
                                                @if ($message->is_announcement)
                                                    <x-badge variant="gold">Announcement</x-badge>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-slate-500">{{ $message->created_at?->diffForHumans() }}</p>
                                        </div>
                                        <button type="button" class="text-xs font-semibold text-emerald-600" x-on:click="replyTo = {{ $message->id }}">Reply</button>
                                    </div>
                                    <p class="mt-4 text-sm leading-7 text-slate-700">{!! nl2br(e($message->message)) !!}</p>

                                    @if ($message->replies->isNotEmpty())
                                        <div class="mt-4 space-y-3 border-l-2 border-emerald-100 pl-4">
                                            @foreach ($message->replies as $reply)
                                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                    <div class="flex items-center justify-between gap-4">
                                                        <p class="text-sm font-semibold text-slate-900">{{ $reply->user?->name ?? 'User' }}</p>
                                                        <p class="text-xs text-slate-500">{{ $reply->created_at?->diffForHumans() }}</p>
                                                    </div>
                                                    <p class="mt-2 text-sm leading-7 text-slate-700">{!! nl2br(e($reply->message)) !!}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                                    <p class="text-sm font-semibold text-slate-900">No chat yet</p>
                                    <p class="mt-1 text-sm text-slate-500">Be the first to reply in this meeting thread.</p>
                                </div>
                            @endforelse
                        @else
                            <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                                <p class="text-sm font-semibold text-slate-900">No meeting selected</p>
                                <p class="mt-1 text-sm text-slate-500">Create or pick a meeting to view the chat and replies.</p>
                            </div>
                        @endif
                    </div>

                    @if ($selectedMeeting)
                        <div class="border-t border-slate-200/80 px-6 py-5">
                            <form method="POST" action="{{ route('meetings.reply', $selectedMeeting) }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="parent_id" :value="replyTo || ''">
                                <div>
                                    <div class="mb-2 flex items-center justify-between">
                                        <label class="text-sm font-semibold text-slate-700">Write a reply</label>
                                        <button type="button" x-show="replyTo" x-cloak class="text-xs font-semibold text-slate-500" x-on:click="replyTo = null">Clear reply target</button>
                                    </div>
                                    <textarea name="message" rows="4" class="premium-input" placeholder="Send a chat message, question, or update to everyone in this meeting." required>{{ old('message') }}</textarea>
                                    @error('message')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                @error('parent_id')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <div class="flex flex-wrap justify-end gap-3">
                                    <x-button type="submit" icon="fa-paper-plane">Send reply</x-button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @if ($canCreateMeeting)
            <x-modal open="createMeeting" title="Create a meeting" maxWidth="2xl">
                <form method="POST" action="{{ route('meetings.store') }}" class="grid gap-5 sm:grid-cols-2">
                    @csrf
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Meeting title</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="premium-input" placeholder="Enter meeting title" required />
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Date</label>
                        <input type="date" name="meeting_date" value="{{ old('meeting_date') }}" class="premium-input" required />
                        @error('meeting_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Time</label>
                        <input type="time" name="meeting_time" value="{{ old('meeting_time') }}" class="premium-input" required />
                        @error('meeting_time')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Venue</label>
                        <input type="text" name="location" value="{{ old('location') }}" class="premium-input" placeholder="Enter venue" required />
                        @error('location')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Meeting type</label>
                        <select name="meeting_type" class="premium-select" required>
                            <option value="">Select type</option>
                            <option value="physical" @selected(old('meeting_type') === 'physical')>Physical</option>
                            <option value="virtual" @selected(old('meeting_type') === 'virtual')>Virtual</option>
                            <option value="hybrid" @selected(old('meeting_type') === 'hybrid')>Hybrid</option>
                        </select>
                        @error('meeting_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Agenda</label>
                        <textarea name="agenda" rows="4" class="premium-input" placeholder="Outline key points for discussion" required>{{ old('agenda') }}</textarea>
                        @error('agenda')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap justify-end gap-3">
                        <x-button variant="secondary" type="button" x-on:click="createMeeting = false">Cancel</x-button>
                        <x-button type="submit">Save meeting</x-button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</x-app-layout>
