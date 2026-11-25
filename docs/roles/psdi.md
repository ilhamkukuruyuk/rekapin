# Panduan Peran: PSDI (Pengembangan Sumber Daya Internal)

## 1. Profil Peran
- **Nama Peran**: PSDI (Pengembangan Sumber Daya Internal)
- **Sasaran Pengguna**: Kepala Departemen dan staf fungsionaris PSDI BEM FILKOM UB.
- **Karakteristik Akses**: Berorientasi pada manajemen kegiatan organisasi, pembentukan kode QR presensi kegiatan, evaluasi partisipasi fungsionaris, dan pemantauan rekapitulasi kehadiran.

---

## 2. Struktur Navigasi Bilah Samping (Sidebar Menu)

```
├── Dashboard
├── PRESENSI
│   ├── Kegiatan (Manajemen Acara & QR Code)
│   └── Rekap Presensi (Piket & Kegiatan)
└── Profile
```

---

## 3. Daftar Fitur & Spesifikasi Antarmuka

### 3.1. Dashboard PSDI
- **Screenshot**: [`Rekapin-Resource/UI-PSDI/dashboard psdi.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/dashboard%20psdi.png)
- **Fungsi**: Rangkuman operasional penyelenggaraan kegiatan dan rasio partisipasi fungsionaris.
- **Fitur Utama**:
  - 4 Kartu KPI: Total Kegiatan Tahun 2025 (`8`), Kegiatan Aktif Sedang Berlangsung (`2`), Rata-rata Kehadiran Seluruh Kegiatan (`85%`), dan Peserta Terdaftar Akumulatif (`156`).
  - Panel Kegiatan Terbaru: Menampilkan status kehadiran acara selesai (e.g. *Seminar Kepemimpinan 28/30 hadir*, *Workshop UI/UX 22/25 hadir*) dan acara berstatus `Upcoming`.
  - Kartu Aksi Cepat (*CTA Cards*):
    - Blok Hijau Toska: `Buat Kegiatan Baru` (Tambah agenda & generate QR presensi).
    - Kartu Putih: `Rekap Presensi` (Akses langsung ke data kehadiran organisasi).

### 3.2. Manajemen Kegiatan & Generator QR Presensi
- **Screenshot Katalog Kegiatan**: [`Rekapin-Resource/UI-PSDI/kegiatan semua.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/kegiatan%20semua.png)
- **Screenshot Filter Berlangsung**: [`Rekapin-Resource/UI-PSDI/kegiatan berlangsung.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/kegiatan%20berlangsung.png)
- **Screenshot Filter Akan Datang**: [`Rekapin-Resource/UI-PSDI/kegiatan akan datang.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/kegiatan%20akan%20datang.png)
- **Screenshot Filter Selesai**: [`Rekapin-Resource/UI-PSDI/kegiatan selesai.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/kegiatan%20selesai.png)
- **Fungsi**: Publikasi agenda acara dan penerbitan kode QR presensi kegiatan.
- **Fitur Utama**:
  - Tombol `[+ Tambah Kegiatan]` untuk menerbitkan agenda acara baru.
  - Filter status kegiatan: `Semua`, `Akan Datang`, `Berlangsung`, `Selesai`.
  - Kartu grid informasi kegiatan:
    - Judul acara, deskripsi singkat sasaran, tanggal, lokasi ruangan/aula, dan jumlah kuota/kehadiran.
    - Tombol `[Detail]` untuk daftar hadir partisipan.
    - Tombol `[Generate QR]` untuk memproduksi kode QR dinamis bagi acara mendatang.
    - Tombol `[Lihat QR]` untuk memproyeksikan kode QR ke layar proyektor saat acara berlangsung.

### 3.3. Rekapitulasi Presensi Organisasi (Piket & Kegiatan)
- **Screenshot Rekap Tab Piket**: [`Rekapin-Resource/UI-PSDI/rekap piket.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/rekap%20piket.png)
- **Screenshot Rekap Tab Kegiatan**: [`Rekapin-Resource/UI-PSDI/rerkap kegiatan.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/rerkap%20kegiatan.png)
- **Fungsi**: Memantau tingkat keaktifan fungsionaris pada tugas piket maupun kegiatan resmi organisasi.
- **Fitur Utama**:
  - Tab navigasi: `Piket` | `Kegiatan`.
  - Ringkasan KPI: Rata-rata Kehadiran (`88%`), Total Alpha (`18`), dan Jumlah Anggota di bawah 75% (`4 orang`).
  - Grafik Batang Kehadiran Mingguan (M1 s/d M4).
  - Tabel evaluasi kehadiran per fungsionaris dengan progress bar persentase kehadiran berwarna indikatif.

### 3.4. Profil PSDI
- **Screenshot**: [`Rekapin-Resource/UI-PSDI/profil psdi.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/profil%20psdi.png)
- **Fungsi**: Informasi akun pejabat PSDI, status kewajiban pribadi, dan pengaturan keamanan.
