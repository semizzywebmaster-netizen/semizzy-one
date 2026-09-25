@extends('layouts.app')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-[#071A33] mb-6">Edit User: {{ $user->name }}</h1>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155EEF] text-sm @error('name') border-red-500 @enderror">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155EEF] text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Phone</label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155EEF] text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Status</label>
                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm">
                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-2">Roles</label>
                @foreach($roles as $role)
                <label class="flex items-center gap-2 mb-2">
                    <input type="checkbox" name="roles[]" value="{{ $role->slug }}" {{ in_array($role->slug, $userRoles) ? 'checked' : '' }} class="w-4 h-4 rounded text-[#155EEF]">
                    <span class="text-sm text-gray-700">{{ $role->name }}</span>
                </label>
                @endforeach
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white px-6 py-3 rounded-xl font-medium hover:opacity-90">Update User</button>
                <a href="{{ route('admin.users.show', $user) }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection