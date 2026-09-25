@extends('layouts.app')
@section('page-title', 'Audit Logs')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-[#071A33]">Audit Logs</h1>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Action..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
        <select name="result" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">All Results</option>
            <option value="success" {{ request('result') === 'success' ? 'selected' : '' }}>Success</option>
            <option value="failure" {{ request('result') === 'failure' ? 'selected' : '' }}>Failure</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
        <button type="submit" class="px-4 py-2 bg-gray-100 rounded-lg text-sm hover:bg-gray-200">Filter</button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $log->created_at->format('M d, H:i') }}</td>
                    <td class="px-6 py-3 text-sm text-[#071A33]">{{ $log->user_name ?? 'System' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600">{{ str_replace('_', ' ', ucfirst($log->action)) }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500 font-mono">{{ $log->ip_address ?? '-' }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $log->result === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $log->result }}</span>
                    </td>
                    <td class="px-6 py-3 text-right">
                        <a href="{{ route('admin.audit.show', $log) }}" class="text-sm text-[#155EEF] hover:text-[#00B8A9]">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No audit logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection