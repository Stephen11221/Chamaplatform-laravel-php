<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class MembersController extends Controller
{
    public function index(): View
    {
        $members = User::with('role')->orderByDesc('created_at')->get();
        $roles = Role::orderBy('role_name')->get();

        $totalMembers = $members->count();
        $activeMembers = $members->where('status', 'active')->count();
        $leaderCount = $members->whereIn('role.role_name', ['Admin', 'Treasurer', 'Secretary'])->count();
        $adminCount = $members->where('role.role_name', 'Admin')->count();

        $activeLoans = Loan::whereIn('user_id', $members->pluck('id'))
            ->where('approval_status', 'approved')
            ->where('repayment_status', 'active')
            ->get()
            ->groupBy('user_id');

        $membersData = $members->map(fn ($member) => [
            'id' => $member->id,
            'full_name' => $member->full_name,
            'national_id' => $member->national_id,
            'email' => $member->email,
            'phone_number' => $member->phone_number,
            'role' => $member->role?->role_name ?? 'Member',
            'location' => $member->location,
            'status' => $member->status,
            'active_loans_count' => $activeLoans->has($member->id) ? $activeLoans->get($member->id)->count() : 0,
            'active_loans' => $activeLoans->has($member->id)
                ? $activeLoans->get($member->id)->map(fn ($loan) => [
                    'id' => $loan->id,
                    'purpose' => $loan->purpose,
                    'loan_amount' => number_format((float) $loan->loan_amount, 2),
                ])->all()
                : [],
        ])->all();

        return view('pages.members.index', compact('members', 'roles', 'totalMembers', 'activeMembers', 'leaderCount', 'adminCount', 'membersData'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::user()->role?->role_name !== 'Admin') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'string', 'max:30', 'unique:users,national_id'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:Admin,Treasurer,Secretary,Member'],
            'location' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = Role::where('role_name', $request->role)->firstOrFail();

        User::create([
            'full_name' => $request->full_name,
            'national_id' => $request->national_id,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role_id' => $role->id,
            'location' => $request->location,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('members')->with('success', 'Member account created successfully.');
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        if (Auth::user()->role?->role_name !== 'Admin') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'string', 'max:30', Rule::unique('users', 'national_id')->ignore($member->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member->id)],
            'phone_number' => ['required', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:Admin,Treasurer,Secretary,Member'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
            'location' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $role = Role::where('role_name', $request->role)->firstOrFail();

        $member->fill([
            'full_name' => $request->full_name,
            'national_id' => $request->national_id,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role_id' => $role->id,
            'location' => $request->location,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $member->password = Hash::make($request->password);
        }

        $member->save();

        return redirect()->route('members')->with('success', 'Member account updated successfully.');
    }

    public function destroy(User $member): RedirectResponse
    {
        if (Auth::user()->role?->role_name !== 'Admin') {
            abort(Response::HTTP_FORBIDDEN);
        }

        if ($member->id === Auth::id()) {
            return redirect()->route('members')->with('success', 'You cannot delete your own account.');
        }

        $member->delete();

        return redirect()->route('members')->with('success', 'Member deleted successfully.');
    }

    public function sendSms(Request $request): RedirectResponse
    {
        if (Auth::user()->role?->role_name !== 'Admin') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'member_ids' => ['required', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'message' => ['required', 'string', 'max:1000'],
            'channel' => ['required', 'string', 'in:free'],
        ]);

        $members = User::whereIn('id', $data['member_ids'])->get();

        foreach ($members as $member) {
            logger()->info('Free SMS sent', [
                'phone_number' => $member->phone_number,
                'message' => $data['message'],
                'member_id' => $member->id,
            ]);
        }

        return redirect()->route('members')->with('success', 'SMS queued for selected members using the free channel.');
    }
}
