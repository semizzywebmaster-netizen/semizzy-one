{{-- SEMIZZY ONE Sidebar --}}
<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-[#071A33] transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    {{-- Logo --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
        <div class="w-10 h-10 bg-gradient-to-br from-[#155EEF] to-[#00B8A9] rounded-xl flex items-center justify-center">
            <span class="text-white font-bold text-lg">S1</span>
        </div>
        <div>
            <h1 class="text-white font-bold text-lg leading-tight">SEMIZZY ONE</h1>
            <p class="text-gray-400 text-xs">Everything You Need</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="mt-4 px-3 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 140px);">
        {{-- Main --}}
        <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Main</div>

        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- Admin Section --}}
        @if(auth()->user()->hasRole('admin'))
        <div class="px-3 pt-6 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration</div>

        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Admin Dashboard</span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Users</span>
        </a>

        <a href="{{ route('admin.roles.index') }}" class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Roles & Permissions</span>
        </a>

        <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Settings</span>
        </a>

        <a href="{{ route('admin.addons.index') }}" class="sidebar-link {{ request()->routeIs('admin.addons.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>Addons</span>
        </a>

        <a href="{{ route('admin.providers.index') }}" class="sidebar-link {{ request()->routeIs('admin.providers.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
            </svg>
            <span>Providers</span>
        </a>

        <a href="{{ route('admin.audit.index') }}" class="sidebar-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>Audit Logs</span>
        </a>

        <a href="{{ route('admin.security.index') }}" class="sidebar-link {{ request()->routeIs('admin.security.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span>Security</span>
        </a>

        <a href="{{ route('admin.health.index') }}" class="sidebar-link {{ request()->routeIs('admin.health.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span>System Health</span>
        </a>

        <a href="{{ route('admin.backups.index') }}" class="sidebar-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <span>Backups</span>
        </a>
        @endif
    </nav>

    {{-- Footer --}}
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
        <div class="text-gray-500 text-xs text-center">
            SEMIZZY ONE v2.0.0
        </div>
    </div>
</aside>

{{-- Mobile sidebar overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

<style>
.sidebar-link {
    @apply flex items-center gap-3 px-3 py-2.5 text-gray-300 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-white/10 hover:text-white;
}
.sidebar-link.active {
    @apply bg-gradient-to-r from-[#155EEF]/20 to-[#00B8A9]/20 text-white border-l-2 border-[#00B8A9];
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
</script>