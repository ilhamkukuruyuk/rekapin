@extends('layouts.psdi')

@section('title', 'Kegiatan')
@section('page-title', 'Kegiatan')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Kegiatan</h2>
        <p class="mt-1 text-sm text-gray-500">Buat kegiatan, buka sesi presensi, dan tampilkan QR aktif.</p>
    </div>
    <button type="button" onclick="openModalTambahKegiatan()" class="rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#155757]">
        + Tambah Kegiatan
    </button>
</div>

@if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        <ul class="list-inside list-disc space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-5 inline-flex flex-wrap gap-2 rounded-2xl bg-gray-100/80 p-1.5 text-xs font-semibold">
    <button type="button" onclick="filterKategori('all')" data-filter="all" class="keg-filter rounded-xl bg-[#1A6B6B] px-4 py-2 text-white">Semua</button>
    <button type="button" onclick="filterKategori('akan-datang')" data-filter="akan-datang" class="keg-filter rounded-xl px-4 py-2 text-gray-600">Belum Dibuka</button>
    <button type="button" onclick="filterKategori('berlangsung')" data-filter="berlangsung" class="keg-filter rounded-xl px-4 py-2 text-gray-600">Sesi Aktif</button>
    <button type="button" onclick="filterKategori('selesai')" data-filter="selesai" class="keg-filter rounded-xl px-4 py-2 text-gray-600">Selesai</button>
</div>

<p id="kegiatanEmpty" class="{{ $kegiatans->isEmpty() ? '' : 'hidden' }} mb-5 rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
    Belum ada kegiatan. Tambahkan kegiatan untuk memulai.
</p>

