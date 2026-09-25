<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $groups = [
            'app' => Setting::getGroup('app'),
            'security' => Setting::getGroup('security'),
            'notifications' => Setting::getGroup('notifications'),
            'pwa' => Setting::getGroup('pwa'),
            'brand' => Setting::getGroup('brand'),
        ];

        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request, string $group)
    {
        $allowedGroups = ['app', 'security', 'notifications', 'pwa', 'brand'];

        if (!in_array($group, $allowedGroups)) {
            return back()->with('error', 'Invalid settings group.');
        }

        $validated = $request->validate($this->getValidationRules($group));

        foreach ($validated as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_array($value) => 'json',
                default => 'string',
            };

            Setting::set($group, $key, $value, $type);
        }

        AuditService::settingsUpdated($group);

        return back()->with('success', ucfirst($group) . ' settings updated successfully.');
    }

    private function getValidationRules(string $group): array
    {
        return match ($group) {
            'app' => [
                'name' => 'required|string|max:255',
                'tagline' => 'nullable|string|max:500',
                'timezone' => 'required|string|max:64',
                'locale' => 'required|string|max:10',
                'date_format' => 'required|string|max:30',
            ],
            'security' => [
                'max_login_attempts' => 'required|integer|min:1|max:20',
                'lockout_duration' => 'required|integer|min:60|max:3600',
                'session_lifetime' => 'required|integer|min:5|max:1440',
                'password_min_length' => 'required|integer|min:6|max:32',
            ],
            'notifications' => [
                'email_enabled' => 'boolean',
                'sms_enabled' => 'boolean',
                'push_enabled' => 'boolean',
            ],
            'pwa' => [
                'enabled' => 'boolean',
                'short_name' => 'nullable|string|max:12',
            ],
            'brand' => [
                'primary_color' => 'required|string|max:9',
                'teal_color' => 'required|string|max:9',
                'navy_color' => 'required|string|max:9',
            ],
            default => [],
        };
    }
}