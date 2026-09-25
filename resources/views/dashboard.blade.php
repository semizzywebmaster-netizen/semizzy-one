@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Welcome Section --}}
    <div class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] rounded-2xl p-6 sm:p-8 text-white">
        <h1 class="text-2xl sm:text-3xl font-bold">Welcome back, {{ $user->name }}!</h1>
        <p class="mt-2 text-white/80">Here's what's happening with your account today.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($roles as $role)
                <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-medium">{{ $role }}</span>
            @endforeach
        </div>
    </div>

    {{-- Stats Grid --}}
    @if(!empty($stats))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @if(isset($stats['total_users']))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="text-2xl font-bold text-[#071A33]">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#155EEF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        @if(isset($stats['active_users']))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Users</p>
                    <p class="text-2xl font-bold text-[#071A33]">{{ number_format($stats['active_users']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#00B8A9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        @if(isset($stats['active_addons']))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Addons</p>
                    <p class="text-2xl font-bold text-[#071A33]">{{ number_format($stats['active_addons']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        @if(isset($stats['active_providers']))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Providers</p>
                    <p class="text-2xl font-bold text-[#071A33]">{{ number_format($stats['active_providers']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                    </svg>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Recent Activity --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Recent Activity</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentActivity as $log)
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-[#071A33]">{{ str_replace('_', ' ', ucfirst($log->action)) }}</p>
                    <p class="text-xs text-gray-500">{{ $log->user_name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full {{ $log->result === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $log->result }}
                </span>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                <p>No recent activity</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-[#071A33] mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @if($user->hasRole('admin'))
            <a href="{{ route('admin.users.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-[#155EEF] hover:bg-blue-50/50 transition-all">
                <svg class="w-6 h-6 text-[#155EEF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span class="text-xs font-medium text-gray-700">Add User</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-[#155EEF] hover:bg-blue-50/50 transition-all">
                <svg class="w-6 h-6 text-[#155EEF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                </svg>
                <span class="text-xs font-medium text-gray-700">Settings</span>
            </a>
            <a href="{{ route('admin.health.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-[#155EEF] hover:bg-blue-50/50 transition-all">
                <svg class="w-6 h-6 text-[#00B8A9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span class="text-xs font-medium text-gray-700">Health</span>
            </a>
            <a href="{{ route('admin.addons.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 hover:border-[#155EEF] hover:bg-blue-50/50 transition-all">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="text-xs font-medium text-gray-700">Addons</span>
            </a>
            @endif
        </div>
    </div>
</div>
@endsection