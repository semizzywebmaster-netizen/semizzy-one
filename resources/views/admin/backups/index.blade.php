@extends('layouts.app')
@section('page-title', 'Backups')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#071A33]">Backups</h1>
        <form method="POST" action="{{ route('admin.backups.create') }}">
            @csrf
            <button class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">Create Backup</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filename</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($backups as $backup)
                <tr>
                    <td class="px-6 py-4 text-sm text-[#071A33] font-mono">{{ $backup['filename'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $backup['size_human'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ date('M d, Y H:i', $backup['created_at']) }}</td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="{{ route('admin.backups.download', $backup['filename']) }}" class="text-sm text-[#155EEF] hover:text-[#00B8A9]">Download</a>
                        <form method="POST" action="{{ route('admin.backups.destroy', $backup['filename']) }}" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-sm text-red-600 hover:text-red-800" onclick="return confirm('Delete this backup?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No backups found. Create one above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection