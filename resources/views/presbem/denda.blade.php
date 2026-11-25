@extends('layouts.presbem')

@section('title', 'Denda Saya')
@section('page-title', 'Denda Saya')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Denda Saya</h2>
    <p class="mt-1 text-sm text-gray-500">Ringkasan keterlambatan pembayaran kas Anda.</p>
</div>

<div class="mb-6 rounded-2xl bg-[#1A6B6B] p-6 text-white">
    <div class="text-xs font-semibold uppercase tracking-wider text-teal-100">Denda belum lunas</div>
    <div class="mt-1 text-3xl font-black">Rp{{ number_format($outstandingLateFees, 0, ',', '.') }}</div>
    <div class="mt-1 text-xs text-teal-100">{{ $penalties->where('status', '!=', 'paid')->count() }} tagihan</div>
</div>

<section class="rounded-2xl border border-gray-100 bg-white p-5">
    <h3 class="mb-3 text-sm font-bold text-gray-900">Rincian denda</h3>
    <div class="divide-y divide-gray-100">
        @forelse($penalties as $penalty)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Denda keterlambatan kas</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $penalty['period']->locale('id')->isoFormat('MMMM Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-900">Rp{{ number_format($penalty['amount'], 0, ',', '.') }}</p>
                    <span class="text-xs text-gray-500">{{ match ($penalty['status']) { 'paid' => 'Lunas', 'pending' => 'Menunggu verifikasi', default => 'Belum lunas' } }}</span>
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-sm text-gray-500">Tidak ada denda.</p>
        @endforelse
    </div>
</section>
@endsection
