@extends('layouts.app')
@section('page-title', 'Audit Log Details')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#071A33]">Audit Log #{{ $audit->id }}</h1>
        <a href="{{ route('admin.audit.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">← Back</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div><p class="text-sm text-gray-500">Action</p><p class="text-sm font-medium">{{ str_replace('_', ' ', ucfirst($audit->action)) }}</p></div>
            <div><p class="text-sm text-gray-500">Result</p><p class="text-sm font-medium">{{ ucfirst($audit->result) }}</p></div>
            <div><p class="text-sm text-gray-500">User</p><p class="text-sm font-medium">{{ $audit->user_name ?? 'System' }}</p></div>
            <div><p class="text-sm text-gray-500">IP Address</p><p class="text-sm font-medium font-mono">{{ $audit->ip_address ?? 'N/A' }}</p></div>
            <div><p class="text-sm text-gray-500">Target Type</p><p class="text-sm font-medium">{{ $audit->target_type ?? 'N/A' }}</p></div>
            <div><p class="text-sm text-gray-500">Target ID</p><p class="text-sm font-medium">{{ $audit->target_id ?? 'N/A' }}</p></div>
            <div><p class="text-sm text-gray-500">Timestamp</p><p class="text-sm font-medium">{{ $audit->created_at->format('Y-m-d H:i:s') }}</p></div>
        </div>
        @if($audit->user_agent)
        <div>
            <p class="text-sm text-gray-500">User Agent</p>
            <p class="text-sm text-gray-600 break-all">{{ $audit->user_agent }}</p>
        </div>
        @endif
        @if($audit->metadata)
        <div>
            <p class="text-sm text-gray-500 mb-2">Metadata</p>
            <pre class="bg-gray-50 p-4 rounded-lg text-xs overflow-auto">{{ json_encode($audit->metadata, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>
</div>
@endsection