# Panduan Peran: ADKEU (Administrasi Keuangan / Bendahara)

## 1. Profil Peran
- **Nama Peran**: ADKEU (Administrasi Keuangan)
- **Sasaran Pengguna**: Bendahara Umum dan staf keuangan BEM FILKOM UB.
- **Karakteristik Akses**: Berorientasi pada tata kelola buku kas organisasi, penagihan iuran, pemeriksaan validitas bukti transfer, penetapan denda, serta pengaturan operasional jadwal piket harian.

---

## 2. Struktur Navigasi Bilah Samping (Sidebar Menu)

```
├── Dashboard
├── KAS
│   ├── Kas (Pembukuan Kas Masuk & Keluar)
│   ├── Validasi Pembayaran
│   ├── Rekap Kas
│   └── Denda
├── PRESENSI
│   ├── Jadwal Piket
│   ├── Presensi Piket
│   └── Rekap Presensi
└── Profile
```

---

## 3. Daftar Fitur & Spesifikasi Antarmuka

### 3.1. Dashboard ADKEU
- **Screenshot**: [`Rekapin-Resource/UI-Bendahara/dashboard adkeu.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/dashboard%20adkeu.png)
- **Fungsi**: Rangkuman analitik arus kas dan kepatuhan finansial organisasi.
- **Fitur Utama**:
  - 4 Kartu KPI: Saldo Kas Total (`Rp28.450.000`), Rasio Anggota Lunas (`24/32`), Denda Belum Bayar (`Rp250.000`), dan Kehadiran Piket Mingguan (`91%`).
  - Grafik Garis Tren Kas Masuk & Keluar (periode Mar - Sep 2025).
  - Diagram Donut Status Denda (Lunas, Belum Bayar, Menunggak).
  - Grafik Batang Kehadiran Piket Mingguan (M1 - M4).
  - Widget Transaksi Kas/Denda Terbaru beserta statusnya (`Lunas`, `Pending`, `Belum Bayar`).

### 3.2. Pencatatan Kas Masuk & Keluar
- **Screenshot Kas**: [`Rekapin-Resource/UI-Bendahara/kas adkeu.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/kas%20adkeu.png)
- **Screenshot Modal Kas Masuk**: [`Rekapin-Resource/UI-Bendahara/kas masuk.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/kas%20masuk.png)
- **Screenshot Modal Kas Keluar**: [`Rekapin-Resource/UI-Bendahara/kas keluar.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/kas%20keluar.png)
- **Fungsi**: Buku kas umum (*general ledger*) pencatatan mutasi kas BEM.
- **Fitur Utama**:
  - Saldo saat ini, akumulasi pemasukan, dan total pengeluaran.
  - Tombol `[+ Tambah Transaksi]` dengan form modal:
    - Toggle `Kas Masuk` (hijau) / `Kas Keluar` (merah).
    - Tanggal, Keterangan, Nominal, dan Catatan Tambahan.
  - Filter transaksi: `Semua`, `Masuk`, `Keluar`.
  - Tabel mutasi lengkap dengan penanda nominal warna dinamis dan saldo berjalan.

### 3.3. Validasi Pembayaran Bukti Transfer
- **Screenshot**: [`Rekapin-Resource/UI-Bendahara/validasi pembayaran.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/validasi%20pembayaran.png)
- **Fungsi**: Memverifikasi keabsahan setoran kas bulanan dan denda fungsionaris.
- **Fitur Utama**:
  - Indikator peringatan antrean: e.g. `⚠️ 3 menunggu validasi`.
  - Filter status: `Semua`, `Pending`, `Disetujui`, `Ditolak`.
  - Kartu pengajuan verifikasi dengan identitas mahasiswa, jenis pembayaran, dan nominal.
  - Tombol `[Periksa]` untuk melihat struk bukti transfer dan memutuskan `Setujui` atau `Tolak`.

### 3.4. Rekap Kas & Matriks Iuran
- **Screenshot**: [`Rekapin-Resource/UI-Bendahara/rekap kas.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/rekap%20kas.png)
- **Fungsi**: Laporan agregasi keuangan bulanan dan matriks kelunasan kas fungsionaris.
- **Fitur Utama**:
  - Total pemasukan, pengeluaran, dan saldo akhir.
  - Tombol `[📥 Export PDF]` laporan keuangan resmi.
  - Grafik komparasi bulanan kas masuk vs kas keluar.
  - Tabel matriks pembayaran kas per anggota (Maret s/d September) dengan tanda centang `✓` atau strip `—`.

### 3.5. Manajemen Denda Anggota
- **Screenshot**: [`Rekapin-Resource/UI-Bendahara/denda.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/denda.png)
- **Fungsi**: Menerbitkan dan mengawasi sanksi denda ketidakhadiran fungsionaris.
- **Fitur Utama**:
  - Ringkasan denda belum bayar (`Rp125.000`) dan denda lunas (`Rp75.000`).
  - Tombol `[+ Tambah Denda]` untuk penerbitan manual.
  - Filter status denda.
  - Tabel denda anggota dengan rincian nama, jenis sanksi, alasan, nominal, status, dan aksi `[Detail]`.

### 3.6. Penjadwalan & Presensi Piket
- **Screenshot Jadwal**: [`Rekapin-Resource/UI-Bendahara/jadwal piket.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/jadwal%20piket.png)
- **Screenshot Presensi**: [`Rekapin-Resource/UI-Bendahara/presensi piket.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/presensi%20piket.png)
- **Screenshot Rekap**: [`Rekapin-Resource/UI-Bendahara/rekap presensi.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/rekap%20presensi.png)
- **Fungsi**: Pengelolaan jadwal tugas sekretariat dan verifikasi log kehadiran piket.
- **Fitur Utama**:
  - Membuat jadwal dengan memilih anggota, tanggal, jam shift, dan lokasi; bentrok jadwal anggota ditolak.
  - Pemantauan log kehadiran dan bukti foto yang dikirim anggota saat shift (`Hadir`, `Alpha`, atau `Terjadwal`).
  - Aksi `[Edit]` status presensi (e.g. konversi status Alpha menjadi Izin karena surat sakit).
  - Rekapitulasi persentase kehadiran per anggota dengan indikator warna status.

### 3.7. Profil ADKEU
- **Screenshot**: [`Rekapin-Resource/UI-Bendahara/profil adkeu.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/profil%20adkeu.png)
- **Fungsi**: Informasi akun bendahara, pengaturan notifikasi, dan tombol logout.
