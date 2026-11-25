@extends('layouts.psdi')

@section('title', 'Validasi Izin Kegiatan')
@section('page-title', 'Validasi Izin Presensi Kegiatan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Validasi Perizinan Fungsionaris</h2>
        <p class="text-sm text-gray-500 mt-1">Tinjau surat dan dokumen permohonan izin ketidakhadiran kegiatan BEM FILKOM UB.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm">
    <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600 font-bold">✓</div>
    <div class="flex-1">
        <h4 class="text-sm font-bold text-emerald-900">Berhasil!</h4>
        <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
    </div>
</div>
@endif

@if($errors->any())
<div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-start gap-3 shadow-sm">
    <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0 text-red-600 font-bold">✕</div>
    <div class="flex-1">
        <h4 class="text-sm font-bold text-red-900">Terjadi Kesalahan</h4>
        <ul class="text-xs text-red-700 mt-1 list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Stat Counters --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('psdi.perizinan', ['status' => 'all']) }}"
       class="p-4 rounded-2xl border transition shadow-sm {{ $status === 'all' ? 'border-[#1A6B6B] bg-white ring-2 ring-[#1A6B6B]/20' : 'border-gray-100 bg-white hover:bg-gray-50' }}">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Izin</p>
        <p class="text-2xl font-black text-gray-900 mt-1">{{ $counts['total'] }}</p>
    </a>
    <a href="{{ route('psdi.perizinan', ['status' => 'menunggu']) }}"
       class="p-4 rounded-2xl border transition shadow-sm {{ $status === 'menunggu' ? 'border-amber-500 bg-amber-50/40 ring-2 ring-amber-500/20' : 'border-gray-100 bg-white hover:bg-gray-50' }}">
        <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Menunggu</p>
        <p class="text-2xl font-black text-amber-600 mt-1">{{ $counts['menunggu'] }}</p>
    </a>
    <a href="{{ route('psdi.perizinan', ['status' => 'disetujui']) }}"
       class="p-4 rounded-2xl border transition shadow-sm {{ $status === 'disetujui' ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-gray-100 bg-white hover:bg-gray-50' }}">
        <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Disetujui</p>
        <p class="text-2xl font-black text-emerald-600 mt-1">{{ $counts['disetujui'] }}</p>
    </a>
    <a href="{{ route('psdi.perizinan', ['status' => 'ditolak']) }}"
       class="p-4 rounded-2xl border transition shadow-sm {{ $status === 'ditolak' ? 'border-red-500 bg-red-50/40 ring-2 ring-red-500/20' : 'border-gray-100 bg-white hover:bg-gray-50' }}">
        <p class="text-xs font-bold text-red-600 uppercase tracking-wider">Ditolak</p>
        <p class="text-2xl font-black text-red-600 mt-1">{{ $counts['ditolak'] }}</p>
    </a>
</div>

{{-- Tabel Daftar Izin --}}
<div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-gray-900">Daftar Pengajuan Izin Presensi</h3>
            <p class="text-xs text-gray-500">Tinjau berkas alasan dan dokumen sebelum menyetujui atau menolak permohonan</p>
        </div>
        <span class="text-xs font-semibold text-gray-400">Total {{ $izins->count() }} Data</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-6 py-3.5">Fungsionaris</th>
                    <th class="px-6 py-3.5">Kegiatan</th>
                    <th class="px-6 py-3.5">Alasan Izin</th>
                    <th class="px-6 py-3.5">Dokumen Bukti</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($izins as $izin)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-sm text-gray-900">{{ $izin->user?->name ?? 'Fungsionaris' }}</div>
                            <div class="text-[11px] text-gray-400">{{ $izin->user?->nim ?? 'NIM -' }} · {{ $izin->user?->departemen ?? 'Umum' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $izin->kegiatan?->nama ?? '-' }}</div>
                            <div class="text-[11px] text-gray-400">{{ $izin->kegiatan?->tanggal ? $izin->kegiatan->tanggal->locale('id')->translatedFormat('d M Y') : '-' }}</div>
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            <p class="text-gray-800 font-medium whitespace-pre-wrap">{{ $izin->alasan }}</p>
                            @if($izin->catatan_reviewer)
                                <div class="mt-1 text-[11px] text-red-600 bg-red-50 p-1.5 rounded-lg border border-red-200">
                                    Catatan PSDI: {{ $izin->catatan_reviewer }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($izin->bukti_path)
                                <a href="{{ route('psdi.perizinan.bukti', $izin) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Buka Berkas
                                </a>
                            @else
                                <span class="text-gray-400">Tidak ada</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($izin->status === 'disetujui')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    ✓ Disetujui
                                </span>
                            @elseif($izin->status === 'menunggu')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    ⏳ Menunggu Validasi
                                </span>
                            @elseif($izin->status === 'ditolak')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
                                    ✕ Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($izin->status === 'menunggu')
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Form Setujui --}}
                                    <form action="{{ route('psdi.perizinan.review', $izin) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui izin ini?')">
                                        @csrf
                                        <input type="hidden" name="action" value="setujui">
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-xl bg-[#1A6B6B] hover:bg-[#155757] text-white font-bold text-xs shadow-xs transition flex items-center gap-1">
                                            ✓ Setujui
                                        </button>
                                    </form>

                                    {{-- Tombol Buka Modal Tolak --}}
                                    <button type="button" onclick="openRejectModal({{ $izin->id }}, '{{ addslashes($izin->user?->name) }}', '{{ addslashes($izin->kegiatan?->nama) }}')"
                                            class="px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold text-xs shadow-xs transition flex items-center gap-1">
                                        ✕ Tolak
                                    </button>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">
                                    Oleh {{ $izin->reviewer?->name ?? 'PSDI' }} ({{ $izin->reviewed_at ? $izin->reviewed_at->format('d/m/Y') : '-' }})
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">
                            Tidak ada data pengajuan izin pada filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tolak Izin --}}
<div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-gray-100 relative">
        <h3 class="text-base font-bold text-gray-900 mb-2">Tolak Pengajuan Izin</h3>
        <p class="text-xs text-gray-500 mb-4" id="reject-modal-desc">
            Masukkan catatan atau alasan mengapa pengajuan izin fungsionaris ini ditolak:
        </p>

        <form id="reject-form" action="" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="action" value="tolak">
            <div>
                <label for="catatan" class="block text-xs font-bold text-gray-700 mb-1">Catatan Penolakan *</label>
                <textarea id="catatan" name="catatan" rows="3" required
                          placeholder="Misal: Bukti surat tidak valid, tidak bertanda tangan resmi, atau kegiatan bersifat wajib tanpa dispensasi..."
                          class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-xs focus:border-red-500 focus:ring-1 focus:ring-red-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeRejectModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-xs font-bold shadow-sm transition">
                    Konfirmasi Tolak Izin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openRejectModal(id, name, kegiatan) {
        const form = document.getElementById('reject-form');
        form.action = `/psdi/perizinan/${id}/review`;
        document.getElementById('reject-modal-desc').innerText = `Tolak izin untuk fungsionaris "${name}" pada kegiatan "${kegiatan}":`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endpush
