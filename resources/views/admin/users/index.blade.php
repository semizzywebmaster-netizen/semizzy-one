@extends('layouts.app')
@section('page-title', 'User Management')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#071A33]">Users</h1>
        <a href="{{ route('admin.users.create') }}" class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">
            + Add User
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..."
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#155EEF]">
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 rounded-lg text-sm hover:bg-gray-200">Filter</button>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-[#155EEF] to-[#00B8A9] rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <span class="text-sm font-medium text-[#071A33]">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @foreach($user->roles as $role)
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4">
                        @php $colors = ['active' => 'green', 'inactive' => 'gray', 'suspended' => 'red']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $colors[$user->status] ?? 'gray' }}-100 text-{{ $colors[$user->status] ?? 'gray' }}-800">{{ ucfirst($user->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-[#155EEF] hover:text-[#00B8A9] font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection