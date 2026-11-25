# Modul 02: Dashboard Monitoring Multi-Role

## 1. Deskripsi Fitur
Modul Dashboard merupakan pusat kendali informasi (*command center*) yang disesuaikan secara dinamis berdasarkan peran pengguna yang sedang aktif. Dashboard menyajikan rangkuman metrik penting (*Key Performance Indicators / KPI*), visualisasi grafik tren, status kewajiban personal, jadwal mendatang, hingga peringatan kedisiplinan organisasi (*smart alert system*).

---

## 2. Acuan Tangkapan Layar (Screenshots)
- **Dashboard Anggota**: [`Rekapin-Resource/UI-Anggota/dashboard anggota.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/dashboard%20anggota.png)
- **Dashboard ADKEU**: [`Rekapin-Resource/UI-Bendahara/dashboard adkeu.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Bendahara/dashboard%20adkeu.png)
- **Dashboard PSDI**: [`Rekapin-Resource/UI-PSDI/dashboard psdi.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/dashboard%20psdi.png)
- **Dashboard PresBEM**: [`Rekapin-Resource/UI-PresBem/dashboard pres.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/dashboard%20pres.png)

---

## 3. Komparasi Antarmuka Dashboard Per Role

| Komponen / Fitur | Anggota Biasa | ADKEU (Bendahara) | PSDI | PresBEM (Ketua BEM) |
| :--- | :---: | :---: | :---: | :---: |
| **Kartu Metrik KPI** | Personal (Kas & Denda) | Finansial & Piket Global | Event & Presensi Kegiatan | Eksekutif BEM (Organisasi) |
| **Grafik Keuangan** | — | Line Chart (Kas Masuk/Keluar) | — | — |
| **Grafik Presensi** | — | Bar Chart (Piket Mingguan) | Bar Chart (Piket Mingguan) | — |
| **Donut Status Denda** | — | Donut Chart (Lunas vs Tunggakan) | — | — |
| **Tabel Ringkasan Departemen** | — | — | — | Break down 5 Departemen |
| **Smart Alert & Notifikasi** | — | — | — | Panel Notifikasi Perhatian |
| **Tabel Transaksi Terkini** | — | 5 Transaksi Terkini + Link | — | — |
| **Jadwal Piket Minggu Ini** | Card Piket Personal | — | — | — |
| **Agenda Kegiatan Terdekat** | List Kegiatan Terdekat | — | List Kegiatan + Status Hadir | — |
| **Tombol Aksi Cepat (*CTA*)** | — | — | Tambah Kegiatan & Rekap | Shortcut Rekap & Bayar Kas |

---

## 4. Rincian Antarmuka Tiap Peran

### 4.1. Dashboard Anggota Biasa
Tujuan: Memberikan transparansi status kewajiban anggota secara cepat tanpa distraksi.
1. **Banner Identitas Fungsionaris**:
   - Menampilkan inisial, Nama Anggota (*Budi Santoso*), NIM (`215150700111003`), dan Status (*Anggota Biasa · BEM FILKOM UB*).
   - Menampilkan 3 metrik cepat:
     - Bulan Berjalan (e.g. `Sep`)
     - Rasio Kehadiran (e.g. `87% Kehadiran`)
     - Status Iuran Kas (e.g. `Lunas`)
2. **Kartu Status Finansial Mandiri**:
   - **Status Kas**: `Rp50.000` (Keterangan: *Dibayar 05/09/2025*, Badge hijau `Lunas`).
   - **Denda Aktif**: `Rp0` (Keterangan: *Tidak ada denda*, Badge hijau `Lunas`).
3. **Panel Jadwal Piket Minggu Ini**:
   - Daftar jadwal piket yang ditugaskan dalam 7 hari ke depan.
   - Contoh:
     - `Senin, 22/09` (08:00 – 10:00 · Sekretariat) -> Badge status hijau `✓ Hadir`.
     - `Kamis, 25/09` (08:00 – 10:00 · Sekretariat) -> Badge status biru toska `Akan Datang`.
4. **Panel Kegiatan Mendatang**:
   - Menampilkan daftar agenda organisasi resmi yang akan dilaksanakan.
   - Contoh item: *Pelatihan Public Speaking* (28/09/2025 · Ruang Rapat BEM), *FGD Roadmap BEM 2026* (30/09/2025 · Sekretariat BEM).

---

### 4.2. Dashboard ADKEU (Administrasi Keuangan)
Tujuan: Memberikan gambaran analitik arus kas dan kepatuhan finansial serta piket organisasi.
1. **4 Kartu Metrik Utama (Top KPI Cards)**:
   - **Total Saldo Kas**: `Rp28.450.000` (+Rp6.300.000 bulan ini, tren naik hijau).
   - **Anggota Lunas Kas**: `24 / 32` (75% tingkat kepatuhan, tren naik hijau).
   - **Denda Belum Bayar**: `Rp250.000` (7 anggota menunggak, indikator perhatian merah).
   - **Kehadiran Piket**: `91%` (Minggu ini 30/32 hadir, tren naik hijau).
2. **Grafik Tren Kas Masuk & Keluar**:
   - Grafik garis ganda (*dual line chart*) periode Maret – September 2025.
   - Garis hijau: Total Kas Masuk per bulan.
   - Garis kuning: Total Kas Keluar operasional BEM.
