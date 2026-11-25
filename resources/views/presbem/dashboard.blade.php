@extends('layouts.presbem')

@section('title', 'Dashboard Eksekutif PresBEM')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Executive Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1A6B6B] to-[#124b4b] p-6 text-white shadow-md">
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Presidium BEM FILKOM UB
                </span>
                <h2 class="mt-2 text-2xl font-black tracking-tight">Selamat Datang, {{ auth()->user()->name }}</h2>
                <p class="mt-1 text-xs text-teal-100 max-w-xl">
                    Pantau kondisi keuangan kabinet, kepatuhan pembayaran kas seluruh fungsionaris, dan jadwal kegiatan secara real-time.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('presbem.rekap-kas') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-[#1A6B6B] shadow-sm transition hover:bg-teal-50">
                    Lihat Rekap Kas
                </a>
                <a href="{{ route('presbem.transparansi-kas') }}" class="rounded-xl bg-white/10 px-4 py-2.5 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                    Buku Kas
                </a>
            </div>
        </div>
        <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/5 pointer-events-none"></div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Saldo Kas -->
        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Saldo Kas Bersih</span>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-teal-50 text-[#1A6B6B]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black tracking-tight text-[#1A6B6B]">
                Rp{{ number_format($saldo, 0, ',', '.') }}
            </div>
            <div class="mt-1 flex items-center gap-2 text-[11px] text-gray-400">
                <span class="text-emerald-600 font-semibold">+{{ number_format($pemasukan, 0, ',', '.') }}</span>
                <span>/</span>
                <span class="text-rose-600 font-semibold">-{{ number_format($pengeluaran, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Fungsionaris -->
        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Fungsionaris</span>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black tracking-tight text-gray-900">
                {{ $anggotaCount }}
            </div>
            <p class="mt-1 text-[11px] text-gray-400">Terdaftar di seluruh kementerian</p>
        </div>

        <!-- Pembayaran Menunggu -->
        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Antrean Verifikasi</span>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black tracking-tight text-amber-600">
                {{ $pendingPaymentsCount }}
            </div>
            <p class="mt-1 text-[11px] text-gray-400">Pembayaran menunggu validasi ADKEU</p>
        </div>

        <!-- Kegiatan Aktif -->
        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Kegiatan Aktif</span>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black tracking-tight text-gray-900">
                {{ $kegiatanAktif->count() }}
            </div>
            <p class="mt-1 text-[11px] text-gray-400">Program kerja mendatang</p>
        </div>
    </div>

    <!-- Quick Shortcuts Grid -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <a href="{{ route('presbem.rekap-kas') }}" class="group flex flex-col items-center rounded-2xl border border-gray-100/80 bg-white p-4 text-center shadow-sm transition hover:border-[#1A6B6B]/40 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-[#1A6B6B] group-hover:bg-[#1A6B6B] group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <span class="mt-2.5 text-xs font-bold text-gray-800">Rekap Kas</span>
            <span class="text-[10px] text-gray-400">Laporan kabinet</span>
        </a>

        <a href="{{ route('presbem.transparansi-kas') }}" class="group flex flex-col items-center rounded-2xl border border-gray-100/80 bg-white p-4 text-center shadow-sm transition hover:border-[#1A6B6B]/40 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <span class="mt-2.5 text-xs font-bold text-gray-800">Transparansi Kas</span>
            <span class="text-[10px] text-gray-400">Buku kas terbuka</span>
        </a>

        <a href="{{ route('presbem.rekap-presensi') }}" class="group flex flex-col items-center rounded-2xl border border-gray-100/80 bg-white p-4 text-center shadow-sm transition hover:border-[#1A6B6B]/40 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <span class="mt-2.5 text-xs font-bold text-gray-800">Rekap Presensi</span>
            <span class="text-[10px] text-gray-400">Presensi kabinet</span>
        </a>

        <a href="{{ route('presbem.bayar-kas') }}" class="group flex flex-col items-center rounded-2xl border border-gray-100/80 bg-white p-4 text-center shadow-sm transition hover:border-[#1A6B6B]/40 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <span class="mt-2.5 text-xs font-bold text-gray-800">Bayar Kas Saya</span>
            <span class="text-[10px] text-gray-400">Iuran pribadi</span>
        </a>

        <a href="{{ route('presbem.presensi-kegiatan') }}" class="group flex flex-col items-center rounded-2xl border border-gray-100/80 bg-white p-4 text-center shadow-sm transition hover:border-[#1A6B6B]/40 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
            </div>
            <span class="mt-2.5 text-xs font-bold text-gray-800">Presensi Kegiatan</span>
            <span class="text-[10px] text-gray-400">QR & Selfie</span>
        </a>

        <a href="{{ route('presbem.jadwal-piket') }}" class="group flex flex-col items-center rounded-2xl border border-gray-100/80 bg-white p-4 text-center shadow-sm transition hover:border-[#1A6B6B]/40 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <span class="mt-2.5 text-xs font-bold text-gray-800">Jadwal Piket</span>
            <span class="text-[10px] text-gray-400">Shift piket BEM</span>
        </a>
    </div>

    <!-- 2 Column Content: Transaksi Kas & Kegiatan -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Transaksi Kas Terbaru -->
        <section class="rounded-2xl border border-gray-100/80 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Transaksi Kas Terbaru</h3>
                    <p class="text-xs text-gray-500">Mutasi kas organisasi terkini</p>
                </div>
                <a href="{{ route('presbem.transparansi-kas') }}" class="text-xs font-semibold text-[#1A6B6B] hover:underline">
                    Semua Transaksi &rarr;
                </a>
            </div>

            <div class="divide-y divide-gray-100/80">
                @forelse($transaksiTerbaru as $transaksi)
                    <div class="flex items-center justify-between gap-3 py-3.5 hover:bg-gray-50/50 transition rounded-lg px-2">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $transaksi->type === 'masuk' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                @if($transaksi->type === 'masuk')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                    </svg>
                                @else
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-900 line-clamp-1">{{ $transaksi->description }}</p>
                                <p class="text-[11px] text-gray-400">
                                    {{ $transaksi->transaction_date->format('d/m/Y') }}
                                    @if($transaksi->member) · {{ $transaksi->member->name }} @endif
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-black shrink-0 {{ $transaksi->type === 'masuk' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $transaksi->type === 'masuk' ? '+' : '-' }}Rp{{ number_format($transaksi->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-gray-400">Belum ada transaksi kas yang dibukukan.</p>
                @endforelse
            </div>
        </section>

        <!-- Kegiatan Terkini & Mendatang -->
        <section class="rounded-2xl border border-gray-100/80 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Agenda Kegiatan BEM</h3>
                    <p class="text-xs text-gray-500">Program kerja dan kegiatan mendatang</p>
                </div>
                <a href="{{ route('presbem.presensi-kegiatan') }}" class="text-xs font-semibold text-[#1A6B6B] hover:underline">
                    Presensi &rarr;
                </a>
            </div>

            <div class="divide-y divide-gray-100/80">
                @forelse($kegiatanAktif as $kegiatan)
                    <div class="flex items-center justify-between gap-3 py-3.5 hover:bg-gray-50/50 transition rounded-lg px-2">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-900 line-clamp-1">{{ $kegiatan->nama }}</p>
                                <p class="text-[11px] text-gray-400">
                                    {{ $kegiatan->tanggal->format('d/m/Y') }}
                                    @if($kegiatan->jam_mulai) · {{ $kegiatan->jam_mulai }} WIB @endif
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold text-[#1A6B6B] border border-teal-100 shrink-0">
                            {{ $kegiatan->status ?? 'Aktif' }}
                        </span>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-gray-400">Belum ada kegiatan mendatang.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
