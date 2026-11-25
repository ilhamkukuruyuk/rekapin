@extends('layouts.anggota')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">Dashboard Saya</h2>
    <p class="mt-0.5 text-sm text-gray-500">Ringkasan akun dan aktivitas Anda.</p>
</div>

<section class="mb-5 rounded-2xl bg-[#1A6B6B] p-6 text-white">
    <div class="flex items-center gap-4">
        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#2A8A8A] text-lg font-bold">
            {{ auth()->user()->initials }}
        </div>
        <div>
            <h3 class="text-lg font-bold">{{ auth()->user()->name }}</h3>
            <p class="text-sm text-white/70">{{ auth()->user()->nim ?: 'NIM belum diisi' }} · {{ auth()->user()->role_badge }}</p>
        </div>
    </div>
</section>

<div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
    <section class="rounded-2xl border border-gray-100 bg-white p-5">
        <div class="mb-3 text-sm font-medium text-gray-600">Kas bulan ini</div>
        @php
            $paymentStatus = match ($currentPayment?->status) {
                \App\Models\CashPayment::STATUS_APPROVED => 'Lunas',
                \App\Models\CashPayment::STATUS_PENDING => 'Menunggu verifikasi',
                \App\Models\CashPayment::STATUS_REJECTED => 'Ditolak',
                default => 'Belum ada pembayaran',
            };
        @endphp
        <div class="text-xl font-bold text-gray-900">{{ $paymentStatus }}</div>
        @if($currentPayment)
            <div class="mt-1 text-xs text-gray-500">Rp{{ number_format($currentPayment->amount, 0, ',', '.') }}</div>
        @endif
    </section>

    <a href="{{ route('anggota.denda') }}" class="rounded-2xl border border-gray-100 bg-white p-5 transition hover:border-red-200">
        <div class="mb-3 text-sm font-medium text-gray-600">Denda belum lunas</div>
        <div class="text-xl font-bold {{ $outstandingLateFees > 0 ? 'text-red-600' : 'text-gray-900' }}">
            Rp{{ number_format($outstandingLateFees, 0, ',', '.') }}
        </div>
        <div class="mt-1 text-xs text-gray-500">{{ $activeLateFeeCount }} tagihan</div>
    </a>
</div>

<section class="rounded-2xl border border-gray-100 bg-white p-5">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800">Kegiatan Mendatang</h3>
        <a href="{{ route('anggota.presensi-kegiatan') }}" class="text-xs font-semibold text-[#1A6B6B] hover:underline">Presensi kegiatan</a>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($upcomingActivities as $kegiatan)
            <div class="py-3">
                <div class="text-sm font-semibold text-gray-800">{{ $kegiatan->nama }}</div>
                <div class="mt-1 text-xs text-gray-500">
                    {{ $kegiatan->tanggal->format('d/m/Y') }}
                    @if($kegiatan->jam_mulai) · {{ $kegiatan->jam_mulai }} WIB @endif
                    @if($kegiatan->lokasi) · {{ $kegiatan->lokasi }} @endif
                </div>
            </div>
        @empty
            <p class="py-6 text-center text-sm text-gray-500">Belum ada kegiatan mendatang.</p>
        @endforelse
    </div>
</section>
@endsection
