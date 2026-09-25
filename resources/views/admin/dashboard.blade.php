@extends('layouts.app')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-[#071A33]">{{ number_format($stats['total_users']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Active Users</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active_users']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Suspended</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['suspended_users']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Active Addons</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['active_addons']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Active Providers</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['active_providers']) }}</p>
        </div>
    </div>

    {{-- System Status --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">System Status</h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($system as $key => $check)
            <div class="flex items-center gap-3 p-3 rounded-lg {{ $check['status'] === 'healthy' ? 'bg-green-50' : ($check['status'] === 'warning' ? 'bg-yellow-50' : 'bg-red-50') }}">
                <div class="w-3 h-3 rounded-full {{ $check['status'] === 'healthy' ? 'bg-green-500' : ($check['status'] === 'warning' ? 'bg-yellow-500' : 'bg-red-500') }}"></div>
                <div>
                    <p class="text-sm font-medium text-[#071A33]">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                    <p class="text-xs text-gray-500">{{ $check['message'] ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent Audit Logs --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-[#071A33]">Recent Audit Activity</h3>
            <a href="{{ route('admin.audit.index') }}" class="text-sm text-[#155EEF] font-medium hover:text-[#00B8A9]">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recent_audit as $log)
                    <tr>
                        <td class="px-6 py-3 text-sm text-[#071A33]">{{ $log->user_name ?? 'System' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ str_replace('_', ' ', ucfirst($log->action)) }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $log->result === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $log->result }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection