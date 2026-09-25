<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index()
    {
        $data = [
            'failed_logins' => AuditLog::where('action', 'auth.login.failure')
                ->recent(7)
                ->count(),
            'suspended_users' => User::suspended()->count(),
            'active_sessions' => \DB::table('sessions')
                ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
                ->count(),
            'recent_security_events' => AuditLog::whereIn('action', [
                'auth.login.failure',
                'auth.password.changed',
                'user.suspended',
                'permission.changed',
            ])->latest()->limit(50)->get(),
        ];

        return view('admin.security.index', $data);
    }
}