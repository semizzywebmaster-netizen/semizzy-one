@extends('layouts.app')
@section('page-title', 'User Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#071A33]">User: {{ $user->name }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Edit</a>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">← Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- User Info --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-[#155EEF] to-[#00B8A9] rounded-full flex items-center justify-center">
                    <span class="text-white text-2xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#071A33]">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                <div><p class="text-sm text-gray-500">Phone</p><p class="text-sm font-medium">{{ $user->phone ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-500">Status</p><p class="text-sm font-medium">{{ ucfirst($user->status) }}</p></div>
                <div><p class="text-sm text-gray-500">Timezone</p><p class="text-sm font-medium">{{ $user->timezone }}</p></div>
                <div><p class="text-sm text-gray-500">Last Login</p><p class="text-sm font-medium">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</p></div>
                <div><p class="text-sm text-gray-500">Created</p><p class="text-sm font-medium">{{ $user->created_at->format('M d, Y H:i') }}</p></div>
                <div><p class="text-sm text-gray-500">2FA</p><p class="text-sm font-medium">{{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</p></div>
            </div>
        </div>

        {{-- Roles --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-[#071A33] mb-4">Roles</h3>
            <div class="space-y-2">
                @forelse($user->roles as $role)
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm font-medium text-[#071A33]">{{ $role->name }}</p>
                    <p class="text-xs text-gray-500">{{ $role->description }}</p>
                </div>
                @empty
                <p class="text-sm text-gray-500">No roles assigned</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Recent Activity</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($user->auditLogs()->latest()->limit(20)->get() as $log)
            <div class="px-6 py-3 flex justify-between">
                <div>
                    <p class="text-sm text-[#071A33]">{{ str_replace('_', ' ', ucfirst($log->action)) }}</p>
                    <p class="text-xs text-gray-500">{{ $log->ip_address }} • {{ $log->created_at->diffForHumans() }}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full {{ $log->result === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $log->result }}</span>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">No activity recorded.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection