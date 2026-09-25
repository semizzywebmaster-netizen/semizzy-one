@extends('layouts.app')
@section('page-title', 'Provider Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#071A33]">Provider: {{ $provider->name }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.providers.edit', $provider) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Edit</a>
            <a href="{{ route('admin.providers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">← Back</a>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
            <div><p class="text-sm text-gray-500">Slug</p><p class="text-sm font-medium">{{ $provider->slug }}</p></div>
            <div><p class="text-sm text-gray-500">Type</p><p class="text-sm font-medium">{{ $provider->type }}</p></div>
            <div><p class="text-sm text-gray-500">Status</p><p class="text-sm font-medium">{{ ucfirst($provider->status) }}</p></div>
            <div><p class="text-sm text-gray-500">Health</p><p class="text-sm font-medium">{{ ucfirst($provider->health_status) }}</p></div>
            <div><p class="text-sm text-gray-500">Priority</p><p class="text-sm font-medium">{{ $provider->priority }}</p></div>
            <div><p class="text-sm text-gray-500">Timeout</p><p class="text-sm font-medium">{{ $provider->timeout }}s</p></div>
            <div><p class="text-sm text-gray-500">Max Retries</p><p class="text-sm font-medium">{{ $provider->max_retries }}</p></div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-[#071A33] mb-4">Health Logs</h3>
            <div class="space-y-2">
                @forelse($provider->healthLogs()->latest()->limit(10)->get() as $log)
                <div class="flex items-center justify-between text-sm">
                    <span class="{{ $log->status === 'healthy' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($log->status) }}</span>
                    <span class="text-gray-500">{{ $log->checked_at?->diffForHumans() ?? 'N/A' }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No health logs.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection