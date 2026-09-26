@extends('layouts.app')
@php
    // Blade's @error compiles to $errors->getBag(...). The bag is normally shared
    // by the ShareErrorsFromSession middleware, but any view rendered when the
    // session never started (blank APP_KEY, an unreachable session backend) leaves
    // $errors undefined and turns the whole page into an HTTP 500. Guarantee it
    // exists so a missing bag can never blank the site.
    if (!isset($errors)) {
        $errors = new \Illuminate\Support\ViewErrorBag();
    }
@endphp
@section('page-title', 'Create Role')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-[#071A33] mb-6">Create New Role</h1>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Role Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm @error('name') border-red-500 @enderror">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm @error('slug') border-red-500 @enderror" placeholder="e.g. editor">
                @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#071A33] mb-3">Permissions</label>
                @foreach($permissions as $module => $perms)
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-600 uppercase mb-2">{{ $module }}</h4>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->slug }}" class="w-4 h-4 rounded text-[#155EEF]">
                            <span class="text-sm text-gray-700">{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white px-6 py-3 rounded-xl font-medium hover:opacity-90">Create Role</button>
                <a href="{{ route('admin.roles.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection