@extends('layouts.psdi')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Dashboard PSDI</h2>
    <p class="mt-1 text-sm text-gray-500">Ringkasan kegiatan yang tercatat di sistem.</p>
</div>

<section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-900">Kegiatan Terbaru</h3>
        <a href="{{ route('psdi.kegiatan') }}" class="text-xs font-semibold text-[#1A6B6B] hover:underline">Kelola kegiatan</a>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($kegiatans as $kegiatan)
            <div class="flex flex-wrap items-center justify-between gap-3 py-4">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $kegiatan->nama }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $kegiatan->tanggal->format('d/m/Y') }}
                        · {{ $kegiatan->jam_mulai ?: 'Waktu belum ditentukan' }}
                        @if($kegiatan->lokasi) · {{ $kegiatan->lokasi }} @endif
                    </p>
                </div>
                <span class="rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-600">{{ $kegiatan->presensis_count }} presensi</span>
            </div>
        @empty
            <p class="py-8 text-center text-sm text-gray-500">Belum ada kegiatan.</p>
        @endforelse
    </div>
</section>
@endsection
