@extends('layouts.presbem')

@section('title', 'Riwayat Presensi')
@section('page-title', 'Riwayat Presensi')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Riwayat Presensi</h2>
    <p class="text-sm text-gray-500 mt-1">Rekaman kehadiran piket dan kegiatan Anda</p>
</div>

{{-- Sub Tabs (Piket / Kegiatan) --}}
<div class="flex items-center gap-6 border-b border-gray-200/80 mb-6">
    <button type="button" onclick="switchPresensiTab('piket')" id="tabBtnPiket"
            class="pb-3 text-sm font-bold {{ $activeTab === 'piket' ? 'text-[#1A6B6B] border-b-2 border-[#1A6B6B]' : 'text-gray-500 hover:text-gray-800' }} transition">
        Piket
    </button>
    <button type="button" onclick="switchPresensiTab('kegiatan')" id="tabBtnKegiatan"
            class="pb-3 text-sm font-bold {{ $activeTab === 'kegiatan' ? 'text-[#1A6B6B] border-b-2 border-[#1A6B6B]' : 'text-gray-500 hover:text-gray-800' }} transition">
        Kegiatan
    </button>
</div>

{{-- Panel Piket --}}
<div id="panel-piket" class="{{ $activeTab === 'piket' ? '' : 'hidden' }}">
    <div class="rounded-2xl border border-gray-100 bg-white p-8 text-center text-sm text-gray-500">
        Belum ada data presensi piket.
    </div>
</div>

{{-- Panel Kegiatan --}}
<div id="panel-kegiatan" class="{{ $activeTab === 'kegiatan' ? '' : 'hidden' }}">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm text-center">
            <div class="text-2xl font-black text-emerald-600">{{ $ringkasanKegiatan['hadir'] }}</div>
            <div class="text-xs font-semibold text-gray-500">Hadir</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm text-center">
            <div class="text-2xl font-black text-amber-500">{{ $ringkasanKegiatan['izin'] }}</div>
            <div class="text-xs font-semibold text-gray-500">Izin</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm text-center">
            <div class="text-2xl font-black text-red-600">{{ $ringkasanKegiatan['alpha'] }}</div>
            <div class="text-xs font-semibold text-gray-500">Alpha</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm text-center">
            <div class="text-2xl font-black text-[#1A6B6B]">{{ $ringkasanKegiatan['persentase'] }}%</div>
            <div class="text-xs font-semibold text-gray-500">Kehadiran ({{ $ringkasanKegiatan['selesai'] }} kegiatan selesai)</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/70 text-gray-400 font-semibold uppercase tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">Kegiatan</th>
                        <th class="px-6 py-3.5">Waktu Presensi</th>
                        <th class="px-6 py-3.5 text-center">Bukti Selfie</th>
                        <th class="px-6 py-3.5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/70 text-gray-700">
                    @forelse($kegiatanRiwayat as $kegiatan)
                        @php
                            $presensi = $kegiatan->presensis->first();
                            $statusKehadiran = $presensi?->status === 'hadir'
                                ? 'hadir'
                                : ($presensi?->status === 'izin' ? 'izin' : (
                                    $presensi?->status === 'tidak-hadir' || $kegiatan->status === 'selesai'
                                        ? 'tidak-hadir'
                                        : 'belum-presensi'
                                ));
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm">{{ $kegiatan->nama }}</div>
                                <div class="text-[11px] text-gray-400">{{ $kegiatan->lokasi }}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-800">
                                <div>{{ $kegiatan->tanggal->locale('id')->isoFormat('D MMMM Y') }}</div>
                                @if($presensi)
                                    <div class="text-[11px] font-mono text-emerald-700 mt-0.5">🕒 {{ $presensi->waktu_presensi->copy()->timezone('Asia/Jakarta')->format('H:i:s') }} WIB · via {{ $presensi->metode === 'qr_code' ? 'QR Code' : 'Manual' }}</div>
                                @else
                                    <div class="text-[11px] text-gray-400 mt-0.5">{{ $kegiatan->jam_mulai ?? 'Waktu belum ditentukan' }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($presensi?->bukti_foto)
                                <a href="{{ $presensi->bukti_foto_url }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-[#1A6B6B] border border-teal-200 text-xs font-medium transition">
                                    <span>📷</span>
                                    <span>Lihat Foto</span>
                                </a>
                                @else
                                <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($statusKehadiran === 'hadir')
                                    <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-full border border-emerald-200">Hadir</span>
                                @elseif($statusKehadiran === 'izin')
                                    <span class="inline-block px-3 py-1 bg-amber-50 text-amber-700 font-bold text-xs rounded-full border border-amber-200">Izin</span>
                                @elseif($statusKehadiran === 'tidak-hadir')
                                    <span class="inline-block px-3 py-1 bg-red-50 text-red-600 font-bold text-xs rounded-full border border-red-200">Alpha</span>
                                @else
                                    <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 font-bold text-xs rounded-full border border-gray-200">Belum presensi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                Belum ada agenda BEM.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchPresensiTab(tab) {
        const btnPiket = document.getElementById('tabBtnPiket');
        const btnKegiatan = document.getElementById('tabBtnKegiatan');
        const panelPiket = document.getElementById('panel-piket');
        const panelKegiatan = document.getElementById('panel-kegiatan');

        if (tab === 'piket') {
            btnPiket.className = 'pb-3 text-sm font-bold text-[#1A6B6B] border-b-2 border-[#1A6B6B] transition';
            btnKegiatan.className = 'pb-3 text-sm font-medium text-gray-500 hover:text-gray-800 transition';
            panelPiket.classList.remove('hidden');
            panelKegiatan.classList.add('hidden');
        } else {
            btnKegiatan.className = 'pb-3 text-sm font-bold text-[#1A6B6B] border-b-2 border-[#1A6B6B] transition';
            btnPiket.className = 'pb-3 text-sm font-medium text-gray-500 hover:text-gray-800 transition';
            panelPiket.classList.add('hidden');
            panelKegiatan.classList.remove('hidden');
        }
    }

    switchPresensiTab(@json($activeTab));
</script>
@endpush
@endsection