3. **Diagram Status Denda (*Donut Chart*)**:
   - Rincian total 28 anggota:
     - Hijau: Lunas (18 orang)
     - Kuning: Belum Bayar (7 orang)
     - Merah: Menunggak (3 orang)
4. **Grafik Kehadiran Piket Harian**:
   - Bar chart vertikal membandingkan kehadiran vs ketidakhadiran minggu M1 s/d M4.
5. **Daftar Transaksi Terbaru**:
   - Menampilkan 5 transaksi mutasi atau pembayaran kas/denda terbaru.
   - Menampilkan nama fungsionaris, tipe transaksi, tanggal, nominal, dan badge status (`Lunas`, `Pending`, `Belum Bayar`).
   - Dilengkapi tautan navigasi *"Lihat semua"* menuju halaman mutasi kas.

---

### 4.3. Dashboard PSDI (Pengembangan Sumber Daya Internal)
Tujuan: Manajemen operasional keaktifan kegiatan internal organisasi dan pemantauan presensi.
1. **4 Kartu Metrik Operasional Event**:
   - **Total Kegiatan**: `8` (Tahun 2025).
   - **Kegiatan Aktif**: `2` (Sedang berlangsung).
   - **Rata-rata Kehadiran**: `85%` (Semua kegiatan terakumulasi).
   - **Peserta Terdaftar**: `156` (Total partisipasi akumulatif).
2. **Daftar Kegiatan Terbaru**:
   - Daftar status event organisasi terkini:
     - *Seminar Kepemimpinan* (20/09/2025 · 30 peserta terdaftar | 28/30 hadir).
     - *Workshop UI/UX Design* (15/09/2025 · 25 peserta terdaftar | 22/25 hadir).
     - *Pelatihan Public Speaking* (28/09/2025 · 20 peserta terdaftar | Badge `Upcoming`).
     - *FGD Roadmap BEM 2026* (30/09/2025 · 32 peserta terdaftar | Badge `Upcoming`).
3. **Aksi Cepat (Quick Action CTA Cards)**:
   - Tombol blok Teal: *"Buat Kegiatan Baru"* (Tambah agenda & generate QR presensi instan).
   - Kartu Putih: *"Rekap Presensi"* (Lihat semua data kehadiran dan ekspor).

---

### 4.4. Dashboard PresBEM (Ketua BEM / Eksekutif)
Tujuan: Pengawasan helikopter (*helicopter view*) kesehatan tata kelola organisasi lintas departemen.
1. **4 Kartu Metrik Eksekutif**:
   - **Saldo Kas BEM**: `Rp28.450.000` (Posisi saldo kas per September 2025).
   - **Anggota Lunas Kas**: `24 / 32` (Kepatuhan kas 75%).
   - **Denda Beredar**: `Rp250.000` (Total denda belum tertagih pada 7 anggota).
   - **Kehadiran Rata-rata**: `88%` (Rata-rata gabungan piket & kegiatan).
2. **Pintasan Cepat (*Quick Navigation Cards*)**:
   - *Rekap Kas*: Laporan keuangan lengkap organisasi.
   - *Rekap Presensi*: Data analitik kehadiran seluruh fungsionaris.
   - *Bayar Kas Saya*: Pemenuhan kewajiban iuran kas Ketua BEM secara pribadi.
   - *Denda Saya*: Pengecekan denda mandiri.
3. **Panel Notifikasi & Perhatian (*Organization Health Warning System*)**:
   - Kartu Peringatan Kuning: *"7 anggota belum bayar kas September (Hari ini)"*.
   - Kartu Peringatan Merah: *"4 anggota di bawah 75% kehadiran piket (Kemarin)"*.
   - Kartu Peringatan Hijau: *"Kegiatan Seminar berhasil dilaksanakan, 28/30 hadir (21/09/2025)"*.
   - Kartu Peringatan Kuning: *"3 anggota denda belum dibayar > 30 hari (20/09/2025)"*.
4. **Tabel Ringkasan Kinerja Per Departemen**:
   - Menampilkan evaluasi komparatif per departemen:

| Departemen | Jumlah Anggota | Lunas Kas | Persentase Kehadiran | Status Evaluasi |
| :--- | :---: | :---: | :---: | :--- |
| **ADKEU** | 5 | 5/5 | 94% | 🟢 Sangat Baik |
| **PSDI** | 6 | 4/6 | 88% | 🟢 Baik |
| **Kementerian A** | 7 | 5/7 | 82% | 🟡 Cukup |
| **Kementerian B** | 8 | 6/8 | 91% | 🟢 Sangat Baik |
| **Anggota Umum** | 6 | 4/6 | 75% | 🔴 Perlu Perhatian |

---

## 5. Sumber Data & Query Kalkulasi
1. **Saldo Kas Berjalan**: `Sum(kas_masuk) - Sum(kas_keluar)`.
2. **Tingkat Kepatuhan Kas**: `(Jumlah Anggota Lunas Bulan Ini / Total Anggota Aktif) * 100%`.
3. **Persentase Kehadiran Fungsionaris**: `(Total Kehadiran Valid / Total Slot Piket + Kegiatan Ditugaskan) * 100%`.
4. **Threshold Peringatan**: Sistem memicu *alert* otomatis jika kehadiran fungsionaris turun di bawah `< 75%` atau denda menunggak `> 30 hari`.
