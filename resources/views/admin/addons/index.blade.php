@extends('layouts.app')
@section('page-title', 'Addons')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-[#071A33]">Addons</h1>

    {{-- Installed Addons --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Installed Addons</h3>
        </div>
        <div class="p-6">
            @forelse($installed as $addon)
            <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div>
                    <p class="text-sm font-medium text-[#071A33]">{{ $addon->name }} <span class="text-gray-400">v{{ $addon->version }}</span></p>
                    <p class="text-xs text-gray-500">{{ $addon->description ?? 'No description' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @php $colors = ['active' => 'green', 'installed' => 'blue', 'inactive' => 'gray', 'error' => 'red']; @endphp
                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $colors[$addon->status] ?? 'gray' }}-100 text-{{ $colors[$addon->status] ?? 'gray' }}-800">{{ ucfirst($addon->status) }}</span>
                    @if($addon->status === 'installed' || $addon->status === 'inactive')
                    <form method="POST" action="{{ route('admin.addons.activate', $addon) }}" class="inline">@csrf
                        <button class="text-xs text-green-600 hover:underline">Activate</button>
                    </form>
                    @endif
                    @if($addon->status === 'active')
                    <form method="POST" action="{{ route('admin.addons.deactivate', $addon) }}" class="inline">@csrf
                        <button class="text-xs text-yellow-600 hover:underline">Deactivate</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">No addons installed.</p>
            @endforelse
        </div>
    </div>

    {{-- Available Addons --}}
    @if(count($available) > 0)
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Available Addons</h3>
        </div>
        <div class="p-6 space-y-4">
            @foreach($available as $addon)
            @if(!$addon['is_installed'])
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-[#071A33]">{{ $addon['name'] }} <span class="text-gray-400">v{{ $addon['version'] }}</span></p>
                    <p class="text-xs text-gray-500">{{ $addon['description'] ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.addons.install') }}">
                    @csrf
                    <input type="hidden" name="slug" value="{{ $addon['slug'] }}">
                    <button class="px-4 py-2 bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white rounded-lg text-sm hover:opacity-90">Install</button>
                </form>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection