@extends('layouts.anggota')

@section('title', 'Profil Fungsionaris')
@section('page-title', 'Profil Saya')

@section('content')
<div class="space-y-6">
    {{-- Header & Tombol Aksi --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-black tracking-tight text-gray-900">Profil Fungsionaris</h2>
            <p class="mt-0.5 text-xs text-gray-500">Kelola informasi data diri, jabatan organisasi, dan status kewajiban fungsionaris BEM FILKOM UB.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openEditModal()"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#155757]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Profil & Password
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

    {{-- Hero Card Profil --}}
    <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
        {{-- Banner dengan dekorasi lebih kaya --}}
        <div class="relative h-48 bg-gradient-to-br from-[#0D4F4F] via-[#1A6B6B] to-[#2A9090] overflow-hidden">
            {{-- Pattern dekoratif --}}
            <div class="absolute inset-0 opacity-[0.07]"
                 style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 20px 20px;"></div>
            {{-- Lingkaran dekoratif --}}
            <div class="absolute -top-10 -right-10 w-52 h-52 rounded-full bg-white/5 border border-white/10"></div>
            <div class="absolute -bottom-16 -left-8 w-48 h-48 rounded-full bg-white/5 border border-white/10"></div>
            <div class="absolute top-6 right-24 w-20 h-20 rounded-full bg-teal-400/10 border border-white/10"></div>
            {{-- Konten banner atas --}}
            <div class="relative h-full flex flex-col justify-between p-6">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-semibold text-white backdrop-blur-sm border border-white/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Fungsionaris Aktif Kabinet 2026
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-white/40">BEM FILKOM UB</span>
                    </div>
                </div>
                {{-- Stats ringkas di bagian bawah banner --}}
                <div class="flex items-end justify-between">
                    <div class="flex items-center gap-6">
                        <div class="text-white/80">
                            <p class="text-[10px] font-semibold uppercase tracking-wider opacity-60">Departemen</p>
                            <p class="text-sm font-bold mt-0.5">{{ auth()->user()->departemen ?: 'BEM FILKOM' }}</p>
                        </div>
                        <div class="w-px h-8 bg-white/20"></div>
                        <div class="text-white/80">
                            <p class="text-[10px] font-semibold uppercase tracking-wider opacity-60">Jabatan</p>
                            <p class="text-sm font-bold mt-0.5">{{ auth()->user()->jabatan ?: 'Fungsionaris' }}</p>
                        </div>
                        <div class="w-px h-8 bg-white/20"></div>
                        <div class="text-white/80">
                            <p class="text-[10px] font-semibold uppercase tracking-wider opacity-60">Angkatan</p>
                            <p class="text-sm font-bold mt-0.5">{{ auth()->user()->angkatan ?: '2024' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Identitas Utama dengan avatar overlap --}}
        <div class="relative px-6 pb-5 pt-0">
            <div class="-mt-10 flex flex-wrap items-end justify-between gap-4">
                <div class="flex items-end gap-4">
                    {{-- Avatar besar dengan ring --}}
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#1A8A8A] to-[#0D5F5F] border-4 border-white flex items-center justify-center text-white text-2xl font-black shadow-lg shrink-0 ring-2 ring-teal-200/50">
                        {{ auth()->user()->initials }}
                    </div>
                    <div class="mb-1">
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <h3 class="text-xl font-black text-gray-900 leading-tight">{{ auth()->user()->name }}</h3>
                            <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-[11px] font-bold text-[#1A6B6B] border border-teal-200">
                                {{ auth()->user()->role_badge }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 text-[11px] text-gray-400 bg-gray-50 border border-gray-200 rounded-full px-3 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        NIM: {{ auth()->user()->nim ?: '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid Konten 2 Kolom --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Kolom Kiri: Rincian Biodata & Organisasi --}}
        <div class="lg:col-span-8 space-y-6">
            {{-- Kartu Data Fungsionaris --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#1A6B6B] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Informasi Data Fungsionaris</h4>
                            <p class="text-[11px] text-gray-400">Data pribadi dan keanggotaan departemen yang tercatat di sistem</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-[#1A6B6B]">Terverifikasi</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Nama Lengkap</p>
                        <p class="mt-1 text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Nomor Induk Mahasiswa (NIM)</p>
                        <p class="mt-1 text-sm font-bold text-gray-900 font-mono">{{ auth()->user()->nim ?: 'Belum diisi' }}</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Kementerian / Kebiroan</p>
                        <p class="mt-1 text-sm font-bold text-gray-900">{{ auth()->user()->departemen ?: 'BEM FILKOM' }}</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Jabatan Struktural</p>
                        <p class="mt-1 text-sm font-bold text-gray-900">{{ auth()->user()->jabatan ?: 'Fungsionaris' }}</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Angkatan Mahasiswa</p>
                        <p class="mt-1 text-sm font-bold text-gray-900">{{ auth()->user()->angkatan ?: '2024' }}</p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Status Keaktifan</p>
                        <p class="mt-1 text-sm font-bold text-emerald-600 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Fungsionaris Aktif
                        </p>
                    </div>

                    <div class="rounded-2xl bg-gray-50/70 p-4 border border-gray-100/80 sm:col-span-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Alamat Email Resmi</p>
                        <p class="mt-1 text-sm font-bold text-gray-900 font-mono">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>

            {{-- Kartu Keamanan & Sandi --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-teal-50 text-[#1A6B6B] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Keamanan & Password Akun</h4>
                            <p class="text-[11px] text-gray-400">Kelola kata sandi akun untuk menjaga keamanan akses Anda</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                    <div>
                        <p class="text-xs font-bold text-gray-800">Password Akun</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Password dienkripsi dengan standar Bcrypt yang aman.</p>
                    </div>
                    <button type="button" onclick="openEditModal()"
                            class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-100 transition shrink-0">
                        Ubah Password
                    </button>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Ringkasan Status Finansial & Partisipasi --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Status Kas & Kewajiban --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#1A6B6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ringkasan Kas Fungsionaris
                </h4>

                <div class="space-y-3">
                    {{-- Kas Bulan Ini --}}
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 font-medium">Kas Bulan Ini:</span>
                            @switch($currentPayment?->status)
                                @case(\App\Models\CashPayment::STATUS_APPROVED)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Lunas</span>
                                    @break
                                @case(\App\Models\CashPayment::STATUS_PENDING)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Menunggu</span>
                                    @break
                                @case(\App\Models\CashPayment::STATUS_REJECTED)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">Ditolak</span>
                                    @break
                                @default
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600">Belum Dibayar</span>
                            @endswitch
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs">
                            <span class="text-[11px] text-gray-400">Tarif Pokok Rp20.000</span>
                            <a href="{{ route('anggota.bayar-kas') }}" class="font-bold text-[#1A6B6B] hover:underline">Bayar Kas →</a>
                        </div>
                    </div>

                    {{-- Denda Keterlambatan --}}
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 font-medium">Denda Keterlambatan:</span>
                            <span class="text-xs font-bold text-gray-500">{{ $activeLateFeeCount ?? 0 }} tagihan</span>
                        </div>
                        <p class="text-lg font-black {{ ($outstandingLateFees ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                            Rp{{ number_format($outstandingLateFees ?? 0, 0, ',', '.') }}
                        </p>
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-[11px] text-gray-400">{{ $activeLateFeeCount ?? 0 }} tagihan belum lunas</span>
                            <a href="{{ route('anggota.denda') }}" class="font-bold text-[#1A6B6B] hover:underline">Rincian Denda →</a>
                        </div>
                    </div>

                    {{-- Total Kas Terverifikasi --}}
                    <div class="p-4 rounded-2xl bg-teal-50/50 border border-teal-100">
                        <span class="text-xs text-teal-800 font-medium">Total Kas Disetujui:</span>
                        <p class="text-lg font-black text-[#1A6B6B] mt-1">
                            Rp{{ number_format($totalCashPaid ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-teal-600/80 mt-1">Telah diverifikasi bendahara ADKEU</p>
                    </div>
                </div>
            </div>

            {{-- Akses Cepat --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Tautan Navigasi Cepat</h4>
                <a href="{{ route('anggota.bayar-kas') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 hover:bg-teal-50/60 hover:text-[#1A6B6B] transition text-xs font-bold text-gray-700">
                    <span>Bayar Tagihan Kas</span>
                    <span>→</span>
                </a>
                <a href="{{ route('anggota.riwayat-pembayaran') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 hover:bg-teal-50/60 hover:text-[#1A6B6B] transition text-xs font-bold text-gray-700">
                    <span>Riwayat Pembayaran Kas</span>
                    <span>→</span>
                </a>
                <a href="{{ route('anggota.presensi-kegiatan') }}" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 hover:bg-teal-50/60 hover:text-[#1A6B6B] transition text-xs font-bold text-gray-700">
                    <span>Presensi Kegiatan Fungsionaris</span>
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
                <h3 class="text-base font-bold text-gray-900">Perbarui Profil Fungsionaris</h3>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui biodata atau ubah kata sandi akun Anda</p>
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
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Nama Lengkap *</label>
                <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required
                       class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm font-semibold text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="nim" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">NIM</label>
                    <input type="text" id="nim" name="nim" value="{{ old('nim', auth()->user()->nim) }}"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm font-mono text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                </div>
                <div>
                    <label for="angkatan" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Angkatan</label>
                    <input type="text" id="angkatan" name="angkatan" value="{{ old('angkatan', auth()->user()->angkatan) }}"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="departemen" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Kementerian / Biro</label>
                    <input type="text" id="departemen" name="departemen" value="{{ old('departemen', auth()->user()->departemen) }}"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                </div>
                <div>
                    <label for="jabatan" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', auth()->user()->jabatan) }}"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-900 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/20 transition">
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Ganti Password</p>
                <p class="text-[11px] text-gray-400 mb-3">Kosongkan kedua kolom di bawah jika tidak ingin mengubah kata sandi.</p>
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
