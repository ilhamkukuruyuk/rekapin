@extends('layouts.psdi')

@section('title', 'Profil Pengembangan Sumber Daya Insani (PSDI)')
@section('page-title', 'Profil PSDI')

@section('content')
<div class="space-y-6">
    {{-- Header & Tombol Aksi --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-black tracking-tight text-gray-900">Profil Pengembangan Sumber Daya Insani (PSDI)</h2>
            <p class="mt-0.5 text-xs text-gray-500">Akun institusional resmi Biro Pengembangan Sumber Daya Insani BEM FILKOM Universitas Brawijaya.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openEditModal()"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#155757]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Perbarui Akun Departemen
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-medium text-emerald-800 flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs text-red-800 shadow-xs">
            <p class="font-bold mb-1">Terjadi kesalahan pada data profil:</p>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Hero Card Profil Institusional PSDI (Kompak & Rapi) --}}
    <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-xs">
        {{-- Banner Background --}}
        <div class="relative h-20 sm:h-24 bg-gradient-to-r from-[#1A6B6B] via-[#207B7B] to-[#124B4B] p-4 flex items-start justify-between">
            <div class="absolute inset-0 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] opacity-10"></div>
            <span class="relative inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-0.5 text-[10px] font-semibold text-white backdrop-blur-xs border border-white/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Akun Departemen Universal BEM FILKOM UB
            </span>
            <span class="relative text-[11px] font-bold uppercase tracking-widest text-white/50 hidden sm:inline">BIRO PSDI</span>
        </div>

        {{-- Identitas Lembaga --}}
        <div class="relative px-6 pb-5 pt-10 sm:pt-2.5">
            {{-- Logo / Inisial Avatar (Absolute Overlapping) --}}
            <div class="absolute -top-7 left-6 w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#1A6B6B] border-4 border-white flex items-center justify-center text-white text-xl sm:text-2xl font-black shadow-lg">
                PS
            </div>

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pt-1 sm:pt-0 sm:pl-24">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-lg sm:text-xl font-black text-gray-900 leading-tight">
                            {{ auth()->user()->name ?: 'Pengembangan Sumber Daya Insani (PSDI)' }}
                        </h3>
                        <span class="rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-bold text-[#1A6B6B] border border-teal-200">
                            Akun Universal Departemen
                        </span>
                    </div>
                    <p class="text-[11px] font-semibold text-gray-500 mt-0.5">
                        Biro Pengembangan Sumber Daya Insani · Pengawasan Presensi & Keaktifan Fungsionaris
                    </p>
                </div>

                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-2xl border border-gray-100 self-start sm:self-auto">
                    <span class="text-[10px] text-gray-400 font-medium">Email Departemen:</span>
                    <span class="text-xs font-bold text-gray-700 font-mono">{{ auth()->user()->email }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid Konten 2 Kolom --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Kolom Kiri: Rincian Kelembagaan & Hak Akses --}}
        <div class="lg:col-span-8 space-y-6">
            {{-- Data Kelembagaan Biro --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#1A6B6B] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Informasi Lembaga Departemen</h4>
                            <p class="text-[11px] text-gray-400">Parameter akun operasional Biro Pengembangan Sumber Daya Insani (PSDI)</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-[#1A6B6B]">Institusional</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Nama Instansi Akun</p>
                        <p class="mt-1 text-sm font-bold text-gray-900">{{ auth()->user()->name ?: 'Pengembangan Sumber Daya Insani (PSDI)' }}</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Tipe / Sifat Akun</p>
                        <p class="mt-1 text-sm font-bold text-teal-700 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                            Universal Departemental
                        </p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Kebiroan Induk</p>
                        <p class="mt-1 text-sm font-bold text-gray-900">Biro Pengembangan Sumber Daya Insani (PSDI)</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Kode Unit / NIM Lembaga</p>
                        <p class="mt-1 text-sm font-bold text-gray-900 font-mono">{{ auth()->user()->nim ?: 'BEM-PSDI' }}</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Periode Kepengurusan</p>
                        <p class="mt-1 text-sm font-bold text-gray-900">Kabinet 2026</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Status Otorisasi</p>
                        <p class="mt-1 text-sm font-bold text-emerald-600 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Akses Penuh Presensi & Perizinan
                        </p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80 sm:col-span-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Alamat Email Resmi Departemen</p>
                        <p class="mt-1 text-sm font-bold text-gray-900 font-mono">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>

            {{-- Kartu Keamanan Akun Departemen --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#1A6B6B] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Kredensial & Kata Sandi Akun Departemen</h4>
                            <p class="text-[11px] text-gray-400">Gunakan kata sandi departemen yang hanya dibagikan ke staf authorized PSDI</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                    <div>
                        <p class="text-xs font-bold text-gray-800">Password Akun Departemen</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Disarankan memperbarui kata sandi secara berkala antar pergantian kepengurusan.</p>
                    </div>
                    <button type="button" onclick="openEditModal()"
                            class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-100 transition shrink-0">
                        Ubah Password
                    </button>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Otoritas & Tanggung Jawab Operasional PSDI --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Cakupan Tugas PSDI --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#1A6B6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Fungsi & Kewenangan PSDI
                </h4>

                <div class="space-y-3">
                    <div class="p-3.5 rounded-2xl bg-teal-50/60 border border-teal-100">
                        <p class="text-xs font-bold text-teal-900">Manajemen Kegiatan & QR Presensi</p>
                        <p class="text-[11px] text-teal-700 mt-0.5">Membuka sesi kegiatan kabinet dan mengelola QR Code dinamis untuk kehadiran fungsionaris.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                        <p class="text-xs font-bold text-gray-900">Review Izin & Dispensasi</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Menilai bukti perizinan fungsionaris yang berhalangan hadir pada kegiatan BEM.</p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                        <p class="text-xs font-bold text-gray-900">Rekapitulasi Kehadiran Organisasi</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Menyusun statistik tingkat kehadiran fungsionaris per kementerian/kebiroan.</p>
                    </div>
                </div>
            </div>

            {{-- Akses Cepat Fitur PSDI --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Tautan Cepat Operasional</h4>
                <a href="{{ route('psdi.kegiatan') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 hover:bg-teal-50/60 hover:text-[#1A6B6B] transition text-xs font-bold text-gray-700">
                    <span>Kelola Kegiatan & QR Presensi</span>
                    <span>→</span>
                </a>
                <a href="{{ route('psdi.perizinan') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 hover:bg-teal-50/60 hover:text-[#1A6B6B] transition text-xs font-bold text-gray-700">
                    <span>Validasi Perizinan Fungsionaris</span>
                    <span>→</span>
                </a>
                <a href="{{ route('psdi.rekap-presensi') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 hover:bg-teal-50/60 hover:text-[#1A6B6B] transition text-xs font-bold text-gray-700">
                    <span>Rekap Presensi Kabinet</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Modal Dialog Edit Profil & Password --}}
<div id="editProfileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 hidden">
    <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
            <div>
                <h3 class="text-base font-bold text-gray-900">Perbarui Profil Institusional PSDI</h3>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui label departemen atau ubah kata sandi akun</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Nama Instansi Departemen *</label>
                <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name ?: 'Pengembangan Sumber Daya Insani (PSDI)') }}" required
                       class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm font-semibold text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="nim" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Kode Unit / NIM</label>
                    <input type="text" id="nim" name="nim" value="{{ old('nim', auth()->user()->nim ?: 'BEM-PSDI') }}"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm font-mono text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                </div>
                <div>
                    <label for="angkatan" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Angkatan</label>
                    <input type="text" id="angkatan" name="angkatan" value="{{ old('angkatan', auth()->user()->angkatan ?: '2024') }}"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="departemen" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Kebiroan</label>
                    <input type="text" id="departemen" name="departemen" value="{{ old('departemen', auth()->user()->departemen ?: 'PSDI') }}"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                </div>
                <div>
                    <label for="jabatan" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Jabatan Lembaga</label>
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', auth()->user()->jabatan ?: 'Biro Pengembangan Sumber Daya Insani') }}"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Ganti Password Departemen</p>
                <p class="text-[11px] text-gray-400 mb-3">Kosongkan kolom di bawah jika tidak ingin mengubah kata sandi akun.</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <input type="password" name="password" placeholder="Password Baru"
                               class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                    </div>
                    <div>
                        <input type="password" name="password_confirmation" placeholder="Ulangi Password"
                               class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="rounded-xl px-4 py-2.5 text-xs font-bold text-gray-600 hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-[#1A6B6B] px-5 py-2.5 text-xs font-bold text-white hover:bg-[#155757] transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal() {
        document.getElementById('editProfileModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editProfileModal').classList.add('hidden');
    }
</script>
@endpush