<div id="kegiatanGrid" class="grid grid-cols-1 gap-5 md:grid-cols-2">
    @foreach($kegiatans as $kegiatan)
        @php
            $statusLabel = match ($kegiatan->status) {
                \App\Models\Kegiatan::STATUS_BERLANGSUNG => 'Sesi Aktif',
                \App\Models\Kegiatan::STATUS_SELESAI => 'Selesai',
                default => 'Belum Dibuka',
            };
            $statusClass = match ($kegiatan->status) {
                \App\Models\Kegiatan::STATUS_BERLANGSUNG => 'bg-teal-50 text-teal-800 border-teal-200',
                \App\Models\Kegiatan::STATUS_SELESAI => 'bg-gray-100 text-gray-600 border-gray-200',
                default => 'bg-amber-50 text-amber-800 border-amber-200',
            };
        @endphp
        <article class="kegiatan-card flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-6 shadow-sm" data-category="{{ $kegiatan->status }}">
            <div>
                <div class="mb-2 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $kegiatan->nama }}</h3>
                    <span class="shrink-0 rounded-full border px-3 py-1 text-[11px] font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <p class="mb-4 text-xs text-gray-500">{{ $kegiatan->jenis }}{{ $kegiatan->deskripsi ? ' · '.$kegiatan->deskripsi : '' }}</p>
                <dl class="mb-5 grid grid-cols-2 gap-3 text-xs text-gray-600">
                    <div><dt class="text-gray-400">Tanggal</dt><dd class="mt-0.5 font-semibold">{{ $kegiatan->tanggal->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-400">Lokasi</dt><dd class="mt-0.5 font-semibold">{{ $kegiatan->lokasi }}</dd></div>
                    <div><dt class="text-gray-400">Waktu</dt><dd class="mt-0.5 font-semibold">{{ $kegiatan->jam_mulai ?: '—' }}{{ $kegiatan->jam_selesai ? ' - '.$kegiatan->jam_selesai : '' }}</dd></div>
                    <div><dt class="text-gray-400">Presensi</dt><dd class="mt-0.5 font-semibold">{{ $kegiatan->presensis_count }} anggota</dd></div>
                </dl>
            </div>

            <div class="flex flex-wrap gap-2 border-t border-gray-100 pt-4">
                @if($kegiatan->status === \App\Models\Kegiatan::STATUS_AKAN_DATANG)
                    <form action="{{ route('psdi.kegiatan.sesi.buka', $kegiatan) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#155757]">Buka Sesi Sekarang</button>
                    </form>
                    <span class="self-center text-xs text-gray-400">Sesi juga akan terbuka otomatis pada jam mulai.</span>
                @elseif($kegiatan->status === \App\Models\Kegiatan::STATUS_BERLANGSUNG)
                    <button type="button" data-kegiatan-id="{{ $kegiatan->id }}" data-qr-title="{{ $kegiatan->nama }}" data-qr-code="{{ $kegiatan->qr_code }}" onclick="openQrModal(this.dataset.qrTitle, this.dataset.qrCode)" class="rounded-xl bg-teal-50 px-4 py-2.5 text-xs font-bold text-[#1A6B6B] transition hover:bg-teal-100">Lihat QR Presensi</button>
                    <form action="{{ route('psdi.kegiatan.sesi.tutup', $kegiatan) }}" method="POST" onsubmit="return confirm('Tutup sesi presensi kegiatan ini? QR tidak dapat digunakan setelah sesi ditutup.')">
                        @csrf
                        <button type="submit" class="rounded-xl border border-red-200 px-4 py-2.5 text-xs font-bold text-red-700 transition hover:bg-red-50">Tutup Sesi</button>
                    </form>
                @else
                    <span class="text-xs font-medium text-gray-500">Sesi ditutup. QR tidak lagi berlaku.</span>
                @endif
            </div>
        </article>
    @endforeach
</div>

<div id="modalTambahKegiatan" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
            <h3 class="text-base font-bold text-gray-900">Buat Kegiatan Baru</h3>
            <button type="button" onclick="closeModalTambahKegiatan()" class="text-xl text-gray-400" aria-label="Tutup">×</button>
        </div>
        <form action="{{ route('psdi.kegiatan.store') }}" method="POST" class="space-y-4 p-6">
            @csrf
            <div>
                <label for="nama" class="mb-1.5 block text-xs font-semibold text-gray-700">Nama Kegiatan *</label>
                <input id="nama" name="nama" required maxlength="255" value="{{ old('nama') }}" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20">
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label for="tanggal" class="mb-1.5 block text-xs font-semibold text-gray-700">Tanggal *</label>
                    <input id="tanggal" name="tanggal" type="date" required value="{{ old('tanggal', now('Asia/Jakarta')->toDateString()) }}" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20">
                </div>
                <div>
                    <label for="jenis" class="mb-1.5 block text-xs font-semibold text-gray-700">Jenis Kegiatan *</label>
                    <input id="jenis" name="jenis" required maxlength="100" value="{{ old('jenis') }}" placeholder="Seminar, pelatihan, rapat..." class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label for="jam_mulai" class="mb-1.5 block text-xs font-semibold text-gray-700">Jam Mulai *</label>
                    <input id="jam_mulai" name="jam_mulai" type="time" required value="{{ old('jam_mulai') }}" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20">
                    @error('jam_mulai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="jam_selesai" class="mb-1.5 block text-xs font-semibold text-gray-700">Jam Selesai *</label>
                    <input id="jam_selesai" name="jam_selesai" type="time" required value="{{ old('jam_selesai') }}" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20">
                    @error('jam_selesai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="-mt-2 text-xs text-gray-500">Sesi presensi otomatis dibuka dan ditutup sesuai jam kegiatan (WIB).</p>
            <div>
                <label for="lokasi" class="mb-1.5 block text-xs font-semibold text-gray-700">Lokasi (opsional)</label>
                <input id="lokasi" name="lokasi" maxlength="255" value="{{ old('lokasi') }}" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20">
            </div>
            <div>
                <label for="deskripsi" class="mb-1.5 block text-xs font-semibold text-gray-700">Deskripsi (opsional)</label>
                <textarea id="deskripsi" name="deskripsi" rows="2" maxlength="2000" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20">{{ old('deskripsi') }}</textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModalTambahKegiatan()" class="rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700">Batal</button>
                <button type="submit" class="rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-xs font-bold text-white hover:bg-[#155757]">Simpan Kegiatan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalQrCode" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-sm rounded-3xl border border-gray-100 bg-white p-6 text-center shadow-2xl">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">QR Code Presensi</h3>
            <button type="button" onclick="closeQrModal()" class="text-xl text-gray-400" aria-label="Tutup">×</button>
        </div>
        <h4 id="qrKegiatanJudul" class="mb-1 text-sm font-bold text-gray-900"></h4>
        <p class="mb-4 text-xs text-gray-500">QR aktif selama sesi belum ditutup.</p>
        <canvas id="qrCanvas" class="mx-auto max-w-full rounded-xl border border-gray-100 p-2" aria-label="QR presensi aktif"></canvas>
        <p id="qrCodeText" class="mt-3 break-all font-mono text-[11px] text-gray-500"></p>
        <p id="qrError" class="mt-2 hidden text-xs text-red-600" role="alert"></p>
        <div class="mt-5 flex gap-3">
            <button type="button" onclick="downloadQr()" class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-bold text-gray-700">Unduh QR</button>
            <button type="button" onclick="closeQrModal()" class="flex-1 rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-xs font-bold text-white">Selesai</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModalTambahKegiatan() {
        document.getElementById('modalTambahKegiatan').classList.remove('hidden');
        document.getElementById('modalTambahKegiatan').classList.add('flex');
    }

    function closeModalTambahKegiatan() {
        document.getElementById('modalTambahKegiatan').classList.add('hidden');
        document.getElementById('modalTambahKegiatan').classList.remove('flex');
    }

    function openQrModal(title, code) {
        const modal = document.getElementById('modalQrCode');
        const canvas = document.getElementById('qrCanvas');
        const error = document.getElementById('qrError');
        document.getElementById('qrKegiatanJudul').textContent = title;
        document.getElementById('qrCodeText').textContent = code;
        error.classList.add('hidden');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (!window.QRCode) {
            error.textContent = 'Generator QR tidak tersedia. Muat ulang halaman dan coba kembali.';
            error.classList.remove('hidden');
            return;
        }

        window.QRCode.toCanvas(canvas, code, { width: 240, margin: 2, errorCorrectionLevel: 'H' })
            .catch(() => {
                error.textContent = 'QR Code gagal dibuat. Silakan coba kembali.';
                error.classList.remove('hidden');
            });
    }

    function closeQrModal() {
        const modal = document.getElementById('modalQrCode');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function downloadQr() {
        const canvas = document.getElementById('qrCanvas');
        if (!canvas.width) return;

        const link = document.createElement('a');
        link.download = 'qr-presensi.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    function filterKategori(category) {
        let visibleCount = 0;
        document.querySelectorAll('.kegiatan-card').forEach(card => {
            const visible = category === 'all' || card.dataset.category === category;
            card.classList.toggle('hidden', !visible);
            visibleCount += visible ? 1 : 0;
        });

        document.querySelectorAll('.keg-filter').forEach(button => {
            const selected = button.dataset.filter === category;
            button.classList.toggle('bg-[#1A6B6B]', selected);
            button.classList.toggle('text-white', selected);
            button.classList.toggle('text-gray-600', !selected);
        });
        document.getElementById('kegiatanEmpty').classList.toggle('hidden', visibleCount > 0);
    }

    const showQrFor = @json(session('show_qr_for'));
    if (showQrFor) {
        window.addEventListener('load', () => {
            const qrButton = document.querySelector(`[data-kegiatan-id="${showQrFor}"]`);
            if (qrButton) {
                openQrModal(qrButton.dataset.qrTitle, qrButton.dataset.qrCode);
            }
        });
    }

    @if($errors->has('jam_mulai') || $errors->has('jam_selesai'))
        window.addEventListener('load', openModalTambahKegiatan);
    @endif
</script>
@endpush
