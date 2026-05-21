<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditController extends Controller
{
    protected function availableModules(): array
    {
        return [
            'Dashboard',
            'Members',
            'Contributions',
            'Loans',
            'Investments',
            'Meetings',
            'Payments',
            'Reports',
            'Settings',
            'Notifications',
            'Profile',
            'Audit',
        ];
    }

    public function index(): View
    {
        $today = now()->toDateString();
        $ipAddress = request()->ip();
        $deviceInfo = request()->userAgent();

        $eventsToday = ActivityLog::whereDate('created_at', $today)->count();
        $criticalEvents = ActivityLog::where('activity', 'like', '%fail%')
            ->orWhere('activity', 'like', '%error%')
            ->orWhere('activity', 'like', '%unauthorized%')
            ->count();
        $approvalEvents = ActivityLog::where('activity', 'like', '%approve%')
            ->orWhere('activity', 'like', '%approval%')
            ->count();
        $userActions = ActivityLog::select('user_id')->distinct()->count();
        $recentEvents = ActivityLog::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $modules = $this->availableModules();

        return view('pages.audit.index', compact(
            'eventsToday',
            'criticalEvents',
            'approvalEvents',
            'userActions',
            'recentEvents',
            'modules',
            'ipAddress',
            'deviceInfo'
        ));
    }

    public function store(Request $request)
    {
        $modules = $this->availableModules();

        $data = $request->validate([
            'module' => ['required', 'string', 'max:255', Rule::in($modules)],
            'activity' => 'required|string|max:1000',
            'ip_address' => 'nullable|string|max:45',
            'device_info' => 'nullable|string|max:2000',
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => $data['module'],
            'activity' => $data['activity'],
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'device_info' => $data['device_info'] ?? request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('audit')->with('success', 'Audit event recorded successfully.');
    }
}
