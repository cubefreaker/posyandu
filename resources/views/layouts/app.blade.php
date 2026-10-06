<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 font-body text-slate-700 antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-60 flex flex-col bg-white border-r border-slate-200 shadow-xs transition-transform duration-250 ease-in-out lg:translate-x-0 -translate-x-full">
            {{-- Logo & Mobile Close --}}
            <div class="flex items-center justify-between h-14 px-5 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🏥</span>
                    <span class="font-heading font-bold text-primary-700 text-lg">{{ config('app.name') }}</span>
                </div>
                <button type="button" onclick="toggleSidebar()" class="lg:hidden p-1.5 -mr-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" title="Tutup menu">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            {{-- Menu --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @if(auth()->user()->role === 'kader')
                    <div class="px-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Pelayanan Posyandu</div>
                    <x-nav-link href="{{ route('pelayanan.index') }}" icon="zap" :active="request()->routeIs('pelayanan.*')">Pelayanan Terpadu</x-nav-link>
                    <x-nav-link href="{{ route('kesehatan-ibu.index') }}" icon="heart-pulse" :active="request()->routeIs('kesehatan-ibu.*')">Kesehatan Ibu Hamil</x-nav-link>
                    
                    <div class="px-3 pt-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Data & Laporan</div>
                    <x-nav-link href="{{ route('data-ibu.index') }}" icon="users" :active="request()->routeIs('data-ibu.*') || request()->routeIs('data-anak.*')">Data Warga</x-nav-link>
                    <x-nav-link href="{{ route('laporan.index') }}" icon="file-text" :active="request()->routeIs('laporan.*')">Laporan Posyandu</x-nav-link>
                @else
                    <div class="px-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Manajerial</div>
                    <x-nav-link href="{{ route('dashboard') }}" icon="layout-dashboard" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                    <x-nav-link href="{{ route('pelayanan.index') }}" icon="zap" :active="request()->routeIs('pelayanan.*')">Pelayanan Terpadu</x-nav-link>
                    
                    <div class="px-3 pt-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Master Data</div>
                    <x-nav-link href="{{ route('data-ibu.index') }}" icon="users" :active="request()->routeIs('data-ibu.*')">Data Ibu</x-nav-link>
                    <x-nav-link href="{{ route('data-anak.index') }}" icon="baby" :active="request()->routeIs('data-anak.*')">Data Anak</x-nav-link>
                    <x-nav-link href="{{ route('kesehatan-ibu.index') }}" icon="heart-pulse" :active="request()->routeIs('kesehatan-ibu.*')">Kesehatan Ibu Hamil</x-nav-link>
                    <x-nav-link href="{{ route('penimbangan.index') }}" icon="scale" :active="request()->routeIs('penimbangan.*')">Penimbangan</x-nav-link>
                    <x-nav-link href="{{ route('imunisasi.index') }}" icon="syringe" :active="request()->routeIs('imunisasi.*')">Imunisasi</x-nav-link>
                    <x-nav-link href="{{ route('vitamin.index') }}" icon="pill" :active="request()->routeIs('vitamin.*')">Vitamin</x-nav-link>
                    
                    <div class="px-3 pt-3 pb-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Laporan & Pengaturan</div>
                    <x-nav-link href="{{ route('laporan.index') }}" icon="file-text" :active="request()->routeIs('laporan.*')">Laporan</x-nav-link>
                    <x-nav-link href="{{ route('user-management.index') }}" icon="users-cog" :active="request()->routeIs('user-management.*')">User Management</x-nav-link>
                @endif
            </nav>

            {{-- User Info --}}
            <div class="border-t border-slate-100 px-4 py-3 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-sm font-semibold">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ auth()->user()->nama }}</p>
                        <p class="text-xs text-slate-400 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 w-full px-2 py-1.5 text-sm text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Overlay mobile --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

        {{-- Main Content --}}
        <div class="flex-1 min-w-0 lg:ml-60">
            {{-- Topbar --}}
            <header class="sticky top-0 z-10 flex items-center h-14 px-4 sm:px-6 bg-white border-b border-slate-200 shadow-xs">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 mr-2 text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1 class="font-heading font-bold text-lg text-slate-900 truncate">{{ $title ?? 'Dashboard' }}</h1>
            </header>

            {{-- Toast Notification --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="fixed top-4 right-4 left-4 sm:left-auto sm:right-4 z-50 max-w-sm ml-auto flex items-center gap-3 bg-white border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 text-green-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition
                     class="fixed top-4 right-4 left-4 sm:left-auto sm:right-4 z-50 max-w-sm ml-auto flex items-center gap-3 bg-white border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Page Content --}}
            <main class="p-4 sm:p-6 min-w-0">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>
