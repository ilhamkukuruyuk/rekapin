@extends('layouts.presbem')

@section('title', 'Presensi Kegiatan')
@section('page-title', 'Presensi Kegiatan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Presensi Kegiatan PresBEM</h2>
        <p class="text-sm text-gray-500 mt-1">Presensi kehadiran kegiatan BEM menggunakan scan QR Code dan kamera selfie langsung.</p>
    </div>
    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white rounded-xl border border-gray-200/80 shadow-sm text-xs text-gray-600 font-medium self-start sm:self-auto">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span id="live-clock">{{ $currentTime }}</span>
    </div>
</div>

{{-- Flash & Error Alerts --}}
@if(session('success'))
<div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm">
    <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600 font-bold">✓</div>
    <div class="flex-1">
        <h4 class="text-sm font-bold text-emerald-900">Presensi Berhasil Dicatat!</h4>
        <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
    </div>
</div>
@endif

@if($errors->any())
<div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-start gap-3 shadow-sm">
    <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0 text-red-600 font-bold">✕</div>
    <div class="flex-1">
        <h4 class="text-sm font-bold text-red-900">Presensi Tidak Berhasil</h4>
        <ul class="text-xs text-red-700 mt-1 list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Tabs --}}
<div class="flex gap-2 border-b border-gray-200 mb-6 overflow-x-auto">
    <button id="tab-scan" onclick="switchTab('scan')"
        class="tab-btn px-5 py-3 text-sm font-bold border-b-2 transition-all -mb-px flex items-center gap-2 whitespace-nowrap
               {{ $activeTab === 'scan' ? 'border-[#1A6B6B] text-[#1A6B6B]' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
        </svg>
        Presensi Hadir (QR & Selfie)
    </button>

    <button id="tab-izin" onclick="switchTab('izin')"
        class="tab-btn px-5 py-3 text-sm font-bold border-b-2 transition-all -mb-px flex items-center gap-2 whitespace-nowrap
               {{ $activeTab === 'izin' ? 'border-[#1A6B6B] text-[#1A6B6B]' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Ajukan Izin Kegiatan
    </button>

    <button id="tab-riwayat" onclick="switchTab('riwayat')"
        class="tab-btn px-5 py-3 text-sm font-bold border-b-2 transition-all -mb-px flex items-center gap-2 whitespace-nowrap
               {{ $activeTab === 'riwayat' ? 'border-[#1A6B6B] text-[#1A6B6B]' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Riwayat Kehadiran & Izin ({{ $riwayatPresensi->count() + $izinSaya->count() }})
    </button>
</div>

{{-- TAB 1: Scan QR & Upload Selfie Panel --}}
<div id="panel-scan" class="{{ $activeTab === 'scan' ? '' : 'hidden' }}">
    <form action="{{ route('presbem.presensi-kegiatan.store') }}" method="POST" enctype="multipart/form-data" id="form-presensi" onsubmit="return validateSubmit(event)">
        @csrf
        <input type="hidden" name="bukti_foto_base64" id="bukti_foto_base64">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left Column: QR Code Scanner --}}
            <div class="lg:col-span-7 bg-white rounded-3xl border border-gray-100 p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1">1. Scan QR Code Presensi</h3>
                    <p class="text-xs text-gray-400 mb-5">Arahkan kamera ke QR Code kegiatan atau masukkan kode token secara manual</p>

                    <div class="relative w-full h-64 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center overflow-hidden mb-4">
                        <video id="qr-video" class="hidden w-full h-full object-cover rounded-xl" playsinline></video>
                        <canvas id="qr-canvas" class="hidden"></canvas>

                        <div id="scan-overlay" class="hidden absolute inset-0 pointer-events-none flex items-center justify-center">
                            <div class="w-44 h-44 border-2 border-[#1A8A8A] rounded-2xl relative shadow-[0_0_0_9999px_rgba(0,0,0,0.4)]">
                                <div class="absolute -top-1 -left-1 w-4 h-4 border-t-4 border-l-4 border-white rounded-tl"></div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 border-t-4 border-r-4 border-white rounded-tr"></div>
                                <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-4 border-l-4 border-white rounded-bl"></div>
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-4 border-r-4 border-white rounded-br"></div>
                            </div>
                        </div>

                        <div id="qr-placeholder" class="text-center p-6">
                            <div class="w-12 h-12 bg-teal-50 text-[#1A6B6B] rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-gray-700">Arahkan Kamera ke QR Code</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">atau ketik kode QR kegiatan di bawah</p>
                        </div>
                    </div>

                    <div class="flex justify-center mb-5">
                        <button type="button" id="btn-toggle-qr-cam" onclick="toggleQrCamera()"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            <span id="btn-toggle-qr-text">Aktifkan Kamera Scan</span>
                        </button>
                    </div>

                    <div>
                        <label for="qr_code" class="block text-xs font-bold text-gray-700 mb-1.5">
                            Kode Token QR Kegiatan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="qr_code" name="qr_code" required
                                   placeholder="MASUKKAN KODE QR KEGIATAN"
                                   class="w-full uppercase font-mono text-xs tracking-wider rounded-xl border border-gray-200 px-3.5 py-3 focus:border-[#1A8A8A] focus:outline-none focus:ring-1 focus:ring-[#1A8A8A] bg-gray-50/50">
                            <span class="absolute right-3.5 top-3.5 text-[10px] text-gray-400 font-mono">QR Token</span>
                        </div>
                    </div>

                    @if($kegiatanAktif->isNotEmpty())
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <p class="text-[11px] text-gray-500 font-bold mb-2">Sesi Kegiatan Sedang Berlangsung:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($kegiatanAktif as $keg)
                            <button type="button" onclick="selectQrCode('{{ $keg->qr_code }}', '{{ $keg->nama }}')"
                                    class="px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-[#1A6B6B] border border-teal-200 text-xs font-bold transition flex items-center gap-1.5">
                                <span>📌 {{ $keg->nama }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Selfie Photo Proof & Submit --}}
            <div class="lg:col-span-5 bg-white rounded-3xl border border-gray-100 p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">2. Foto Selfie Bukti Kehadiran</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Ambil foto selfie di lokasi kegiatan secara langsung</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-[#1A6B6B] border border-teal-200">
                            Langkah 2
                        </span>
                    </div>

                    <div class="relative w-full h-56 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center overflow-hidden mb-4">
                        <video id="selfie-video" class="hidden w-full h-full object-cover rounded-xl" playsinline></video>
                        <canvas id="selfie-canvas" class="hidden"></canvas>
                        <img id="selfie-preview" class="hidden w-full h-full object-cover rounded-xl" alt="Preview Foto Selfie">

                        <div id="selfie-placeholder" class="text-center p-4">
                            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-gray-700">Belum Ada Foto Selfie</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Wajib menyertakan bukti foto wajah di lokasi</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <button type="button" id="btn-selfie-cam" onclick="startSelfieCamera()"
                                class="py-2.5 px-3 bg-teal-50 hover:bg-teal-100 text-[#1A6B6B] border border-teal-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            <span id="btn-selfie-cam-text">Kamera Selfie</span>
                        </button>

                        <label for="bukti_foto_file" class="py-2.5 px-3 bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer text-center">
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Pilih Berkas</span>
                        </label>
                        <input type="file" id="bukti_foto_file" name="bukti_foto" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
                    </div>

                    <div id="snapshot-action" class="hidden mb-4">
                        <button type="button" onclick="takeSnapshot()"
                                class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                            Jepret Foto Selfie Sekarang
                        </button>
                    </div>

                    <div id="selfie-status-indicator" class="hidden mb-4 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span id="selfie-status-text">Foto selfie siap dikirim.</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl text-[11px] text-gray-500 flex items-start gap-2 mb-6">
                        <span class="text-teal-600">ℹ️</span>
                        <span>Waktu kehadiran akan dicatat secara <strong>real-time</strong> sesuai jam sistem saat Anda menekan tombol Simpan Presensi.</span>
                    </div>
                </div>

                <button type="submit" id="btn-submit-presensi"
                        class="w-full bg-[#1A6B6B] hover:bg-[#155757] text-white font-bold py-3.5 px-6 rounded-xl transition duration-200 shadow-md text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Simpan Presensi Kehadiran
                </button>
            </div>
        </div>
    </form>
</div>

{{-- TAB 2: Form Pengajuan Izin Kegiatan --}}
<div id="panel-izin" class="{{ $activeTab === 'izin' ? '' : 'hidden' }}">
    <div class="max-w-2xl mx-auto rounded-3xl border border-gray-100 bg-white p-6 sm:p-8 shadow-sm">
        <div class="mb-5 pb-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Form Pengajuan Izin Presensi Kegiatan</h3>
            <p class="text-xs text-gray-500 mt-0.5">
                Ajukan permohonan izin jika Anda berhalangan hadir pada kegiatan BEM FILKOM UB. Pengajuan wajib menyertakan dokumen pendukung untuk divalidasi oleh PSDI.
            </p>
        </div>

        <form action="{{ route('presbem.presensi-kegiatan.izin') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label for="kegiatan_id" class="block text-xs font-bold text-gray-700 mb-1.5">
                    Pilih Kegiatan BEM *
                </label>
                <select id="kegiatan_id" name="kegiatan_id" required
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:ring-1 focus:ring-[#1A6B6B]">
                    <option value="">-- Pilih Kegiatan --</option>
                    @foreach($kegiatanBisaIzin as $keg)
                        <option value="{{ $keg->id }}">
                            {{ $keg->nama }} ({{ $keg->tanggal->locale('id')->translatedFormat('d M Y') }} - {{ $keg->jam_mulai }} WIB)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="alasan" class="block text-xs font-bold text-gray-700 mb-1.5">
                    Alasan Izin Tidak Hadir *
                </label>
                <textarea id="alasan" name="alasan" rows="3" required
                          placeholder="Jelaskan alasan izin secara jelas dan sopan (sakit, tugas dinas luar, dll)..."
                          class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:ring-1 focus:ring-[#1A6B6B]"></textarea>
            </div>

            <div>
                <label for="bukti-izin" class="block text-xs font-bold text-gray-700 mb-1.5">
                    Dokumen Bukti Perizinan *
                </label>
                <p class="text-[11px] text-gray-400 mb-2">Unggah surat keterangan dokter, surat dispensasi, atau berkas pendukung resmi (PDF, JPG, PNG maks 2 MB).</p>
                <input type="file" id="bukti-izin" name="bukti" required
                       accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                       class="w-full rounded-xl border border-gray-200 px-3.5 py-2 text-xs text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-[#1A6B6B]">
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="rounded-xl bg-[#1A6B6B] px-6 py-3 text-xs font-bold text-white hover:bg-[#155757] transition shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Kirim Pengajuan Izin ke PSDI
                </button>
            </div>
        </form>
    </div>
</div>

{{-- TAB 3: Riwayat Kehadiran & Riwayat Izin --}}
<div id="panel-riwayat" class="{{ $activeTab === 'riwayat' ? '' : 'hidden' }} space-y-6">
    {{-- Status Pengajuan Izin --}}
    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Pengajuan Izin Kegiatan</h3>
                <p class="text-xs text-gray-500">Status validasi surat perizinan yang Anda ajukan ke PSDI</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-teal-50 text-[#1A6B6B]">
                {{ $izinSaya->count() }} Pengajuan
            </span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($izinSaya as $izin)
                <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-gray-50/50 transition">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-gray-900 text-sm">{{ $izin->kegiatan?->nama ?? 'Kegiatan' }}</h4>
                            @if($izin->status === 'disetujui')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Izin Disetujui</span>
                            @elseif($izin->status === 'menunggu')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Menunggu Validasi PSDI</span>
                            @elseif($izin->status === 'ditolak')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">Izin Ditolak</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Alasan: {{ $izin->alasan }}</p>
                        <p class="text-[11px] text-gray-400 mt-1">Diajukan pada {{ $izin->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                        @if($izin->catatan_reviewer)
                            <div class="mt-2 text-xs text-red-700 bg-red-50 p-2.5 rounded-xl border border-red-200 inline-block">
                                Catatan PSDI: {{ $izin->catatan_reviewer }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('presbem.presensi-kegiatan.izin.bukti', $izin) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-xs">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat Dokumen
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-400">Belum ada pengajuan izin kegiatan.</div>
            @endforelse
        </div>
    </div>

    {{-- Riwayat Presensi Hadir --}}
    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Riwayat Presensi Kehadiran</h3>
                <p class="text-xs text-gray-500">Daftar kehadiran kegiatan yang telah diverifikasi</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                {{ $riwayatPresensi->count() }} Kehadiran
            </span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($riwayatPresensi as $presensi)
                <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-gray-50/50 transition">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-gray-900 text-sm">{{ $presensi->kegiatan?->nama ?? 'Kegiatan BEM' }}</h4>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ ucfirst($presensi->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $presensi->waktu_presensi ? $presensi->waktu_presensi->timezone('Asia/Jakarta')->translatedFormat('l, d F Y - H:i') . ' WIB' : '-' }}
                            · Metode: {{ $presensi->metode === 'qr_code' ? 'Scan QR & Selfie' : 'Manual / Disetujui PSDI' }}
                        </p>
                    </div>
                    @if($presensi->bukti_foto)
                        <div>
                            <a href="{{ route('presbem.presensi-kegiatan.bukti', $presensi) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-xs">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Bukti Selfie
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-400">Belum ada riwayat kehadiran tercatat.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let qrStream = null;
    let qrAnimationFrame = null;
    let selfieStream = null;
    let qrCanvas = document.createElement('canvas');

    function switchTab(tab) {
        document.getElementById('panel-scan').classList.toggle('hidden', tab !== 'scan');
        document.getElementById('panel-izin').classList.toggle('hidden', tab !== 'izin');
        document.getElementById('panel-riwayat').classList.toggle('hidden', tab !== 'riwayat');

        document.getElementById('tab-scan').className = tab === 'scan'
            ? 'tab-btn px-5 py-3 text-sm font-bold border-b-2 border-[#1A6B6B] text-[#1A6B6B] -mb-px flex items-center gap-2 whitespace-nowrap'
            : 'tab-btn px-5 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px flex items-center gap-2 whitespace-nowrap';

        document.getElementById('tab-izin').className = tab === 'izin'
            ? 'tab-btn px-5 py-3 text-sm font-bold border-b-2 border-[#1A6B6B] text-[#1A6B6B] -mb-px flex items-center gap-2 whitespace-nowrap'
            : 'tab-btn px-5 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px flex items-center gap-2 whitespace-nowrap';

        document.getElementById('tab-riwayat').className = tab === 'riwayat'
            ? 'tab-btn px-5 py-3 text-sm font-bold border-b-2 border-[#1A6B6B] text-[#1A6B6B] -mb-px flex items-center gap-2 whitespace-nowrap'
            : 'tab-btn px-5 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px flex items-center gap-2 whitespace-nowrap';

        if (tab !== 'scan') {
            stopQrCamera();
            stopSelfieCamera();
        }
    }

    function selectQrCode(code, name) {
        document.getElementById('qr_code').value = code;
    }

    async function toggleQrCamera() {
        if (qrStream) {
            stopQrCamera();
        } else {
            startQrCamera();
        }
    }

    async function startQrCamera() {
        const video = document.getElementById('qr-video');
        const placeholder = document.getElementById('qr-placeholder');
        const overlay = document.getElementById('scan-overlay');
        const btnText = document.getElementById('btn-toggle-qr-text');

        try {
            qrStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
            });
            video.srcObject = qrStream;
            await video.play();
            video.classList.remove('hidden');
            placeholder.classList.add('hidden');
            overlay.classList.remove('hidden');
            btnText.innerText = 'Matikan Kamera';
            scanQrFrame();
        } catch (err) {
            alert('Tidak dapat mengakses kamera belakang: ' + err.message);
        }
    }

    function stopQrCamera() {
        const video = document.getElementById('qr-video');
        const placeholder = document.getElementById('qr-placeholder');
        const overlay = document.getElementById('scan-overlay');
        const btnText = document.getElementById('btn-toggle-qr-text');

        if (qrStream) {
            qrStream.getTracks().forEach(t => t.stop());
            qrStream = null;
        }
        if (qrAnimationFrame) {
            cancelAnimationFrame(qrAnimationFrame);
            qrAnimationFrame = null;
        }
        video.classList.add('hidden');
        placeholder.classList.remove('hidden');
        overlay.classList.add('hidden');
        if (btnText) btnText.innerText = 'Aktifkan Kamera Scan';
    }

    function scanQrFrame() {
        const video = document.getElementById('qr-video');
        if (!qrStream || video.readyState !== video.HAVE_ENOUGH_DATA) {
            qrAnimationFrame = requestAnimationFrame(scanQrFrame);
            return;
        }

        if (window.jsQR) {
            qrCanvas.width = video.videoWidth;
            qrCanvas.height = video.videoHeight;
            const ctx = qrCanvas.getContext('2d');
            ctx.drawImage(video, 0, 0, qrCanvas.width, qrCanvas.height);
            const imageData = ctx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code && code.data) {
                document.getElementById('qr_code').value = code.data;
                stopQrCamera();
                return;
            }
        }
        qrAnimationFrame = requestAnimationFrame(scanQrFrame);
    }

    async function startSelfieCamera() {
        const video = document.getElementById('selfie-video');
        const preview = document.getElementById('selfie-preview');
        const placeholder = document.getElementById('selfie-placeholder');
        const snapshotAction = document.getElementById('snapshot-action');
        const btnText = document.getElementById('btn-selfie-cam-text');

        if (selfieStream) {
            stopSelfieCamera();
            return;
        }

        try {
            selfieStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
            });
            video.srcObject = selfieStream;
            await video.play();
            video.classList.remove('hidden');
            preview.classList.add('hidden');
            placeholder.classList.add('hidden');
            snapshotAction.classList.remove('hidden');
            btnText.innerText = 'Tutup Kamera';
        } catch (err) {
            alert('Tidak dapat mengakses kamera selfie: ' + err.message);
        }
    }

    function stopSelfieCamera() {
        const video = document.getElementById('selfie-video');
        const snapshotAction = document.getElementById('snapshot-action');
        const btnText = document.getElementById('btn-selfie-cam-text');

        if (selfieStream) {
            selfieStream.getTracks().forEach(t => t.stop());
            selfieStream = null;
        }
        video.classList.add('hidden');
        if (snapshotAction) snapshotAction.classList.add('hidden');
        if (btnText) btnText.innerText = 'Kamera Selfie';
    }

    function takeSnapshot() {
        const video = document.getElementById('selfie-video');
        const canvas = document.getElementById('selfie-canvas');
        const preview = document.getElementById('selfie-preview');
        const base64Input = document.getElementById('bukti_foto_base64');
        const indicator = document.getElementById('selfie-status-indicator');
        const indicatorText = document.getElementById('selfie-status-text');

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        base64Input.value = dataUrl;
        document.getElementById('bukti_foto_file').value = '';

        preview.src = dataUrl;
        preview.classList.remove('hidden');
        video.classList.add('hidden');

        stopSelfieCamera();

        if (indicator) indicator.classList.remove('hidden');
        if (indicatorText) indicatorText.innerText = 'Foto selfie berhasil diambil dari webcam!';
        document.getElementById('btn-selfie-cam-text').innerText = 'Ambil Ulang Selfie';
    }

    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        stopSelfieCamera();
        document.getElementById('bukti_foto_base64').value = '';

        const preview = document.getElementById('selfie-preview');
        const placeholder = document.getElementById('selfie-placeholder');
        const indicator = document.getElementById('selfie-status-indicator');
        const indicatorText = document.getElementById('selfie-status-text');

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
            if (indicator) indicator.classList.remove('hidden');
            if (indicatorText) indicatorText.innerText = `Berkas "${file.name}" siap dikirim.`;
        };
        reader.readAsDataURL(file);
    }

    function validateSubmit(event) {
        const base64Input = document.getElementById('bukti_foto_base64');
        const fileInput = document.getElementById('bukti_foto_file');

        if (selfieStream && (!base64Input.value || base64Input.value.trim() === '')) {
            takeSnapshot();
        }

        const hasBase64 = base64Input && base64Input.value && base64Input.value.trim().length > 0;
        const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

        if (!hasBase64 && !hasFile) {
            event.preventDefault();
            alert('Wajib menyertakan foto selfie bukti kehadiran! Silakan klik "Kamera Selfie" atau "Pilih Berkas".');
            return false;
        }

        return true;
    }

    window.addEventListener('beforeunload', () => {
        stopQrCamera();
        stopSelfieCamera();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
@endpush
