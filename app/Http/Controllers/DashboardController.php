<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $role = strtolower(Auth::user()->role?->role_name ?? 'member');

        $data = [
            'today' => now()->format('l, d F Y'),
            'role' => $role,
        ];

        if ($role === 'secretary') {
            $data['members_count'] = User::count();
            $data['meetings_count'] = Meeting::where('meeting_date', '>=', today())->count();
            $data['reports_count'] = 0; // Placeholder until Report model is created
            $data['notifications_count'] = 0; // Placeholder until SystemNotification model is created
            $data['upcoming_meetings'] = Meeting::where('meeting_date', '>=', today())
                ->orderBy('meeting_date')
                ->orderBy('meeting_time')
                ->limit(5)
                ->get();
        }

        $view = match ($role) {
            'admin' => 'pages.dashboards.admin',
            'treasurer' => 'pages.dashboards.treasurer',
            'secretary' => 'pages.dashboards.secretary',
            default => 'pages.dashboards.member',
        };

        return view($view, $data);
    }
}
