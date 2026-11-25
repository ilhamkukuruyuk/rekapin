# Panduan Peran: Anggota Biasa (Fungsionaris)

## 1. Profil Peran
- **Nama Peran**: Anggota Biasa
- **Sasaran Pengguna**: Seluruh anggota fungsionaris aktif BEM FILKOM Universitas Brawijaya.
- **Karakteristik Akses**: Berorientasi pada pemenuhan kewajiban mandiri (*self-service*), transparansi iuran, serta presensi kehadiran.

---

## 2. Struktur Navigasi Bilah Samping (Sidebar Menu)

```
├── Dashboard
├── KAS SAYA
│   ├── Bayar Kas
│   └── Riwayat Pembayaran
├── PRESENSI
│   ├── Jadwal Piket
│   ├── Presensi Kegiatan (Scan QR)
│   ├── Riwayat Presensi
│   └── Denda Saya
└── Profile
```

---

## 3. Daftar Fitur & Spesifikasi Antarmuka

### 3.1. Dashboard Anggota
- **Screenshot**: [`Rekapin-Resource/UI-Anggota/dashboard anggota.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/dashboard%20anggota.png)
- **Fungsi**: Pusat informasi status individu fungsionaris secara ringkas.
- **Fitur Utama**:
  - Banner Profil: Nama, NIM, Departemen, Bulan aktif, persentase kehadiran, dan status kas.
  - Kartu Status Kas: Menampilkan nominal kas bulan berjalan dan status pelunasan (`Lunas` / `Belum Lunas`).
  - Kartu Denda Aktif: Menampilkan total denda berjalan yang belum dibayar.
  - Panel Jadwal Piket Minggu Ini: Shift piket terdekat dan status kesiapan.
  - Panel Kegiatan Mendatang: Agenda rapat, workshop, dan pelatihan yang wajib dihadiri.

### 3.2. Bayar Kas Saya & Riwayat Pembayaran
- **Screenshot**: [`Rekapin-Resource/UI-Anggota/bayar kas.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/bayar%20kas.png)
- **Fungsi**: Membayar iuran bulanan dan memeriksa histori pembayaran.
- **Fitur Utama**:
  - Baris progres kelunasan kas (e.g. `7/8 Bulan Lunas - 88%`).
  - Kartu tagihan kas belum dibayar: Nominal Rp50.000 dengan tombol `[Bayar]`.
  - Formulir unggah bukti transfer pembayaran.
  - Riwayat pembayaran kas bulanan sebelumnya berstatus `Lunas`.

### 3.3. Denda Saya
- **Screenshot**: [`Rekapin-Resource/UI-Anggota/denda anggota.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/denda%20anggota.png)
- **Fungsi**: Memeriksa tagihan denda keterlambatan piket atau alpha kegiatan.
- **Fitur Utama**:
  - Banner peringatan merah: Total denda belum dibayar dan jumlah denda aktif.
  - Daftar denda aktif dengan rincian tanggal, pelanggaran, nominal sanksi, dan tombol `[Bayar]`.
  - Riwayat denda yang telah diselesaikan (*Lunas*).

### 3.4. Jadwal Piket Saya & Unggah Bukti
- **Screenshot**: [`Rekapin-Resource/UI-Anggota/jadwal piket anggota.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/jadwal%20piket%20anggota.png)
- **Fungsi**: Melihat giliran tugas piket sekretariat serta melakukan presensi mandiri.
- **Fitur Utama**:
  - Counter kehadiran piket pribadi: Jumlah Hadir, Alpha, dan rasio Kehadiran (%).
  - Daftar giliran piket dengan jam shift dan lokasi penugasan.
  - Tombol `[Presensi Sekarang]` untuk melampirkan foto selama jam shift.
  - Tautan berkas bukti foto yang sudah diunggah.

### 3.5. Presensi Kegiatan (Scan QR & Riwayat)
- **Screenshot Scan QR**: [`Rekapin-Resource/UI-Anggota/scan qr anggota.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/scan%20qr%20anggota.png)
- **Screenshot Riwayat Presensi Kegiatan**: [`Rekapin-Resource/UI-Anggota/riwayat presensi anggota-1.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/riwayat%20presensi%20anggota-1.png)
- **Fungsi**: Melakukan absensi kegiatan BEM menggunakan kamera perangkat.
- **Fitur Utama**:
  - Tab pemindai kamera dengan kotak bidik (*viewfinder frame*).
  - Tombol `[Mulai Scan]` untuk membuka izin akses kamera.
  - Tab riwayat presensi kegiatan untuk meninjau log absensi via QR Code atau manual.

### 3.6. Riwayat Presensi Lengkap
- **Screenshot**: [`Rekapin-Resource/UI-Anggota/riwayat presensi anggota.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/riwayat%20presensi%20anggota.png)
- **Fungsi**: Rekapan log kehadiran piket dan kegiatan secara komprehensif.
- **Fitur Utama**:
  - Filter tab: `Piket` | `Kegiatan`.
  - Tabel kehadiran: Tanggal, Shift, Lokasi, dan Status Kehadiran (`Hadir` / `Alpha`).

### 3.7. Profil Pengguna
- **Screenshot**: [`Rekapin-Resource/UI-Anggota/profil anggota.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/profil%20anggota.png)
- **Fungsi**: Meninjau data diri, status kewajiban, dan opsi keamanan akun.
- **Fitur Utama**:
  - Informasi NIM, Departemen, Angkatan, dan Email resmi.
  - Kartu Ringkasan Kewajiban (Status kas bulan ini, Denda aktif, % Kehadiran piket, Total kas dibayar).
  - Opsi Edit Profil dan Ubah Password.
