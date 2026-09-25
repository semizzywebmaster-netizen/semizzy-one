@extends('layouts.app')
@section('page-title', 'Edit Provider')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-[#071A33] mb-6">Edit Provider: {{ $provider->name }}</h1>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.providers.update', $provider) }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Name</label>
                <input type="text" name="name" value="{{ old('name', $provider->name) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm">{{ old('description', $provider->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Status</label>
                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm">
                    <option value="active" {{ $provider->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $provider->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="error" {{ $provider->status === 'error' ? 'selected' : '' }}>Error</option>
                </select>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Priority</label>
                    <input type="number" name="priority" value="{{ $provider->priority }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Timeout (s)</label>
                    <input type="number" name="timeout" value="{{ $provider->timeout }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#071A33] mb-1.5">Max Retries</label>
                    <input type="number" name="max_retries" value="{{ $provider->max_retries }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm">
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white px-6 py-3 rounded-xl font-medium hover:opacity-90">Update Provider</button>
                <a href="{{ route('admin.providers.show', $provider) }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection