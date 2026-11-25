@extends('layouts.anggota')

@section('title', 'Riwayat Presensi')
@section('page-title', 'Riwayat Presensi')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">Riwayat Presensi</h2>
    <p class="text-sm text-gray-500 mt-0.5">Rekaman kehadiran piket dan kegiatan Anda</p>
</div>

{{-- Tabs --}}
<div class="flex gap-0 border-b border-gray-200 mb-6">
    <button id="tab-piket" onclick="switchTab('piket')"
        class="px-5 py-2.5 text-sm font-medium border-b-2 transition-all -mb-px border-[#1A8A8A] text-[#1A8A8A]">
        Piket
    </button>
    <button id="tab-kegiatan" onclick="switchTab('kegiatan')"
        class="px-5 py-2.5 text-sm font-medium border-b-2 transition-all -mb-px border-transparent text-gray-500 hover:text-gray-700">
        Kegiatan
    </button>
</div>

{{-- Piket Panel --}}
<div id="panel-piket">
    <div class="rounded-2xl border border-gray-100 bg-white p-8 text-center text-sm text-gray-500">
        Belum ada data presensi piket.
    </div>
</div>

{{-- Kegiatan Panel --}}
<div id="panel-kegiatan" class="{{ $activeTab === 'kegiatan' ? '' : 'hidden' }}">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="text-2xl font-extrabold text-emerald-600">{{ $ringkasanKegiatan['hadir'] }}</div>
            <div class="text-xs text-gray-500">Hadir</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="text-2xl font-extrabold text-amber-500">{{ $ringkasanKegiatan['izin'] }}</div>
            <div class="text-xs text-gray-500">Izin</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="text-2xl font-extrabold text-red-500">{{ $ringkasanKegiatan['alpha'] }}</div>
            <div class="text-xs text-gray-500">Alpha</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
            <div class="text-2xl font-extrabold text-[#1A6B6B]">{{ $ringkasanKegiatan['persentase'] }}%</div>
            <div class="text-xs text-gray-500">Kehadiran ({{ $ringkasanKegiatan['selesai'] }} kegiatan selesai)</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kegiatan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bukti Selfie</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
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
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-medium text-gray-800">{{ $kegiatan->nama }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $kegiatan->lokasi }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-xs font-medium text-gray-700">{{ $kegiatan->tanggal->locale('id')->isoFormat('D MMMM Y') }}</div>
                            @if($presensi)
                                <div class="text-[11px] font-mono text-gray-500 mt-0.5">🕒 {{ $presensi->waktu_presensi->copy()->timezone('Asia/Jakarta')->format('H:i:s') }} WIB · via {{ $presensi->metode === 'qr_code' ? 'QR Code' : 'Manual' }}</div>
                            @else
                                <div class="text-[11px] text-gray-400 mt-0.5">{{ $kegiatan->jam_mulai ?? 'Waktu belum ditentukan' }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($presensi?->bukti_foto)
                            <a href="{{ $presensi->bukti_foto_url }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-[#1A6B6B] border border-teal-200 text-xs font-medium transition"
                               title="Lihat Bukti Foto">
                                <span>📷</span>
                                <span>Lihat Foto</span>
                            </a>
                            @else
                            <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($statusKehadiran === 'hadir')
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-full px-3 py-1">Hadir</span>
                            @elseif($statusKehadiran === 'izin')
                                <span class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-3 py-1">Izin</span>
                            @elseif($statusKehadiran === 'tidak-hadir')
                                <span class="text-xs font-semibold text-red-600 bg-red-50 border border-red-200 rounded-full px-3 py-1">Alpha</span>
                            @else
                                <span class="text-xs font-semibold text-gray-600 bg-gray-100 border border-gray-200 rounded-full px-3 py-1">Belum presensi</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada agenda BEM.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function switchTab(tab) {
    const piketPanel = document.getElementById('panel-piket');
    const kegiatanPanel = document.getElementById('panel-kegiatan');
    const tabPiket = document.getElementById('tab-piket');
    const tabKegiatan = document.getElementById('tab-kegiatan');

    if (tab === 'piket') {
        piketPanel.classList.remove('hidden');
        kegiatanPanel.classList.add('hidden');
        tabPiket.classList.add('border-[#1A8A8A]', 'text-[#1A8A8A]');
        tabPiket.classList.remove('border-transparent', 'text-gray-500');
        tabKegiatan.classList.remove('border-[#1A8A8A]', 'text-[#1A8A8A]');
        tabKegiatan.classList.add('border-transparent', 'text-gray-500');
    } else {
        kegiatanPanel.classList.remove('hidden');
        piketPanel.classList.add('hidden');
        tabKegiatan.classList.add('border-[#1A8A8A]', 'text-[#1A8A8A]');
        tabKegiatan.classList.remove('border-transparent', 'text-gray-500');
        tabPiket.classList.remove('border-[#1A8A8A]', 'text-[#1A8A8A]');
        tabPiket.classList.add('border-transparent', 'text-gray-500');
    }
}

switchTab(@json($activeTab));
</script>
@endsection
