@extends('layouts.app')
@section('page-title', 'Security')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-[#071A33]">Security Overview</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Failed Logins (7d)</p>
            <p class="text-2xl font-bold text-red-600">{{ $failed_logins }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Suspended Users</p>
            <p class="text-2xl font-bold text-orange-600">{{ $suspended_users }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Active Sessions</p>
            <p class="text-2xl font-bold text-[#155EEF]">{{ $active_sessions }}</p>
        </div>
    </div>

    {{-- Security Events --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Recent Security Events</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recent_security_events as $event)
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $event->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3 text-sm text-[#071A33]">{{ $event->user_name ?? 'Unknown' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ str_replace('_', ' ', ucfirst($event->action)) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500 font-mono">{{ $event->ip_address ?? '-' }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $event->result === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $event->result }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No security events.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection