<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\AuditLog;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $data = [
            'user' => $user,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions(),
            'stats' => $this->getStats($user),
            'recentActivity' => $this->getRecentActivity(),
        ];

        return view('dashboard', $data);
    }

    private function getStats($user): array
    {
        if ($user->hasRole('admin')) {
            return [
                'total_users' => User::count(),
                'active_users' => User::active()->count(),
                'active_addons' => Addon::active()->count(),
                'active_providers' => Provider::active()->count(),
            ];
        }

        return [];
    }

    private function getRecentActivity()
    {
        return AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();
    }
}