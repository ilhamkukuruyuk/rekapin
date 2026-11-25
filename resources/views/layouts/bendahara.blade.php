<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - BEM FILKOM UB | ADKEU</title>
    <meta name="description" content="Sistem Manajemen Kas & Presensi BEM FILKOM UB - ADKEU">
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
            <span class="text-xs text-[#A0AEC0] border border-[#A0AEC0]/30 rounded px-2.5 py-1 inline-block">ADKEU / Bendahara</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">
            <a href="{{ route('bendahara.dashboard') }}" title="Dashboard"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.dashboard') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="sidebar-label">Dashboard</span>
            </a>

            {{-- KAS --}}
            <div class="pt-3 pb-1 px-3 sidebar-heading">
                <span class="text-xs font-semibold text-[#6B7280] tracking-wider uppercase">Kas</span>
            </div>
            <a href="{{ route('bendahara.kas') }}" title="Kas"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.kas') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="sidebar-label">Kas</span>
            </a>
            <a href="{{ route('bendahara.validasi-pembayaran') }}" title="Validasi Pembayaran"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.validasi-pembayaran') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="sidebar-label">Validasi Pembayaran</span>
            </a>
            <a href="{{ route('bendahara.rekap-kas') }}" title="Rekap Kas"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.rekap-kas') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="sidebar-label">Rekap Kas</span>
            </a>
            <a href="{{ route('bendahara.denda') }}" title="Denda"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.denda') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L3.07 16.5c-.77.833.193 2.5 1.732 2.5z" />
                </svg>
                <span class="sidebar-label">Denda</span>
            </a>

            {{-- PRESENSI --}}
            <div class="pt-3 pb-1 px-3 sidebar-heading">
                <span class="text-xs font-semibold text-[#6B7280] tracking-wider uppercase">Presensi</span>
            </div>
            <a href="{{ route('bendahara.jadwal-piket') }}" title="Jadwal Piket"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.jadwal-piket') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="sidebar-label">Jadwal Piket</span>
            </a>
            <a href="{{ route('bendahara.presensi-piket') }}" title="Presensi Piket"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.presensi-piket') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span class="sidebar-label">Presensi Piket</span>
            </a>
            <a href="{{ route('bendahara.rekap-presensi') }}" title="Rekap Presensi"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.rekap-presensi') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="sidebar-label">Rekap Presensi</span>
            </a>
            <a href="{{ route('bendahara.profile') }}" title="Profile"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ request()->routeIs('bendahara.profile') ? 'bg-[#1A8A8A] text-white' : 'text-[#A0AEC0] hover:bg-white/5 hover:text-white' }}">
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

    {{-- Main Content --}}
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
                    <div class="w-8 h-8 bg-[#1A8A8A] rounded-full flex items-center justify-center text-white font-bold text-xs">{{ auth()->user()?->initials ?? 'B' }}</div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-800">{{ auth()->user()?->name ?? 'ADKEU' }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()?->departemen ?: (auth()->user()?->role_badge ?? 'ADKEU') }}</div>
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
