@extends('layouts.anggota')

@section('title', 'Denda Saya')
@section('page-title', 'Denda Saya')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">Denda Saya</h2>
    <p class="mt-0.5 text-sm text-gray-500">Rincian denda keterlambatan kas dan status verifikasinya.</p>
</div>

<div class="mb-5 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <p class="text-xs font-semibold text-gray-500">Total akumulasi denda</p>
        <p class="mt-1 text-2xl font-extrabold text-gray-800">Rp{{ number_format($outstandingLateFees + $paidLateFees, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-2xl border border-red-100 bg-white p-5">
        <p class="text-xs font-semibold text-gray-500">Denda belum lunas / menunggu verifikasi</p>
        <p class="mt-1 text-2xl font-extrabold text-red-600">Rp{{ number_format($outstandingLateFees, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <p class="text-xs font-semibold text-gray-500">Denda lunas dan terverifikasi</p>
        <p class="mt-1 text-2xl font-extrabold text-emerald-600">Rp{{ number_format($paidLateFees, 0, ',', '.') }}</p>
    </div>
</div>

<div class="mb-5 rounded-xl border border-teal-100 bg-teal-50 px-4 py-3 text-sm text-teal-800">
    Denda flat Rp{{ number_format($lateFeePerMonth, 0, ',', '.') }} per bulan keterlambatan untuk seluruh tunggakan akun, bukan per tagihan kas. Pembayaran denda dikirim bersama kas dan dinyatakan lunas setelah bukti diverifikasi ADKEU.
</div>

<div class="space-y-3">
    @forelse($penalties as $penalty)
        @php
            [$statusLabel, $statusClass] = match ($penalty['status']) {
                'paid' => ['Lunas · Terverifikasi', 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                'pending' => ['Menunggu verifikasi', 'border-amber-200 bg-amber-50 text-amber-700'],
                default => ['Belum dibayar', 'border-red-200 bg-red-50 text-red-700'],
            };
        @endphp
        <article class="rounded-2xl border border-gray-100 bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-gray-900">Denda kas bulanan · {{ $penalty['period']->locale('id')->translatedFormat('F Y') }}</h3>
                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Akumulasi {{ $penalty['late_months'] }} bulan keterlambatan
                    </p>
                </div>
                <p class="text-lg font-extrabold {{ $penalty['status'] === 'paid' ? 'text-gray-700' : 'text-red-600' }}">
                    Rp{{ number_format($penalty['amount'], 0, ',', '.') }}
                </p>
            </div>
            @if($penalty['status'] === 'unpaid')
                <div class="mt-4 border-t border-gray-100 pt-3">
                    <a href="{{ route('anggota.bayar-kas') }}" class="inline-flex rounded-lg bg-[#1A6B6B] px-4 py-2 text-xs font-bold text-white hover:bg-[#155757]">
                        Bayar kas dan denda
                    </a>
                </div>
            @elseif($penalty['status'] === 'pending')
                <p class="mt-3 text-xs text-amber-700">Tagihan akan berubah menjadi lunas setelah pembayaran kas dan denda disetujui.</p>
            @endif
        </article>
    @empty
        <div class="rounded-2xl border border-gray-100 bg-white px-5 py-10 text-center">
            <p class="text-sm font-semibold text-gray-800">Tidak ada denda keterlambatan.</p>
            <p class="mt-1 text-xs text-gray-500">Tagihan denda akan tampil di sini jika pembayaran kas melewati tenggat.</p>
        </div>
    @endforelse
</div>
@endsection
