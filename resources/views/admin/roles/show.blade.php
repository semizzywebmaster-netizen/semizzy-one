@extends('layouts.app')
@section('page-title', 'Role Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#071A33]">Role: {{ $role->name }}</h1>
        <div class="flex gap-3">
            @if(!$role->is_system)
            <a href="{{ route('admin.roles.edit', $role) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Edit</a>
            @endif
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">← Back</a>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-[#071A33] mb-4">Details</h3>
            <div class="space-y-3">
                <div><p class="text-sm text-gray-500">Slug</p><p class="text-sm font-medium">{{ $role->slug }}</p></div>
                <div><p class="text-sm text-gray-500">Description</p><p class="text-sm font-medium">{{ $role->description ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-500">System Role</p><p class="text-sm font-medium">{{ $role->is_system ? 'Yes' : 'No' }}</p></div>
                <div><p class="text-sm text-gray-500">Users</p><p class="text-sm font-medium">{{ $role->users->count() }}</p></div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-[#071A33] mb-4">Permissions ({{ $role->permissions->count() }})</h3>
            <div class="flex flex-wrap gap-2">
                @forelse($role->permissions as $perm)
                <span class="px-3 py-1 text-xs bg-blue-50 text-blue-800 rounded-full">{{ $perm->name }}</span>
                @empty
                <p class="text-sm text-gray-500">No permissions assigned</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection