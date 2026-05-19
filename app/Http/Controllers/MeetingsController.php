<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MeetingsController extends Controller
{
    public function index(Request $request): View
    {
        $meetings = Meeting::query()
            ->with(['creator', 'messages.user', 'messages.replies.user'])
            ->withCount(['messages'])
            ->orderByDesc('meeting_date')
            ->orderByDesc('meeting_time')
            ->get();

        $selectedMeeting = $request->integer('meeting')
            ? $meetings->firstWhere('id', $request->integer('meeting'))
            : $meetings->first();

        if (! $selectedMeeting && $meetings->isNotEmpty()) {
            $selectedMeeting = $meetings->first();
        }

        return view('pages.meetings.index', [
            'meetings' => $meetings,
            'selectedMeeting' => $selectedMeeting,
            'isAdmin' => $this->isAdmin(),
            'canCreateMeeting' => $this->isAdmin() || $this->isSecretary(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureCanCreateMeeting();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'meeting_time' => ['required'],
            'location' => ['required', 'string', 'max:255'],
            'meeting_type' => ['required', Rule::in(['physical', 'virtual', 'hybrid'])],
            'agenda' => ['required', 'string'],
        ]);

        $meeting = Meeting::create([
            'title' => $data['title'],
            'meeting_date' => $data['meeting_date'],
            'meeting_time' => $data['meeting_time'],
            'location' => $data['location'],
            'meeting_type' => $data['meeting_type'],
            'status' => 'upcoming',
            'agenda' => $data['agenda'],
            'created_by' => Auth::id(),
        ]);

        MeetingMessage::create([
            'meeting_id' => $meeting->id,
            'user_id' => Auth::id(),
            'message' => "Meeting created: {$meeting->title}\n\nAgenda: {$meeting->agenda}",
            'is_announcement' => true,
        ]);

        return redirect()
            ->route('meetings', ['meeting' => $meeting->id])
            ->with('success', 'Meeting created successfully.');
    }

    public function reply(Request $request, Meeting $meeting): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:meeting_messages,id'],
        ]);

        MeetingMessage::create([
            'meeting_id' => $meeting->id,
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?? null,
            'message' => $data['message'],
            'is_announcement' => false,
        ]);

        return redirect()
            ->route('meetings', ['meeting' => $meeting->id])
            ->with('success', 'Reply posted.');
    }

    private function isAdmin(): bool
    {
        return strtolower(Auth::user()->role?->role_name ?? '') === 'admin';
    }

    private function isSecretary(): bool
    {
        return strtolower(Auth::user()->role?->role_name ?? '') === 'secretary';
    }

    private function ensureCanCreateMeeting(): void
    {
        abort_unless($this->isAdmin() || $this->isSecretary(), 403);
    }
}
