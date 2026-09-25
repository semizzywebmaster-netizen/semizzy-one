@extends('layouts.app')
@section('page-title', 'Example Addon')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-[#155EEF] to-[#00B8A9] rounded-2xl p-6 text-white">
        <h1 class="text-2xl font-bold">Example Addon</h1>
        <p class="mt-2 text-white/80">This is a sample addon demonstrating the SEMIZZY ONE addon engine.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-[#071A33] mb-4">Addon Engine Verification</h3>
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Addon discovered from addons/ directory</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Manifest validated (addon.json)</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Installation completed</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Activation successful</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Permissions registered (example.view, example.manage)</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Settings registered</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Route accessible</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Migration available</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm">✓</span>
                <span class="text-sm text-gray-700">Audit event logged</span>
            </div>
        </div>
    </div>
</div>
@endsection