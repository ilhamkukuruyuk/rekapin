# Panduan Peran: PresBEM (Ketua BEM / Eksekutif)

## 1. Profil Peran
- **Nama Peran**: PresBEM (Presiden / Ketua BEM & Setingkatnya)
- **Sasaran Pengguna**: Ketua BEM, Wakil Ketua BEM, dan jajaran pimpinan inti eksekutif BEM FILKOM UB.
- **Karakteristik Akses**: Memiliki akses dwifungsi:
  1. **Tingkat Eksekutif (*Executive Level*)**: Pengawasan menyeluruh (*oversight*) terhadap seluruh departemen, kondisi saldo kas, penegakan denda, dan evaluasi kepatuhan presensi.
  2. **Tingkat Fungsionaris Pribadi (*Personal Accountability*)**: Memiliki kewajiban iuran kas rutin, jadwal piket sekretariat, presensi kegiatan, dan pelunasan denda mandiri sebagai fungsionaris organisasi.

---

## 2. Struktur Navigasi Bilah Samping (Sidebar Menu)

```
├── Dashboard (Eksekutif)
├── KAS SAYA
│   ├── Bayar Kas
│   └── Riwayat Pembayaran
├── PRESENSI
│   ├── Jadwal Piket
│   ├── Presensi Kegiatan (Scan QR)
│   ├── Riwayat Presensi
│   ├── Denda Saya
│   ├── Rekap Kas (Pengawasan Finansial)
│   └── Rekap Presensi (Pengawasan Fungsionaris)
└── Profile
```

---

## 3. Daftar Fitur & Spesifikasi Antarmuka

### 3.1. Dashboard Ketua BEM
- **Screenshot**: [`Rekapin-Resource/UI-PresBem/dashboard pres.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/dashboard%20pres.png)
- **Fungsi**: Pusat komando eksekutif pemantauan performa organisasi.
- **Fitur Utama**:
  - 4 Kartu KPI Eksekutif:
    - Saldo Kas BEM: `Rp28.450.000` (September 2025).
    - Anggota Lunas Kas: `24 / 32` (Tingkat kepatuhan iuran 75%).
    - Denda Beredar: `Rp250.000` (Sanksi aktif pada 7 fungsionaris).
    - Kehadiran Rata-rata: `88%` (Rata-rata gabungan piket & kegiatan).
  - 4 Kartu Pintasan Cepat:
    - *Rekap Kas*: Laporan keuangan organisasi.
    - *Rekap Presensi*: Laporan kehadiran anggota.
    - *Bayar Kas Saya*: Pemenuhan iuran kas pribadi Ketua BEM.
    - *Denda Saya*: Status tagihan sanksi pribadi.
  - Pusat Peringatan & Perhatian (*Smart Organizational Health Alerts*):
    - 🟡 Peringatan 7 anggota belum bayar kas September.
    - 🔴 Peringatan 4 anggota di bawah 75% kehadiran piket.
    - 🟢 Laporan kegiatan seminar selesai dengan 28/30 hadir.
    - 🟡 Peringatan 3 fungsionaris menunggak denda > 30 hari.
  - Tabel Ringkasan Kinerja Departemen:
    - Rekapitulasi jumlah anggota, rasio lunas kas, dan persentase kehadiran untuk ADKEU, PSDI, Kementerian A, Kementerian B, dan Anggota Umum.

### 3.2. Pengawasan Rekapitulasi Kas Organisasi
- **Screenshot**: [`Rekapin-Resource/UI-PresBem/rekap kas.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/rekap%20kas.png)
- **Fungsi**: Memeriksa transparansi pembukuan kas bendahara.
- **Fitur Utama**:
  - Saldo kas akhir, total pendapatan, dan realisasi belanja.
  - Tombol `[📥 Export PDF]` laporan keuangan.
  - Grafik tren pemasukan vs pengeluaran bulanan.
  - Matriks silang iuran kas per fungsionaris (Maret s/d September).

### 3.3. Pengawasan Rekapitulasi Presensi Organisasi
- **Screenshot**: [`Rekapin-Resource/UI-PresBem/rekap presensi.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/rekap%20presensi.png)
- **Fungsi**: Mengidentifikasi kedisiplinan fungsionaris BEM.
- **Fitur Utama**:
  - Metrik rata-rata kehadiran, total alpha, dan jumlah anggota bermasalah (< 75%).
  - Grafik mingguan kehadiran piket.
  - Tabel evaluasi kehadiran individual dengan bar persentase berwarna.

### 3.4. Kewajiban Finansial Mandiri (Bayar Kas & Denda Saya)
- **Screenshot Bayar Kas**: [`Rekapin-Resource/UI-PresBem/riwayat pembayaran.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/riwayat%20pembayaran.png)
- **Screenshot Denda**: [`Rekapin-Resource/UI-PresBem/denda pres.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/denda%20pres.png)
- **Fungsi**: Memberi teladan kedisiplinan organisasi dengan memenuhi kewajiban kas bulanan dan denda pribadi.
- **Fitur Utama**:
  - Antarmuka progres pelunasan kas bulanan dan tombol bayar.
  - Antarmuka riwayat denda pribadi dan tombol pelunasan.

### 3.5. Kewajiban Kehadiran Mandiri (Piket & Presensi QR)
- **Screenshot Jadwal Piket**: [`Rekapin-Resource/UI-PresBem/jadwal piket pres.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/jadwal%20piket%20pres.png)
- **Screenshot Scan QR**: [`Rekapin-Resource/UI-PresBem/qr presensi.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/qr%20presensi.png)
- **Screenshot Riwayat Scan**: [`Rekapin-Resource/UI-PresBem/riwayat scan.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/riwayat%20scan.png)
- **Screenshot Riwayat Presensi**: [`Rekapin-Resource/UI-PresBem/riwayat presensi pres.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/riwayat%20presensi%20pres.png)
- **Fungsi**: Pencatatan tugas piket pimpinan di sekretariat dan absensi kegiatan.
- **Fitur Utama**:
  - Unggah bukti foto piket sekretariat.
  - Scanner kamera QR Code saat menghadiri kegiatan resmi BEM.
  - Log riwayat kehadiran pribadi.

### 3.6. Profil PresBEM
- **Screenshot**: [`Rekapin-Resource/UI-PresBem/profil pres.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/profil%20pres.png)
- **Fungsi**: Informasi akun pimpinan eksekutif, rekapitulasi kepatuhan mandiri, dan menu keamanan akun.
