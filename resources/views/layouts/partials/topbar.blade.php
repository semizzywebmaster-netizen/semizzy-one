{{-- SEMIZZY ONE Top Navigation Bar --}}
<header class="fixed top-0 right-0 left-0 lg:left-64 z-30 bg-white border-b border-gray-200 h-16">
    <div class="flex items-center justify-between h-full px-4 sm:px-6">
        {{-- Mobile menu button --}}
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Breadcrumb / Page Title --}}
        <div class="hidden lg:block">
            <h2 class="text-lg font-semibold text-[#071A33]">@yield('page-title', 'Dashboard')</h2>
        </div>

        {{-- Right side --}}
        <div class="flex items-center gap-4">
            {{-- Network Status Indicator --}}
            <div id="network-indicator" class="flex items-center gap-1.5">
                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                <span class="text-xs text-gray-500">Online</span>
            </div>

            {{-- Notifications --}}
            <button class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            {{-- Profile Menu --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 bg-gradient-to-br from-[#155EEF] to-[#00B8A9] rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-semibold">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-[#071A33]">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Security</a>
                    <div class="border-t border-gray-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

@push('head')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush