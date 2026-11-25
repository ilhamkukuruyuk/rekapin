@extends(auth()->user()->role === 'presbem' ? 'layouts.presbem' : 'layouts.anggota')

@section('title', 'Bayar Kas')
@section('page-title', 'Pembayaran Kas Bulanan')

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Pembayaran Kas & Denda</h2>
        <p class="mt-1 text-sm text-gray-500">Pilih bulan tagihan yang ingin dibayar, ketik nominal transfer secara manual, lalu unggah bukti transfer.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route(auth()->user()->role === 'presbem' ? 'presbem.riwayat-pembayaran' : 'anggota.riwayat-pembayaran') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Riwayat Pembayaran
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-bold mb-1">Terjadi kesalahan pada pengajuan pembayaran:</p>
        <ul class="list-inside list-disc space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Ringkasan Kewajiban Pokok & Denda --}}
<div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tarif Kas Pokok</p>
        <p class="text-2xl font-black text-gray-900 mt-1">Rp20.000<span class="text-xs font-normal text-gray-400">/bulan</span></p>
        <p class="text-xs text-gray-400 mt-1">Kewajiban bulanan seluruh fungsionaris</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tarif Denda Keterlambatan</p>
        <p class="text-2xl font-black text-amber-600 mt-1">Rp10.000<span class="text-xs font-normal text-gray-400">/bulan</span></p>
        <p class="text-xs text-gray-400 mt-1">Dikenakan bila melewati batas tenggat</p>
    </div>
    <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-5 shadow-sm">
        <p class="text-xs font-bold text-[#1A6B6B] uppercase tracking-wider">Total Tunggakan Anda</p>
        <p class="text-2xl font-black text-[#1A6B6B] mt-1">Rp{{ number_format($totalTunggakan, 0, ',', '.') }}</p>
        <p class="text-xs text-[#1A6B6B]/80 mt-1">{{ $unpaidMonths->count() }} bulan tagihan belum diselesaikan</p>
    </div>
</div>

{{-- Kotak Informasi Rekening Bank Tujuan Transfer (Point 4: Field Atas Nama diperbaiki) --}}
<div class="mb-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-teal-50 text-[#1A6B6B] flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900">Pilihan Rekening Bank Tujuan Transfer</h3>
                <p class="text-xs text-gray-500">Pilih rekening bank atau e-wallet resmi pembayaran kas BEM FILKOM UB melalui dropdown di bawah</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-[#1A6B6B] border border-teal-200 self-start sm:self-auto">
            <span class="w-2 h-2 rounded-full bg-[#1A6B6B]"></span>
            5 Bank & E-Wallet Tersedia
        </span>
    </div>

    {{-- Dropdown Selector Bank --}}
    <div class="mb-4">
        <label for="bank-selector" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1.5">
            Pilih Bank / E-Wallet Tujuan:
        </label>
        <div class="relative">
            <select id="bank-selector" onchange="changeBank(this.value)"
                    class="w-full appearance-none rounded-2xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-sm font-bold text-gray-900 shadow-xs focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition pr-10 cursor-pointer">
                <option value="mandiri" selected>Bank Mandiri — 1440025807527 (a.n. Nayla Shafa Khairunnisa)</option>
                <option value="seabank">SeaBank — 901916061290 (a.n. Revalia Rahmadani)</option>
                <option value="bca">Bank BCA — 0113583468 (a.n. Raden Fatih Abdul Jabbar)</option>
                <option value="bri">Bank BRI — 004901029512533 (a.n. Cantika Putri Permatasari)</option>
                <option value="gopay">GoPay E-Wallet — 081315038604 (a.n. Wirya Ayu Amalia)</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Kartu Rincian Rekening Terpilih (Point 4: Atas Nama rendered valid) --}}
    <div class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50/30 to-white p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span id="bank-badge-name" class="px-2.5 py-0.5 rounded-md text-[11px] font-black tracking-wider border bg-blue-50 text-blue-800 border-blue-200">
                        MANDIRI
                    </span>
                    <span id="bank-badge-desc" class="text-xs text-gray-500">
                        Transfer Bank Mandiri
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-2">
                    <span id="bank-number" class="text-2xl font-black tracking-wider text-gray-900 font-mono select-all">
                        1440025807527
                    </span>
                </div>
                <div class="text-xs text-gray-600 mt-1.5 flex items-center gap-1.5">
                    <span class="text-gray-400 font-medium">Atas Nama:</span>
                    <span id="bank-owner" class="font-bold text-gray-900">
                        Nayla Shafa Khairunnisa (Biro ADKEU BEM FILKOM UB)
                    </span>
                </div>
            </div>

            <button type="button"
                    onclick="copyBankNumber(this)"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1A6B6B] px-5 py-3 text-xs font-bold text-white shadow-sm hover:bg-[#155757] transition shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span id="btn-copy-label">Salin Nomor</span>
            </button>
        </div>
        <div class="mt-4 pt-3.5 border-t border-gray-100 flex items-center gap-2 text-[11px] text-gray-500">
            <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Silakan transfer sejumlah nominal yang ingin Anda bayarkan ke nomor rekening resmi di atas, kemudian lengkapi formulir di bawah.</span>
        </div>
    </div>
</div>

{{-- Redesign Form Pembayaran: Dropdown Bulan & Input Manual Nominal (Point 3) --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
    {{-- Left: Form Input Pembayaran --}}
    <div class="lg:col-span-8 space-y-6">
        <form action="{{ route(auth()->user()->role === 'presbem' ? 'presbem.bayar-kas.submit' : 'anggota.bayar-kas.submit') }}"
              method="POST" enctype="multipart/form-data" id="form-bayar-kas" class="space-y-6">
            @csrf

            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm space-y-6">
                {{-- Header Form --}}
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Formulir Pembayaran Kas</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Pilih bulan tagihan dan masukkan nominal pembayaran secara manual.</p>
                    </div>
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-[#1A6B6B] border border-teal-200">
                        Tagihan Kas
                    </span>
                </div>

                @if($unpaidMonths->isEmpty())
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs text-emerald-800 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold">Seluruh Kewajiban Kas Pokok Anda Sudah Lunas!</span>
                            <p class="text-emerald-700 mt-0.5">Anda tetap dapat memilih periode di bawah untuk mencicil denda atau memantau riwayat pembayaran.</p>
                        </div>
                    </div>
                @endif
                    {{-- 1. Pilihan Komponen Pembayaran (Modular: Kas Pokok / Denda / Keduanya) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                1. Pilih Komponen Pembayaran *
                            </label>
                            <span class="text-[11px] text-gray-400">Pilih jenis tagihan yang ingin dibayar</span>
                        </div>

                        <input type="hidden" name="payment_type" id="payment_type" value="{{ old('payment_type', 'kas_dan_denda') }}">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="payment-type-cards">
                            {{-- Opsi 1: Kas Pokok Saja --}}
                            <div onclick="selectPaymentType('kas_only')" id="card-kas-only"
                                 class="payment-type-card cursor-pointer rounded-2xl border-2 p-3.5 transition flex flex-col justify-between border-gray-200 bg-white hover:border-[#1A6B6B]/40 hover:bg-teal-50/20">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 flex items-center justify-center">
                                                <div class="dot-inner w-2 h-2 rounded-full bg-[#1A6B6B] hidden"></div>
                                            </div>
                                            <span class="text-xs font-bold text-gray-900">Kas Pokok</span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">Rp20k</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 pl-6 leading-tight">Bayar iuran kas bulanan saja tanpa denda</p>
                                </div>
                            </div>

                            {{-- Opsi 2: Denda Saja --}}
                            <div onclick="selectPaymentType('denda_only')" id="card-denda-only"
                                 class="payment-type-card cursor-pointer rounded-2xl border-2 p-3.5 transition flex flex-col justify-between border-gray-200 bg-white hover:border-amber-400 hover:bg-amber-50/20">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="radio-dot w-4 h-4 rounded-full border-2 border-gray-300 flex items-center justify-center">
                                                <div class="dot-inner w-2 h-2 rounded-full bg-amber-600 hidden"></div>
                                            </div>
                                            <span class="text-xs font-bold text-gray-900">Denda Saja</span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">Cicil / Lunasi</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 pl-6 leading-tight">Bayar atau cicil denda tanpa bayar pokok</p>
                                </div>
                            </div>

                            {{-- Opsi 3: Kas Pokok + Denda --}}
                            <div onclick="selectPaymentType('kas_dan_denda')" id="card-kas-dan-denda"
                                 class="payment-type-card cursor-pointer rounded-2xl border-2 p-3.5 transition flex flex-col justify-between border-[#1A6B6B] bg-teal-50/30">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="radio-dot w-4 h-4 rounded-full border-2 border-[#1A6B6B] flex items-center justify-center">
                                                <div class="dot-inner w-2 h-2 rounded-full bg-[#1A6B6B]"></div>
                                            </div>
                                            <span class="text-xs font-bold text-gray-900">Kas + Denda</span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-teal-100 text-[#1A6B6B] border border-teal-300">Lengkap</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 pl-6 leading-tight">Bayar kas pokok beserta denda sekaligus</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Dropdown Pemilihan Bulan Tagihan --}}
                    <div class="border-t border-gray-100 pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <label for="period-select" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                2. Pilih Bulan Tagihan Kas *
                            </label>
                            <span class="text-[11px] text-gray-400">Pilih periode yang ingin Anda bayar</span>
                        </div>

                        <div id="periodList" class="space-y-3">
                            <div class="period-item relative">
                                <select name="periods[]" id="period-select" onchange="onPeriodChange()"
                                        class="w-full appearance-none rounded-2xl border border-gray-200 bg-gray-50/70 px-4 py-3.5 text-sm font-bold text-gray-900 shadow-xs focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition pr-10 cursor-pointer">
                                    @foreach($monthlyObligations as $item)
                                        <option value="{{ $item['period_key'] }}"
                                                data-kas="{{ $item['kas_amount'] }}"
                                                data-denda="{{ $item['late_fee'] }}"
                                                data-total="{{ $item['total'] }}"
                                                data-label="{{ $item['period_name'] }}"
                                                data-overdue="{{ $item['is_overdue'] ? '1' : '0' }}"
                                                data-status="{{ $item['status'] }}"
                                                data-status-label="{{ $item['status_label'] }}"
                                                data-can-pay="{{ $item['can_pay'] ? '1' : '0' }}"
                                                data-deadline="{{ $item['deadline']->locale('id')->translatedFormat('d M Y') }}"
                                                @selected(old('periods.0', $monthlyObligations->firstWhere('can_pay', true)['period_key'] ?? '') === $item['period_key'])>
                                            {{ $item['period_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Info box bulan terpilih --}}
                        <div id="period-info-card" class="mt-3 p-3.5 rounded-2xl bg-teal-50/40 border border-teal-100 text-xs flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-bold text-gray-800" id="info-period-label">—</span>
                                <span class="text-gray-500 ml-1.5" id="info-period-status">—</span>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-500">Tenggat: <strong id="info-period-deadline" class="text-gray-700">—</strong></span>
                            </div>
                        </div>

                        @if(!empty($accountLateFee) && $accountLateFee['fee_amount'] > 0)
                            <div class="mt-2 text-xs font-semibold text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200">
                                Denda akun ({{ $accountLateFee['late_months'] }} bulan keterlambatan) Rp{{ number_format($accountLateFee['fee_amount'], 0, ',', '.') }}
                            </div>
                        @endif

                        {{-- Tombol Tambah Bulan (Opsional untuk Multi-Bulan) --}}
                        <div class="mt-3 flex items-center justify-between">
                            <button type="button" onclick="addPeriodDropdown()" id="btn-add-period"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-teal-200 bg-teal-50/70 text-xs font-bold text-[#1A6B6B] hover:bg-teal-100 hover:border-teal-300 hover:text-[#155757] shadow-xs transition active:scale-[0.98]">
                                <svg class="w-4 h-4 text-[#1A6B6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Tambah bulan</span>
                            </button>
                            <span class="text-[11px] text-gray-400">Satu bukti transfer untuk semua periode yang Anda pilih</span>
                        </div>
                    </div>

                    {{-- 3. Input Field Nominal Pembayaran Manual & Alokasi Cerdas --}}
                    <div class="border-t border-gray-100 pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <label for="amount" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                3. Nominal Pembayaran Manual (Rp) *
                            </label>
                            <span class="text-[11px] text-gray-400">Ketik nominal sesuai transfer Anda</span>
                        </div>

                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 font-bold text-base">
                                Rp
                            </div>
                            <input type="number" id="amount" name="amount"
                                   value="{{ old('amount', 30000) }}" min="1000" step="1000"
                                   placeholder="30000"
                                   oninput="onAmountInput(this.value)"
                                   class="w-full rounded-2xl border border-gray-200 bg-white pl-12 pr-4 py-3.5 text-base font-extrabold text-gray-900 shadow-xs focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition"
                                   required>
                        </div>

                        {{-- Quick Nominal Suggestion Chips (Dinamis sesuai komponen terpilih) --}}
                        <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                            <span class="text-gray-400 text-[11px]">Saran Cepat:</span>
                            <div id="quick-chips-wrapper" class="inline-flex flex-wrap items-center gap-2">
                                {{-- Di-render via JS berdasarkan pilihan komponen dan periode --}}
                            </div>
                        </div>

                        {{-- Transparent Allocation Box (Kalkulator & Rincian Alokasi Cerdas) --}}
                        <div id="allocation-box" class="mt-3.5 rounded-2xl bg-teal-50/30 border border-teal-100 p-3.5 text-xs space-y-2">
                            <div class="flex items-center justify-between font-bold text-gray-700 pb-1.5 border-b border-teal-100/60">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#1A6B6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Alokasi Pembayaran Sistem:
                                </span>
                                <span id="alloc-type-label" class="text-[11px] font-bold text-[#1A6B6B] uppercase tracking-wider">Kas + Denda</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs pt-0.5">
                                <div class="bg-white/80 p-2.5 rounded-xl border border-gray-100">
                                    <div class="text-[11px] text-gray-500">Alokasi Kas Pokok:</div>
                                    <div class="font-extrabold text-emerald-800 text-sm mt-0.5" id="alloc-kas">Rp20.000</div>
                                </div>
                                <div class="bg-white/80 p-2.5 rounded-xl border border-gray-100">
                                    <div class="text-[11px] text-gray-500">Alokasi Denda:</div>
                                    <div class="font-extrabold text-amber-800 text-sm mt-0.5" id="alloc-denda">Rp10.000</div>
                                </div>
                            </div>
                            <p id="alloc-note" class="text-[11px] text-gray-500 italic pt-0.5 leading-snug">
                                Nominal dialokasikan untuk kas pokok dan denda keterlambatan periode terpilih.
                            </p>
                        </div>
                    </div>

                    {{-- 4. Unggah Bukti Transfer --}}
                    <div class="border-t border-gray-100 pt-5">
                        <label for="proof" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                            4. Unggah Berkas Bukti Transfer *
                        </label>
                        <div class="flex items-center gap-3">
                            <label for="proof" class="cursor-pointer inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition shadow-xs shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span>Pilih Berkas Struk</span>
                            </label>
                            <input type="file" id="proof" name="proof" required
                                   accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                                   onchange="updateFileName(this)"
                                   class="hidden">
                            <span id="file-chosen-text" class="text-xs text-gray-500 truncate max-w-sm">
                                Belum ada berkas dipilih (Format JPG, PNG, atau PDF maks 2 MB)
                            </span>
                        </div>
                    </div>

                    {{-- Tombol Kirim Form --}}
                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" id="btn-submit-pembayaran"
                                class="w-full sm:w-auto rounded-xl bg-[#1A6B6B] px-8 py-3.5 text-sm font-bold text-white hover:bg-[#155757] transition shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Kirim Bukti Pembayaran untuk Divalidasi
                        </button>
                    </div>
                </div>
        </form>
    </div>

    {{-- Right: Ringkasan Rincian Pembayaran --}}
    <div class="lg:col-span-4 space-y-6">
        <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sticky top-24">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-3 border-b border-gray-100">
                Rincian Pembayaran Anda
            </h3>

            <div class="space-y-4">
                {{-- Komponen Terpilih --}}
                <div class="rounded-2xl bg-gray-50/80 p-3.5 border border-gray-100 text-xs">
                    <p class="text-gray-400 font-medium">Komponen Pembayaran:</p>
                    <p class="font-bold text-[#1A6B6B] text-sm mt-0.5" id="summary-type-label">Kas + Denda</p>
                    <p class="text-[11px] text-gray-500 mt-0.5" id="summary-type-desc">Kas Pokok beserta Denda Keterlambatan</p>
                </div>

                {{-- Periode Terpilih --}}
                <div class="rounded-2xl bg-gray-50/80 p-3.5 border border-gray-100 text-xs">
                    <p class="text-gray-400 font-medium">Bulan Tagihan:</p>
                    <p class="font-bold text-gray-900 text-sm mt-0.5" id="summary-month-label">—</p>
                    <p class="text-[11px] text-gray-500 mt-0.5" id="summary-month-count">1 periode dipilih</p>
                </div>

                {{-- Alokasi Breakdown --}}
                <div class="rounded-2xl bg-teal-50/40 p-3.5 border border-teal-100 text-xs space-y-2">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Alokasi Kas:</span>
                        <strong class="text-emerald-800" id="summary-kas-alloc">Rp20.000</strong>
                    </div>
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Alokasi Denda:</span>
                        <strong class="text-amber-800" id="summary-denda-alloc">Rp10.000</strong>
                    </div>
                    <div class="border-t border-teal-200/60 pt-2 flex items-center justify-between">
                        <span class="text-teal-900 font-bold">Total Transfer:</span>
                        <span class="font-extrabold text-[#1A6B6B] text-lg" id="summary-nominal-display">
                            Rp30.000
                        </span>
                    </div>
                    <p class="text-[11px] text-teal-700/80 mt-0.5">Sesuai nominal manual yang diketik</p>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
                    ⚠️ <strong>Perhatian:</strong> Pastikan Anda telah melakukan transfer dengan nominal yang sama persis ke salah satu rekening resmi di atas sebelum mengirim bukti transfer.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Riwayat Pengajuan Pembayaran Sebelumnya --}}
<div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
        <div>
            <h3 class="text-base font-bold text-gray-900">Riwayat Pengajuan Pembayaran Anda</h3>
            <p class="text-xs text-gray-500">Status verifikasi berkas transfer yang telah Anda kirimkan</p>
        </div>
        <a href="{{ route(auth()->user()->role === 'presbem' ? 'presbem.riwayat-pembayaran' : 'anggota.riwayat-pembayaran') }}"
           class="text-xs font-bold text-[#1A6B6B] hover:underline">Lihat Selengkapnya →</a>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($payments->take(5) as $payment)
            <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50/50 transition">
                <div>
                    <div class="text-sm font-bold text-gray-900">
                        Periode {{ $payment->period->locale('id')->translatedFormat('F Y') }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        Diajukan: {{ $payment->submitted_at ? $payment->submitted_at->timezone('Asia/Jakarta')->format('d M Y H:i') : $payment->created_at->format('d M Y') }} WIB
                    </div>
                    @if($payment->rejection_reason)
                        <div class="mt-1 text-xs text-red-600 font-semibold bg-red-50 px-2.5 py-1 rounded-lg border border-red-200 inline-block">
                            Alasan penolakan: {{ $payment->rejection_reason }}
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-sm font-extrabold text-gray-900">
                        Rp{{ number_format($payment->amount + $payment->late_fee_amount, 0, ',', '.') }}
                    </div>
                    @if($payment->late_fee_amount > 0)
                        <div class="text-[11px] text-red-600 font-semibold">Kas Rp{{ number_format($payment->amount, 0, ',', '.') }} + Denda Rp{{ number_format($payment->late_fee_amount, 0, ',', '.') }}</div>
                    @else
                        <div class="text-[11px] text-gray-400">Kas Pokok Rp{{ number_format($payment->amount, 0, ',', '.') }}</div>
                    @endif
                    <div class="mt-1">
                        @if($payment->status === 'approved')
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Lunas / Disetujui</span>
                        @elseif($payment->status === 'pending')
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Menunggu Validasi</span>
                        @elseif($payment->status === 'rejected')
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">Ditolak</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-gray-400">Belum ada riwayat pengajuan pembayaran.</div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Master data bank dan atas nama resmi
    const bankDetails = {
        mandiri: {
            name: 'MANDIRI',
            badge: 'Transfer Bank Mandiri',
            number: '1440025807527',
            owner: 'Nayla Shafa Khairunnisa (Biro ADKEU BEM FILKOM UB)',
            colorClass: 'bg-blue-50 text-blue-800 border-blue-200'
        },
        seabank: {
            name: 'SeaBank',
            badge: 'Transfer Bank SeaBank',
            number: '901916061290',
            owner: 'Revalia Rahmadani (Biro ADKEU BEM FILKOM UB)',
            colorClass: 'bg-orange-50 text-orange-800 border-orange-200'
        },
        bca: {
            name: 'BCA',
            badge: 'Transfer Bank Central Asia (BCA)',
            number: '0113583468',
            owner: 'Raden Fatih Abdul Jabbar (Biro ADKEU BEM FILKOM UB)',
            colorClass: 'bg-indigo-50 text-indigo-800 border-indigo-200'
        },
        bri: {
            name: 'BRI',
            badge: 'Transfer Bank Rakyat Indonesia (BRI)',
            number: '004901029512533',
            owner: 'Cantika Putri Permatasari (Biro ADKEU BEM FILKOM UB)',
            colorClass: 'bg-sky-50 text-sky-800 border-sky-200'
        },
        gopay: {
            name: 'GoPay',
            badge: 'E-Wallet GoPay',
            number: '081315038604',
            owner: 'Wirya Ayu Amalia (Biro ADKEU BEM FILKOM UB)',
            colorClass: 'bg-emerald-50 text-emerald-800 border-emerald-200'
        }
    };

    function changeBank(bankKey) {
        const bank = bankDetails[bankKey];
        if (!bank) return;

        const badgeName = document.getElementById('bank-badge-name');
        const badgeDesc = document.getElementById('bank-badge-desc');
        const numberEl = document.getElementById('bank-number');
        const ownerEl = document.getElementById('bank-owner');

        if (badgeName) {
            badgeName.innerText = bank.name;
            badgeName.className = 'px-2.5 py-0.5 rounded-md text-[11px] font-black tracking-wider border ' + bank.colorClass;
        }
        if (badgeDesc) badgeDesc.innerText = bank.badge;
        if (numberEl) numberEl.innerText = bank.number;
        if (ownerEl) ownerEl.innerText = bank.owner;
    }

    function copyBankNumber(btn) {
        const numberEl = document.getElementById('bank-number');
        if (!numberEl) return;
        const text = numberEl.innerText.trim();

        navigator.clipboard.writeText(text).then(() => {
            const label = document.getElementById('btn-copy-label');
            if (label) {
                const orig = label.innerText;
                label.innerText = 'Tersalin!';
                setTimeout(() => { label.innerText = orig; }, 2000);
            }
        }).catch(() => {
            alert('Nomor rekening: ' + text);
        });
    }

    function updateFileName(input) {
        const textSpan = document.getElementById('file-chosen-text');
        if (!textSpan) return;
        if (input.files && input.files.length > 0) {
            textSpan.innerText = input.files[0].name + ' (' + Math.round(input.files[0].size / 1024) + ' KB)';
            textSpan.classList.add('text-[#1A6B6B]', 'font-bold');
        } else {
            textSpan.innerText = 'Belum ada berkas dipilih (Format JPG, PNG, atau PDF maks 2 MB)';
            textSpan.classList.remove('text-[#1A6B6B]', 'font-bold');
        }
    }

    let currentPaymentType = 'kas_dan_denda';
    const totalTunggakanKas = {{ (int) $totalTunggakanKas }};
    const totalTunggakanDenda = {{ (int) $totalTunggakanDenda }};
    const totalTunggakanSemua = {{ (int) $totalTunggakan }};

    function selectPaymentType(type) {
        currentPaymentType = type;
        const hiddenInput = document.getElementById('payment_type');
        if (hiddenInput) hiddenInput.value = type;

        // Reset styling all cards
        const cardKas = document.getElementById('card-kas-only');
        const cardDenda = document.getElementById('card-denda-only');
        const cardBoth = document.getElementById('card-kas-dan-denda');

        [cardKas, cardDenda, cardBoth].forEach(c => {
            if (!c) return;
            c.classList.remove('border-[#1A6B6B]', 'bg-teal-50/30', 'border-amber-500', 'bg-amber-50/30');
            c.classList.add('border-gray-200', 'bg-white');
            const dotInner = c.querySelector('.dot-inner');
            const dotOuter = c.querySelector('.radio-dot');
            if (dotInner) dotInner.classList.add('hidden');
            if (dotOuter) dotOuter.className = 'radio-dot w-4 h-4 rounded-full border-2 border-gray-300 flex items-center justify-center';
        });

        // Activate selected card
        let activeCard = cardBoth;
        let borderColor = 'border-[#1A6B6B]';
        let bgTint = 'bg-teal-50/30';
        let dotColor = 'border-[#1A6B6B]';
        let typeTitle = 'Kas + Denda';
        let typeDesc = 'Kas Pokok beserta Denda Keterlambatan';

        if (type === 'kas_only') {
            activeCard = cardKas;
            typeTitle = 'Kas Pokok Saja';
            typeDesc = 'Iuran bulanan kas tanpa denda';
        } else if (type === 'denda_only') {
            activeCard = cardDenda;
            borderColor = 'border-amber-500';
            bgTint = 'bg-amber-50/30';
            dotColor = 'border-amber-500';
            typeTitle = 'Denda Saja';
            typeDesc = 'Cicil atau lunasi denda keterlambatan';
        }

        if (activeCard) {
            activeCard.classList.remove('border-gray-200', 'bg-white');
            activeCard.classList.add(borderColor, bgTint);
            const dotInner = activeCard.querySelector('.dot-inner');
            const dotOuter = activeCard.querySelector('.radio-dot');
            if (dotInner) dotInner.classList.remove('hidden');
            if (dotOuter) dotOuter.className = 'radio-dot w-4 h-4 rounded-full border-2 ' + dotColor + ' flex items-center justify-center';
        }

        // Update sidebar labels
        const summaryTypeLabel = document.getElementById('summary-type-label');
        const summaryTypeDesc = document.getElementById('summary-type-desc');
        const allocTypeLabel = document.getElementById('alloc-type-label');
        if (summaryTypeLabel) summaryTypeLabel.innerText = typeTitle;
        if (summaryTypeDesc) summaryTypeDesc.innerText = typeDesc;
        if (allocTypeLabel) allocTypeLabel.innerText = typeTitle;

        // Auto preset nominal on type switch
        const select = document.getElementById('period-select');
        const opt = select ? select.options[select.selectedIndex] : null;
        const isOverdue = opt ? opt.dataset.overdue === '1' : false;

        let defaultNominal = 20000;
        if (type === 'denda_only') {
            defaultNominal = 10000;
        } else if (type === 'kas_dan_denda') {
            defaultNominal = isOverdue ? 30000 : 20000;
        }

        const amountInput = document.getElementById('amount');
        if (amountInput) {
            amountInput.value = defaultNominal;
        }

        renderQuickChips();
        recalculateAllocation();
    }

    function renderQuickChips() {
        const container = document.getElementById('quick-chips-wrapper');
        if (!container) return;

        let chips = [];
        if (currentPaymentType === 'kas_only') {
            chips.push({ label: 'Rp20.000 (1 Bulan Kas)', val: 20000 });
            if (totalTunggakanKas >= 40000) {
                chips.push({ label: 'Rp40.000 (2 Bulan Kas)', val: 40000 });
            }
            if (totalTunggakanKas > 20000) {
                chips.push({ label: 'Rp' + totalTunggakanKas.toLocaleString('id-ID') + ' (Semua Kas)', val: totalTunggakanKas });
            }
        } else if (currentPaymentType === 'denda_only') {
            chips.push({ label: 'Rp10.000 (Denda 1 Bulan)', val: 10000 });
            if (totalTunggakanDenda >= 20000) {
                chips.push({ label: 'Rp20.000 (Denda 2 Bulan)', val: 20000 });
            }
            if (totalTunggakanDenda > 10000) {
                chips.push({ label: 'Rp' + totalTunggakanDenda.toLocaleString('id-ID') + ' (Semua Denda)', val: totalTunggakanDenda });
            }
        } else {
            // kas_dan_denda
            chips.push({ label: 'Rp20.000 (Kas Pokok)', val: 20000 });
            chips.push({ label: 'Rp30.000 (Kas + Denda)', val: 30000 });
            if (totalTunggakanSemua > 30000) {
                chips.push({ label: 'Rp' + totalTunggakanSemua.toLocaleString('id-ID') + ' (Semua Tunggakan)', val: totalTunggakanSemua });
            }
        }

        container.innerHTML = chips.map(chip => `
            <button type="button" onclick="setNominal(${chip.val})"
                    class="px-2.5 py-1 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 hover:bg-teal-50 hover:border-teal-200 hover:text-[#1A6B6B] font-semibold transition text-[11px]">
                ${chip.label}
            </button>
        `).join('');
    }

    function onPeriodChange() {
        const select = document.getElementById('period-select');
        if (!select) return;
        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        const label = opt.dataset.label || opt.text;
        const isOverdue = opt.dataset.overdue === '1';
        const deadline = opt.dataset.deadline || '—';
        const status = opt.dataset.status || 'belum_bayar';

        // Update info box
        const infoLabel = document.getElementById('info-period-label');
        const infoStatus = document.getElementById('info-period-status');
        const infoDeadline = document.getElementById('info-period-deadline');

        if (infoLabel) infoLabel.innerText = label;
        if (infoStatus) {
            if (status === 'approved') {
                infoStatus.innerText = '✓ Lunas (Disetujui)';
                infoStatus.className = 'text-emerald-700 font-semibold ml-1.5';
            } else if (status === 'pending') {
                infoStatus.innerText = '⏳ Sedang Diverifikasi ADKEU';
                infoStatus.className = 'text-amber-600 font-semibold ml-1.5';
            } else if (status === 'rejected') {
                infoStatus.innerText = '✕ Pembayaran Ditolak';
                infoStatus.className = 'text-red-600 font-semibold ml-1.5';
            } else {
                infoStatus.innerText = isOverdue ? '⚠️ Melewati tenggat (Denda keterlambatan Rp10.000)' : '✓ Tepat waktu (Tanpa denda)';
                infoStatus.className = isOverdue ? 'text-red-600 font-semibold ml-1.5' : 'text-emerald-700 font-semibold ml-1.5';
            }
        }
        if (infoDeadline) infoDeadline.innerText = deadline;

        // Update summary
        const summaryLabel = document.getElementById('summary-month-label');
        if (summaryLabel) summaryLabel.innerText = label;

        recalculateAllocation();
    }

    function onAmountInput(val) {
        recalculateAllocation();
    }

    function recalculateAllocation() {
        const amountInput = document.getElementById('amount');
        const val = amountInput ? (parseInt(amountInput.value) || 0) : 0;

        let allocKas = 0;
        let allocDenda = 0;
        let note = '';

        if (currentPaymentType === 'kas_only') {
            allocKas = val;
            allocDenda = 0;
            note = 'Seluruh nominal Rp' + val.toLocaleString('id-ID') + ' dialokasikan murni untuk pembayaran kas pokok tanpa denda.';
        } else if (currentPaymentType === 'denda_only') {
            allocKas = 0;
            allocDenda = val;
            note = 'Seluruh nominal Rp' + val.toLocaleString('id-ID') + ' dialokasikan untuk cicilan/pelunasan denda keterlambatan.';
        } else {
            // kas_dan_denda
            if (val >= 20000) {
                allocKas = 20000;
                allocDenda = val - 20000;
                note = 'Rp20.000 dialokasikan untuk kas pokok, dan Rp' + allocDenda.toLocaleString('id-ID') + ' untuk denda keterlambatan.';
            } else {
                allocKas = val;
                allocDenda = 0;
                note = 'Nominal Rp' + val.toLocaleString('id-ID') + ' dialokasikan untuk kas pokok.';
            }
        }

        // Update allocation box
        const allocKasEl = document.getElementById('alloc-kas');
        const allocDendaEl = document.getElementById('alloc-denda');
        const allocNoteEl = document.getElementById('alloc-note');
        if (allocKasEl) allocKasEl.innerText = 'Rp' + allocKas.toLocaleString('id-ID');
        if (allocDendaEl) allocDendaEl.innerText = 'Rp' + allocDenda.toLocaleString('id-ID');
        if (allocNoteEl) allocNoteEl.innerText = note;

        // Update summary sidebar
        const summaryKasEl = document.getElementById('summary-kas-alloc');
        const summaryDendaEl = document.getElementById('summary-denda-alloc');
        const summaryNominal = document.getElementById('summary-nominal-display');
        if (summaryKasEl) summaryKasEl.innerText = 'Rp' + allocKas.toLocaleString('id-ID');
        if (summaryDendaEl) summaryDendaEl.innerText = 'Rp' + allocDenda.toLocaleString('id-ID');
        if (summaryNominal) summaryNominal.innerText = 'Rp' + val.toLocaleString('id-ID');
    }

    function setNominal(amount) {
        const amountInput = document.getElementById('amount');
        if (amountInput) {
            amountInput.value = amount;
            recalculateAllocation();
        }
    }

    function addPeriodDropdown() {
        const periodList = document.getElementById('periodList');
        const primarySelect = document.getElementById('period-select');
        if (!periodList || !primarySelect) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'period-item relative flex items-center gap-2 mt-2';

        const newSelect = primarySelect.cloneNode(true);
        newSelect.id = '';
        newSelect.removeAttribute('id');

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.innerHTML = '✕';
        removeBtn.className = 'h-11 w-11 flex items-center justify-center rounded-2xl bg-red-50 text-red-600 hover:bg-red-100 font-bold shrink-0 transition';
        removeBtn.onclick = function() {
            wrapper.remove();
            updatePeriodCount();
        };

        const selectWrap = document.createElement('div');
        selectWrap.className = 'relative flex-1';
        selectWrap.appendChild(newSelect);

        const chevron = document.createElement('div');
        chevron.className = 'pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500';
        chevron.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>';
        selectWrap.appendChild(chevron);

        wrapper.appendChild(selectWrap);
        wrapper.appendChild(removeBtn);

        periodList.append(wrapper);
        updatePeriodCount();
    }

    function updatePeriodCount() {
        const selects = document.querySelectorAll('select[name="periods[]"]');
        const countEl = document.getElementById('summary-month-count');
        if (countEl) {
            countEl.innerText = selects.length + ' periode dipilih';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        selectPaymentType('kas_dan_denda');
        onPeriodChange();
    });
</script>
@endpush
