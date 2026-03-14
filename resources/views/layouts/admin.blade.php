<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — HeadlessCMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { @apply flex items-center gap-2.5 px-3 py-1.5 text-sm rounded-md transition-colors; }
        .sidebar-link:hover { @apply bg-white/10 text-white; }
        .sidebar-link.active { @apply bg-white/20 text-white font-medium; }
        .sidebar-link.inactive { @apply text-blue-200; }
    </style>
    @stack('head')
</head>
<body class="h-full flex flex-col bg-gray-50" x-data="{ sidebarOpen: true }">

{{-- ═══ Header ═══ --}}
<header class="h-14 bg-blue-700 flex items-center px-4 gap-4 flex-shrink-0 z-20">
    {{-- Logo --}}
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white font-semibold text-sm">
        <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        HeadlessCMS
    </a>

    <div class="flex-1"></div>

    {{-- User Menu --}}
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="flex items-center gap-2 text-blue-100 hover:text-white text-sm py-1 px-2 rounded-lg hover:bg-white/10 transition-colors">
            <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold text-white">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <span class="hidden sm:block">{{ auth()->user()->name }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" @click.outside="open = false" x-cloak
            class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
            <div class="px-3 py-2 border-b border-gray-100">
                <p class="text-xs font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    Sign out
                </button>
            </form>
        </div>
    </div>
</header>

{{-- ═══ Body ═══ --}}
<div class="flex flex-1 min-h-0">

    {{-- ═══ Sidebar ═══ --}}
    <aside class="w-64 bg-blue-800 flex flex-col flex-shrink-0 overflow-y-auto">
        <nav class="flex-1 px-3 py-4 space-y-5">

            {{-- Content Manager --}}
            <div>
                <p class="text-xs font-semibold text-blue-300 uppercase tracking-wider px-3 mb-1.5">Content Manager</p>
                @php $contentTypes = \App\Models\ContentType::getForSidebar(); @endphp
                @forelse($contentTypes as $ct)
                    <a href="{{ route('admin.cm.index', $ct->plural_name) }}"
                        class="sidebar-link {{ request()->route('slug') === $ct->plural_name ? 'active' : 'inactive' }}">
                        <span class="text-base leading-none">{{ $ct->icon ?: '📄' }}</span>
                        {{ $ct->display_name }}
                    </a>
                @empty
                    <p class="text-xs text-blue-300 italic px-3 py-1">No content types yet</p>
                @endforelse
            </div>

            {{-- Plugins --}}
            <div>
                <p class="text-xs font-semibold text-blue-300 uppercase tracking-wider px-3 mb-1.5">Plugins</p>
                <a href="{{ route('admin.media.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.media.*') ? 'active' : 'inactive' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Media Library
                </a>
            </div>

            {{-- General --}}
            <div class="border-t border-blue-700 pt-4">
                <a href="{{ route('admin.ctb.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.ctb.*') ? 'active' : 'inactive' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    Content-Type Builder
                </a>
                <a href="{{ route('admin.settings.global') }}"
                    class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : 'inactive' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
            </div>
        </nav>
    </aside>

    {{-- ═══ Main Content ═══ --}}
    <main class="flex-1 min-w-0 overflow-auto">
        @yield('content')
    </main>

</div>

{{-- Flash Messages --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed bottom-5 right-5 bg-green-600 text-white text-sm px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed bottom-5 right-5 bg-red-600 text-white text-sm px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
    </svg>
    {{ session('error') }}
</div>
@endif

@stack('scripts')
</body>
</html>
