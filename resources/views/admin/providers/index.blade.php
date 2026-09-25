@extends('layouts.app')
@section('page-title', 'Providers')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#071A33]">Providers</h1>
        <a href="{{ route('admin.providers.create') }}" class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">+ Add Provider</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Health</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($providers as $provider)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-[#071A33]">{{ $provider->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $provider->type }}</td>
                    <td class="px-6 py-4">
                        @php $colors = ['active' => 'green', 'inactive' => 'gray', 'error' => 'red']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $colors[$provider->status] ?? 'gray' }}-100 text-{{ $colors[$provider->status] ?? 'gray' }}-800">{{ ucfirst($provider->status) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="w-2 h-2 inline-block rounded-full {{ $provider->health_status === 'healthy' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        <span class="text-xs text-gray-500 ml-1">{{ ucfirst($provider->health_status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $provider->priority }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.providers.show', $provider) }}" class="text-sm text-[#155EEF] hover:text-[#00B8A9]">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No providers configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $providers->links() }}
</div>
@endsection