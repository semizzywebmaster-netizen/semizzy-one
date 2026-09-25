@extends('layouts.app')
@section('page-title', 'System Health')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-[#071A33]">System Health</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($health as $name => $check)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-3 h-3 rounded-full {{ $check['status'] === 'healthy' ? 'bg-green-500' : ($check['status'] === 'warning' ? 'bg-yellow-500' : 'bg-red-500') }}"></div>
                <h3 class="text-lg font-semibold text-[#071A33]">{{ ucfirst(str_replace('_', ' ', $name)) }}</h3>
            </div>
            <p class="text-sm text-gray-600 mb-2">{{ $check['message'] }}</p>
            @if(isset($check['details']))
            <div class="mt-3 pt-3 border-t border-gray-100 space-y-1">
                @foreach($check['details'] as $key => $value)
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                    <span class="text-gray-700">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                </div>
                @endforeach
            </div>
            @endif
            <div class="mt-3">
                <span class="px-2 py-1 text-xs rounded-full {{ $check['status'] === 'healthy' ? 'bg-green-100 text-green-800' : ($check['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ ucfirst($check['status']) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection