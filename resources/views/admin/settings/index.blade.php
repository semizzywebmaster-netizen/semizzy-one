@extends('layouts.app')
@section('page-title', 'Settings')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-[#071A33]">Settings</h1>

    {{-- Application Settings --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Application</h3>
        </div>
        <form method="POST" action="{{ route('admin.settings.update', 'app') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">App Name</label>
                    <input type="text" name="name" value="{{ $groups['app']['name'] ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Tagline</label>
                    <input type="text" name="tagline" value="{{ $groups['app']['tagline'] ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Timezone</label>
                    <input type="text" name="timezone" value="{{ $groups['app']['timezone'] ?? 'UTC' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Locale</label>
                    <input type="text" name="locale" value="{{ $groups['app']['locale'] ?? 'en' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Date Format</label>
                    <input type="text" name="date_format" value="{{ $groups['app']['date_format'] ?? 'Y-m-d' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <button type="submit" class="bg-[#155EEF] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#155EEF]/90">Save App Settings</button>
        </form>
    </div>

    {{-- Security Settings --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Security</h3>
        </div>
        <form method="POST" action="{{ route('admin.settings.update', 'security') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Max Login Attempts</label>
                    <input type="number" name="max_login_attempts" value="{{ $groups['security']['max_login_attempts'] ?? 5 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Lockout Duration (seconds)</label>
                    <input type="number" name="lockout_duration" value="{{ $groups['security']['lockout_duration'] ?? 900 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Session Lifetime (minutes)</label>
                    <input type="number" name="session_lifetime" value="{{ $groups['security']['session_lifetime'] ?? 120 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Min Password Length</label>
                    <input type="number" name="password_min_length" value="{{ $groups['security']['password_min_length'] ?? 8 }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <button type="submit" class="bg-[#155EEF] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#155EEF]/90">Save Security Settings</button>
        </form>
    </div>

    {{-- Brand Settings --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Brand Colors</h3>
        </div>
        <form method="POST" action="{{ route('admin.settings.update', 'brand') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Primary Blue</label>
                    <div class="flex gap-2">
                        <input type="color" value="{{ $groups['brand']['primary_color'] ?? '#155EEF' }}" class="w-10 h-10 rounded cursor-pointer" onchange="this.nextElementSibling.value=this.value">
                        <input type="text" name="primary_color" value="{{ $groups['brand']['primary_color'] ?? '#155EEF' }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Teal</label>
                    <div class="flex gap-2">
                        <input type="color" value="{{ $groups['brand']['teal_color'] ?? '#00B8A9' }}" class="w-10 h-10 rounded cursor-pointer" onchange="this.nextElementSibling.value=this.value">
                        <input type="text" name="teal_color" value="{{ $groups['brand']['teal_color'] ?? '#00B8A9' }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Navy</label>
                    <div class="flex gap-2">
                        <input type="color" value="{{ $groups['brand']['navy_color'] ?? '#071A33' }}" class="w-10 h-10 rounded cursor-pointer" onchange="this.nextElementSibling.value=this.value">
                        <input type="text" name="navy_color" value="{{ $groups['brand']['navy_color'] ?? '#071A33' }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>
            <button type="submit" class="bg-[#155EEF] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#155EEF]/90">Save Brand Settings</button>
        </form>
    </div>
</div>
@endsection