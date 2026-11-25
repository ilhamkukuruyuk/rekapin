@extends('layouts.bendahara')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Dashboard ADKEU</h2>
    <p class="mt-1 text-sm text-gray-500">Ringkasan transaksi dan pembayaran yang tercatat.</p>
</div>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <div class="text-2xl font-bold text-gray-900">Rp{{ number_format($saldo, 0, ',', '.') }}</div>
        <div class="mt-1 text-sm text-gray-500">Saldo kas</div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <div class="text-2xl font-bold text-emerald-700">Rp{{ number_format($pemasukan, 0, ',', '.') }}</div>
        <div class="mt-1 text-sm text-gray-500">Total pemasukan</div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <div class="text-2xl font-bold text-red-600">Rp{{ number_format($pengeluaran, 0, ',', '.') }}</div>
        <div class="mt-1 text-sm text-gray-500">Total pengeluaran</div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <div class="text-2xl font-bold text-gray-900">{{ $pendingPaymentsCount }}</div>
        <div class="mt-1 text-sm text-gray-500">Pembayaran menunggu verifikasi</div>
    </div>
</div>

<section class="rounded-2xl border border-gray-100 bg-white p-6">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-900">Transaksi Terbaru</h3>
        <a href="{{ route('bendahara.kas') }}" class="text-xs font-semibold text-[#1A6B6B] hover:underline">Kelola kas</a>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($transaksiTerbaru as $transaksi)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $transaksi->description }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $transaksi->member?->name ?: 'Transaksi umum' }}
                        · {{ $transaksi->transaction_date->format('d/m/Y') }}
                    </p>
                </div>
                <span class="text-sm font-bold {{ $transaksi->type === 'masuk' ? 'text-emerald-700' : 'text-red-600' }}">
                    {{ $transaksi->type === 'masuk' ? '+' : '-' }}Rp{{ number_format($transaksi->amount, 0, ',', '.') }}
                </span>
            </div>
        @empty
            <p class="py-8 text-center text-sm text-gray-500">Belum ada transaksi kas.</p>
        @endforelse
    </div>
</section>
@endsection
