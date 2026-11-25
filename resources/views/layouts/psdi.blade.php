<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - BEM FILKOM UB | PSDI</title>
    <meta name="description" content="Sistem Manajemen Presensi & Kegiatan BEM FILKOM UB - PSDI">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .sidebar-collapsed .sidebar-label,
        .sidebar-collapsed .sidebar-badge,
        .sidebar-collapsed .sidebar-heading,
        .sidebar-collapsed .sidebar-title {
            display: none !important;
        }
        .sidebar-collapsed .nav-item {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sidebar-collapsed #toggleSidebar span {
            display: none !important;
        }
        .sidebar-collapsed #toggleSidebar svg {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-[#F4F0EB] min-h-screen flex text-gray-800">

    {{-- Sidebar --}}
    <aside id="sidebar" class="w-[230px] bg-[#2F3336] flex flex-col min-h-screen fixed left-0 top-0 z-30 transition-all duration-300">
        {{-- Logo --}}
        <div class="px-4 py-4 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#1A8A8A] rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="sidebar-title overflow-hidden">
                    <div class="text-white font-bold text-sm leading-tight whitespace-nowrap">BEM FILKOM UB</div>
                    <div class="text-[#5AADAD] text-xs whitespace-nowrap">Kas & Presensi</div>
                </div>
            </div>
        </div>

        {{-- Role Badge --}}
        <div class="px-4 pt-4 pb-2 sidebar-badge">
            <span class="text-xs text-[#A0AEC0] border border-[#A0AEC0]/30 rounded px-2.5 py-1 inline-block">PSDI</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">
            <a href="{{ route('psdi.dashboard') }}" title="Dashboard"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('psdi.dashboard') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="sidebar-label">Dashboard</span>
            </a>

            {{-- PRESENSI Section --}}
            <div class="pt-3 pb-1 px-3 sidebar-heading">
                <span class="text-xs font-semibold text-[#6B7280] tracking-wider uppercase">Presensi</span>
            </div>
            <a href="{{ route('psdi.kegiatan') }}" title="Kegiatan"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('psdi.kegiatan*') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="sidebar-label">Kegiatan</span>
            </a>
            <a href="{{ route('psdi.perizinan') }}" title="Validasi Izin"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('psdi.perizinan*') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="sidebar-label">Validasi Izin</span>
            </a>
            <a href="{{ route('psdi.rekap-presensi') }}" title="Rekap Presensi"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('psdi.rekap-presensi*') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="sidebar-label">Rekap Presensi</span>
            </a>
            <a href="{{ route('psdi.profile') }}" title="Profile"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('psdi.profile') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="sidebar-label">Profile</span>
            </a>
        </nav>

        {{-- Sembunyikan --}}
        <div class="px-4 py-4 border-t border-white/10">
            <button id="toggleSidebar" class="flex items-center gap-2 text-xs text-[#A0AEC0] hover:text-white transition-colors w-full">
                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <span>Sembunyikan</span>
            </button>
        </div>
    </aside>

    {{-- Main Content Area --}}
    <div class="ml-[230px] flex-1 flex flex-col min-h-screen transition-all duration-300" id="mainContent">
        {{-- Top Header --}}
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-20">
            <h1 class="text-base font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <button class="relative p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-[#1A8A8A] rounded-full flex items-center justify-center text-white font-bold text-xs">{{ auth()->user()?->initials ?? 'P' }}</div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-800">{{ auth()->user()?->name ?? 'PSDI' }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()?->departemen ?: (auth()->user()?->role_badge ?? 'PSDI') }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar" aria-label="Keluar dari akun" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors text-gray-500 hover:text-gray-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    @stack('modals')
    @stack('scripts')

    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('w-[230px]');
                sidebar.classList.toggle('w-16');
                sidebar.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('ml-[230px]');
                mainContent.classList.toggle('ml-16');
            });
        }
    </script>
</body>
</html>
