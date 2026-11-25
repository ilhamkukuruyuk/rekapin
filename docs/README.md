# Rekapin - Sistem Informasi Kas & Presensi BEM FILKOM UB
> Dokumentasi Analisis Fitur & Spesifikasi Kebutuhan Sistem berdasarkan Rancangan Antarmuka UI (*High-Fidelity Wireframes*).

---

## 📌 Ringkasan Proyek
**Rekapin** adalah aplikasi manajemen organisasi terpadu berbasis web yang dikembangkan khusus untuk **BEM FILKOM Universitas Brawijaya**. Sistem ini mendigitalisasi dua pilar operasional vital organisasi:
1. **Tata Kelola Keuangan (Kas & Denda)**: Pencatatan kas masuk/keluar, pembayaran kas bulanan anggota, verifikasi/validasi bukti transfer, manajemen denda piket/kegiatan, serta rekapitulasi berkala dan pelaporan PDF.
2. **Tata Kelola Presensi & Keaktifan**: Penjadwalan piket sekretariat, presensi mandiri dengan unggah bukti foto, absensi kegiatan menggunakan pemindaian QR code dinamis, pelacakan ketidakhadiran (alpha/izin), serta rekapitulasi kehadiran per departemen dan eksekutif.

---

## 👥 Matriks Peran Pengguna (User Roles)

Sistem membedakan akses dan wewenang ke dalam 5 (lima) peran:

| Peran (Role) | Representasi Pengguna | Deskripsi Tanggung Jawab Utama |
| :--- | :--- | :--- |
| **Anggota Biasa** | Mahasiswa / Fungsionaris BEM | Membayar kas rutin, melihat denda pribadi, melihat jadwal & mengunggah bukti piket, melakukan *scan QR* kegiatan, memantau riwayat presensi & denda. |
| **ADKEU** (Administrasi Keuangan / Bendahara) | Bendahara Umum BEM | Mengelola buku kas umum (masuk & keluar), memverifikasi bukti pembayaran kas/denda, mengelola denda, menyusun jadwal piket harian, memvalidasi & mengedit presensi piket, mengunduh laporan PDF. |
| **PSDI** (Pengembangan Sumber Daya Internal) | Pengurus PSDI | Mengelola seluruh agenda/kegiatan BEM, membuat *event* baru, menghasilkan (*generate*) QR Code presensi kegiatan, memonitor tingkat partisipasi dan kehadiran anggota. |
| **PresBEM** (Ketua BEM / Eksekutif) | Presiden BEM & Wakil | Memiliki pandangan menyeluruh (*executive view*) terhadap kesehatan kas, rasio kepatuhan iuran tiap departemen, tingkat kehadiran fungsionaris, dan alert kedisiplinan organisasi, sekaligus memiliki akun personal untuk kewajiban iuran/piket sendiri. |
| **Admin** | Administrator sistem | Hanya dapat membuat akun anggota, ADKEU, PresBEM, atau PSDI; tidak dapat mengakses dashboard atau fitur operasional aplikasi. |

---

## 📂 Struktur Dokumentasi Fitur

Dokumentasi ini disusun secara komprehensif berdasarkan penelusuran tangkapan layar antarmuka dari folder [`Rekapin-Resource/`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource):

### Dokumen Spesifikasi Berdasarkan Fitur
1. [**01. Autentikasi & Manajemen Akun**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/01-autentikasi-dan-manajemen-akun.md)
   * Login multi-peran, autofill akun demo, navigasi sidebar responsif, dan profil pengguna.
2. [**02. Dashboard Monitoring Multi-Role**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/02-dashboard-monitoring.md)
   * Analisis metrik, ringkasan KPI, visualisasi data grafik, dan kartu ringkasan untuk tiap role.
3. [**03. Manajemen Kas & Keuangan**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/03-manajemen-kas.md)
   * Pencatatan kas masuk & keluar, modal transaksi, pembayaran kas anggota, alur validasi bukti bayar, matriks pembayaran kas bulanan, dan ekspor PDF.
4. [**04. Manajemen Denda Organisasi**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/04-manajemen-denda.md)
   * Aturan denda piket & kegiatan alpha, pencatatan otomatis/manual, antarmuka bayar denda, dan monitoring penunggak denda.
5. [**05. Penjadwalan & Presensi Piket Sekretariat**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/05-jadwal-dan-presensi-piket.md)
   * Plotting jadwal harian Senin–Jumat, unggah bukti piket foto oleh anggota, edit status presensi oleh ADKEU, dan rasio kehadiran.
6. [**06. Manajemen Kegiatan & Presensi QR Code**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/06-manajemen-kegiatan-dan-presensi-qr.md)
   * Pembuatan agenda kegiatan PSDI, filter status (*Upcoming, Ongoing, Finished*), pembuatan QR Code presensi, antarmuka scanner kamera anggota, dan riwayat presensi.
7. [**07. Rekapitulasi & Pelaporan Eksekutif**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/07-rekapitulasi-dan-laporan.md)
   * Rekap kehadiran piket & kegiatan per anggota, grafik mingguan, monitoring departemen PresBEM, dan alert kedisiplinan organisasi.

### Dokumen Spesifikasi Berdasarkan Peran (Role-Based Guide)
* [**Panduan Peran: Anggota Biasa**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/roles/anggota-biasa.md)
* [**Panduan Peran: ADKEU (Bendahara)**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/roles/adkeu-bendahara.md)
* [**Panduan Peran: PSDI**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/roles/psdi.md)
* [**Panduan Peran: PresBEM (Ketua BEM)**](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/docs/roles/presbem.md)

---

## 🎨 Palet Desain & Tipografi (Design System)
Berdasarkan analisis visual UI yang diterapkan:
- **Warna Primer**: Deep Teal (`#196464` / `#165355` / `#006769`) - memberikan kesan profesional, modern, dan formal organisasi kampus.
- **Warna Aksen / Status**:
  - Hijau Sukses / Lunas / Hadir: `#22C55E` / `#E8F5E9`
  - Merah Peringatan / Denda / Alpha: `#EF4444` / `#FEE2E2`
  - Kuning Perhatian / Pending / Izin: `#F59E0B` / `#FEF3C7`
- **Warna Latar Belakang**: Soft Warm Neutral (`#F8F7F4` / `#FBFBFA`) dengan kontras kartu putih murni (`#FFFFFF`).
- **Sidebar**: Dark Charcoal Slate (`#2E3436` / `#373D3F`) dengan teks putih dan aksen aktif hijau toska.
- **Tipografi**: Sans-Serif modern berkarakter ramah dan mudah dibaca (e.g. *Plus Jakarta Sans* / *Inter*).
