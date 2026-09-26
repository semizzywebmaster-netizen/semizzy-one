<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SEMIZZY ONE') }}</title>

    {{-- PWA Meta --}}
    <meta name="theme-color" content="#155EEF">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/favicon.ico" sizes="any">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @else
        <style>
            .vite-missing{background:#FEF3C7;border-bottom:1px solid #F59E0B;color:#92400E;
                font:13px/1.5 ui-monospace,Menlo,Consolas,monospace;padding:10px 14px}
        </style>
    @endif
</head>
<body class="min-h-screen bg-[#F8FAFC] font-sans antialiased text-[#071A33]">
    <div class="min-h-screen flex flex-col items-center justify-center px-6 py-12">

        <div class="w-full max-w-lg text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-[#155EEF] to-[#00B8A9] mb-6 shadow-lg shadow-[#155EEF]/20">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>

            <h1 class="text-3xl font-bold tracking-tight">{{ config('app.name', 'SEMIZZY ONE') }}</h1>
            <p class="mt-3 text-gray-500">Everything You Need. One Platform.</p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white font-medium hover:opacity-90 transition-opacity">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white font-medium hover:opacity-90 transition-opacity">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="w-full sm:w-auto px-6 py-3 rounded-xl border border-gray-300 font-medium hover:bg-gray-50 transition-colors">
                            Register
                        </a>
                    @endif
                @endauth
            </div>

            <p class="mt-12 text-xs text-gray-400">SEMIZZY ONE CORE v2.0.0</p>
        </div>
    </div>
</body>
</html>
