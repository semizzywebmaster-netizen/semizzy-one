@extends('layouts.app')
@section('page-title', 'Addon Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#071A33]">{{ $addon->name }}</h1>
        <a href="{{ route('admin.addons.index') }}" class="text-sm text-[#155EEF] hover:underline">&larr; Back to Addons</a>
    </div>

    {{-- Status & Actions --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-[#071A33]">Status</h3>
                @php $colors = ['active' => 'green', 'installed' => 'blue', 'inactive' => 'gray', 'error' => 'red', 'uninstalled' => 'gray']; @endphp
                <span class="px-2 py-1 text-xs rounded-full bg-{{ $colors[$addon->status] ?? 'gray' }}-100 text-{{ $colors[$addon->status] ?? 'gray' }}-800">{{ ucfirst($addon->status) }}</span>
                <span class="text-sm text-gray-500">v{{ $addon->version }}</span>
            </div>
            <div class="flex items-center gap-2">
                @if($addon->status === 'installed' || $addon->status === 'inactive')
                <form method="POST" action="{{ route('admin.addons.activate', $addon) }}" class="inline">@csrf
                    <button class="px-4 py-2 bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white rounded-lg text-sm hover:opacity-90">Activate</button>
                </form>
                @endif
                @if($addon->status === 'active')
                <form method="POST" action="{{ route('admin.addons.deactivate', $addon) }}" class="inline">@csrf
                    <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Deactivate</button>
                </form>
                @endif
                @if($addon->status !== 'active' && $addon->status !== 'uninstalled')
                <form method="POST" action="{{ route('admin.addons.uninstall', $addon) }}" class="inline"
                      onsubmit="return confirm('Uninstall this addon?');">@csrf @method('DELETE')
                    <button class="px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm hover:bg-red-50">Uninstall</button>
                </form>
                @endif
            </div>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600">{{ $addon->description ?? 'No description provided.' }}</p>
        </div>
    </div>

    {{-- Details --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Details</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Slug</p>
                <p class="text-sm font-medium text-[#071A33]">{{ $addon->slug }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Version</p>
                <p class="text-sm font-medium text-[#071A33]">{{ $addon->version }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Author</p>
                <p class="text-sm font-medium text-[#071A33]">{{ $addon->author ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Author Email</p>
                <p class="text-sm font-medium text-[#071A33]">{{ $addon->author_email ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Homepage</p>
                <p class="text-sm font-medium text-[#071A33]">
                    @if($addon->homepage)
                        <a href="{{ $addon->homepage }}" target="_blank" rel="noopener noreferrer" class="text-[#155EEF] hover:underline">{{ $addon->homepage }}</a>
                    @else — @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">License</p>
                <p class="text-sm font-medium text-[#071A33]">{{ $addon->license ?? 'proprietary' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Installed</p>
                <p class="text-sm font-medium text-[#071A33]">{{ $addon->installed_at?->diffForHumans() ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Activated</p>
                <p class="text-sm font-medium text-[#071A33]">{{ $addon->activated_at?->diffForHumans() ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Permissions --}}
    @if(!empty($addon->permissions))
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Permissions</h3>
        </div>
        <div class="p-6 flex flex-wrap gap-2">
            @foreach($addon->permissions as $permission)
                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ is_array($permission) ? ($permission['name'] ?? json_encode($permission)) : $permission }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Requirements --}}
    @if(!empty($addon->requires))
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#071A33]">Requirements</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($addon->requires as $key => $value)
                    <div>
                        <dt class="text-xs text-gray-500">{{ ucfirst($key) }}</dt>
                        <dd class="text-sm font-medium text-[#071A33]">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
    @endif

    {{-- Last Error --}}
    @if($addon->last_error)
    <div class="bg-red-50 rounded-xl border border-red-200">
        <div class="px-6 py-4 border-b border-red-200">
            <h3 class="text-lg font-semibold text-red-800">Last Error</h3>
        </div>
        <div class="p-6">
            <p class="text-sm text-red-700 whitespace-pre-wrap">{{ $addon->last_error }}</p>
            <p class="text-xs text-red-500 mt-2">Last health check: {{ $addon->last_health_check_at?->diffForHumans() ?? 'never' }}</p>
        </div>
    </div>
    @endif
</div>
@endsection
