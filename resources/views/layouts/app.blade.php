<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SEMIZZY ONE') }}</title>

    {{-- PWA Meta --}}
    <meta name="theme-color" content="#155EEF">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.webmanifest">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    {{-- Degrade gracefully: @vite throws ViteManifestNotFoundException when the
         frontend has not been built, which turned every page into a blank 500.
         Render the page without assets instead, and show a visible banner so the
         operator knows exactly what to run. --}}
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @else
        <style>
            .vite-missing{background:#FEF3C7;border-bottom:1px solid #F59E0B;color:#92400E;
                font:13px/1.5 ui-monospace,Menlo,Consolas,monospace;padding:10px 14px}
        </style>
    @endif

    @stack('head')
</head>
<body class="h-full bg-[#F8FAFC] font-sans antialiased">
    @php
        // Blade's @error directive compiles to $errors->getBag(...). The bag is
        // normally shared by the ShareErrorsFromSession middleware, but any view
        // rendered outside that middleware - or after a session failure - makes
        // $errors undefined and turns the whole page into an HTTP 500. Guarantee
        // it exists so a missing bag can never blank the site.
        if (!isset($errors)) {
            $errors = new \Illuminate\Support\ViewErrorBag();
        }
    @endphp
    <div id="app" class="min-h-full">
        @unless (file_exists(public_path('build/manifest.json')))
            <div class="vite-missing">
                Frontend assets are not built. Run:
                <code>npm ci &amp;&amp; npm run build</code>
            </div>
        @endunless

        {{-- Network Status Bar (React component) --}}
        <div id="network-status"></div>

        @auth
            @include('layouts.partials.sidebar')
            @include('layouts.partials.topbar')

            {{-- Main Content --}}
            <main class="lg:pl-64 pt-16">
                <div class="px-4 sm:px-6 lg:px-8 py-8">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 text-sm" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        @else
            @yield('content')
        @endauth
    </div>

    {{-- App State for React --}}
    @php
        $currentUser = auth()->user();
        $userData = $currentUser ? $currentUser->only(['id', 'name', 'email', 'status']) : null;
        $userPermissions = $currentUser ? $currentUser->getAllPermissions() : [];
        $userRoles = $currentUser ? $currentUser->roles->pluck('slug')->toArray() : [];
    @endphp
    <script>
        window.__SEMIZZY = {
            csrfToken: '{{ csrf_token() }}',
            user: @json($userData),
            permissions: @json($userPermissions),
            roles: @json($userRoles),
        };
    </script>

    @stack('scripts')
</body>
</html>