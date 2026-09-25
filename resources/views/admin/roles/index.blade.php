@extends('layouts.app')
@section('page-title', 'Roles & Permissions')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#071A33]">Roles</h1>
        <a href="{{ route('admin.roles.create') }}" class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">+ Add Role</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($roles as $role)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-[#071A33]">{{ $role->name }}</h3>
                @if($role->is_system)
                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">System</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mb-4">{{ $role->description ?? 'No description' }}</p>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">{{ $role->users_count }} users</span>
                <a href="{{ route('admin.roles.show', $role) }}" class="text-sm text-[#155EEF] font-medium hover:text-[#00B8A9]">View →</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection