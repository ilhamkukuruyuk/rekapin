# Modul 06: Manajemen Kegiatan & Presensi Berbasis QR Code

## 1. Deskripsi Fitur
Modul Manajemen Kegiatan dan Presensi QR Code dikelola oleh Departemen **PSDI (Pengembangan Sumber Daya Internal)** untuk mengoordinasikan seluruh agenda acara internal BEM FILKOM UB (seperti seminar, pelatihan, workshop, rapat kerja, dan *focus group discussion*). Modul ini menyediakan sistem presensi nirsentuh yang menggunakan token QR per sesi, ditampilkan saat sesi dibuka dan dinonaktifkan saat sesi ditutup.

---

## 2. Acuan Tangkapan Layar (Screenshots)
- **Katalog & Manajemen Kegiatan (PSDI)**:
  - [`Rekapin-Resource/UI-PSDI/kegiatan semua.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/kegiatan%20semua.png)
  - [`Rekapin-Resource/UI-PSDI/kegiatan berlangsung.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/kegiatan%20berlangsung.png)
  - [`Rekapin-Resource/UI-PSDI/kegiatan akan datang.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/kegiatan%20akan%20datang.png)
  - [`Rekapin-Resource/UI-PSDI/kegiatan selesai.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PSDI/kegiatan%20selesai.png)
- **Pemindai Kamera QR Presensi (Anggota & PresBEM)**:
  - [`Rekapin-Resource/UI-Anggota/scan qr anggota.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/scan%20qr%20anggota.png)
  - [`Rekapin-Resource/UI-PresBem/qr presensi.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/qr%20presensi.png)
- **Riwayat Presensi Kegiatan (Anggota & PresBEM)**:
  - [`Rekapin-Resource/UI-Anggota/riwayat presensi anggota-1.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-Anggota/riwayat%20presensi%20anggota-1.png)
  - [`Rekapin-Resource/UI-PresBem/riwayat scan.png`](file:///c:/Users/Archie/Downloads/MPTI-Kelompok1/Rekapin-Resource/UI-PresBem/riwayat%20scan.png)

---

## 3. Komponen & Spesifikasi Antarmuka (UI Specification)

### 3.1. Halaman Manajemen Kegiatan (PSDI)
Digunakan oleh pengurus PSDI untuk mempublikasikan dan mengelola acara:
1. **Header & Tombol Tambah**:
   - Judul: *"Kegiatan - Manajemen kegiatan dan generate QR presensi"*.
   - Tombol Aksi: `[+ Tambah Kegiatan]` (membuka modal formulir pembuatan kegiatan baru).
2. **Filter Status Pelaksanaan**:
   - Tab tombol navigasi: `Semua` | `Belum Dibuka` | `Sesi Aktif` | `Selesai`.
3. **Kartu Grid Kegiatan (*Event Cards Grid*)**:
   - Setiap kartu kegiatan menyajikan:
     - **Judul Kegiatan**: e.g. *Seminar Kepemimpinan*, *Workshop UI/UX Design*, *Pelatihan Public Speaking*, *FGD Roadmap BEM 2026*.
     - **Badge Status**: `Belum Dibuka`, `Sesi Aktif`, atau `Selesai`.
     - **Deskripsi Ringkas Acara**: Penjelasan tujuan dan sasaran peserta.
     - **Metadata Pelaksanaan**:
       - 📅 Tanggal pelaksanaan (e.g. `20/09/2025`, `28/09/2025`).
       - 📍 Lokasi acara (e.g. `Aula FILKOM`, `Lab Komputer 301`, `Ruang Rapat BEM`).
       - 👥 Statistik Peserta: e.g. `28/30 hadir` (jika selesai) atau `20 peserta terdaftar` (jika mendatang).
     - **Tombol Aksi**:
       - `[Detail]`: Membuka daftar hadir lengkap dan catatan kegiatan.
       - `[Buka Sesi & Tampilkan QR]`: Membuka sesi yang belum aktif dan menerbitkan token QR baru.
       - `[Lihat QR Presensi]` dan `[Tutup Sesi]`: Hanya tersedia selama sesi aktif. QR tidak ditampilkan lagi setelah sesi ditutup.
   - Pembuatan kegiatan memerlukan nama, tanggal, jenis, jam mulai, dan jam selesai. Lokasi dan deskripsi bersifat opsional.
   - Sesi presensi dibuka otomatis pada jam mulai dan ditutup otomatis pada jam selesai (WIB).

---

### 3.2. Halaman Presensi Kegiatan (Scan QR - Anggota & PresBEM)
Digunakan oleh fungsionaris saat hadir di lokasi kegiatan:
1. **Sistem Tab Navigasi**:
   - Tab aktif: `Scan QR` | Tab sekunder: `Riwayat Presensi`.
2. **Frame Pemindai Kamera (*Camera Viewfinder Frame*) & Bukti Kehadiran**:
   - Judul: *"Scan QR Presensi"*.
   - Subjudul instruksi: *"Minta panitia untuk menampilkan QR code kegiatan"*.
   - Kotak Viewfinder Kamera: Dilengkapi sudut bidik (*corner brackets*) dan ikon pemindaian interaktif (*"Arahkan kamera ke QR Code"*).
   - Tombol Aksi: `[Mulai Scan]` (mengaktifkan modul kamera perangkat/webcam).
   - Input Kode QR: Mendukung auto-fill dari deteksi kamera atau input manual kode kegiatan.
   - **Unggah Foto Selfie Bukti Kehadiran**: Foto kamera atau berkas foto wajib disertakan; pengiriman tanpa foto ditolak.
   - **Timestamp Otomatis**: Sistem menangkap waktu absensi secara valid dan *real-time* saat pengiriman formulir.
3. **Banner Informasi Lokasi**:
   - Pesan informasi: *"Pastikan Anda berada di lokasi kegiatan saat melakukan presensi"*.

---

### 3.3. Tab Riwayat Presensi Kegiatan
Menampilkan catatan partisipasi kegiatan fungsionaris:
1. **Daftar Log Presensi Acara**:
   - Setiap entri mencakup:
     - Ikon status (centang hijau dalam lingkaran atau silang merah).
     - **Nama Acara**: e.g. *Seminar Kepemimpinan*, *Workshop UI/UX Design*, *Rapat Koordinasi Bulanan*.
     - **Tanggal & Metode Presensi**: e.g. `20/09/2025 · via QR Code`, `10/09/2025 · via Manual`.
     - **Status Badge**:
       - `Hadir`: Berlatar hijau muda.
       - `Izin`: Berlatar kuning muda.
       - `Alpha`: Berlatar merah muda, termasuk kegiatan yang sudah selesai tanpa catatan presensi.
       - Agenda yang belum selesai ditampilkan sebagai `Belum presensi`, bukan Alpha.
2. **Ringkasan Kehadiran Pribadi**:
   - Menghitung jumlah `Hadir`, `Izin`, dan `Alpha` untuk kegiatan yang sudah selesai.
   - Persentase kehadiran dihitung dari jumlah Hadir dibagi seluruh kegiatan yang sudah selesai.

---

## 4. Alur Kerja Sesi Presensi QR (Workflow)

```mermaid
sequenceDiagram
    autonumber
    actor PSDI as Pengurus PSDI
    participant System as Sistem Rekapin
    actor Anggota as Fungsionaris BEM

    PSDI->>System: Buat kegiatan (nama, tanggal, jenis, jam mulai, jam selesai)
    System-->>PSDI: Kegiatan tersimpan dengan status Belum Dibuka
    System->>System: Pada jam mulai, aktifkan sesi dan terbitkan token QR baru
    PSDI->>PSDI: Tampilkan QR di Layar Proyektor Acara
    Anggota->>System: Buka Presensi Kegiatan -> Klik [Mulai Scan]
    Anggota->>System: Pindai QR di Proyektor Menggunakan Kamera
    System->>System: Validasi token, status sesi, foto, dan duplikasi
    alt Token valid, sesi aktif, foto ada, belum pernah presensi
        System->>System: Simpan kehadiran, waktu, dan foto
        System-->>Anggota: Notifikasi Sukses "Presensi Berhasil Dicatat"
    else Sesi belum dibuka/ditutup, token tidak sah, atau foto tidak ada
        System-->>Anggota: Tolak presensi dan tampilkan alasan
    end
    Note over PSDI,System: Pada jam selesai
    System->>System: Nonaktifkan token QR dan ubah status menjadi "Selesai"
    System->>System: Identifikasi Anggota Tanpa Keterangan -> Terbitkan Denda Kegiatan Rp50.000
```

---

## 5. Aturan Bisnis & Mekanisme Keamanan
1. **Jadwal Sesi**: Sesi dan QR dibuka otomatis pada jam mulai kegiatan dan dinonaktifkan pada jam selesai. PSDI tetap dapat membuka atau menutup sesi secara manual.
2. **One-Time Scan Constraint**: Satu akun fungsionaris hanya dapat melakukan presensi satu kali per kegiatan.
3. **Foto Wajib**: Presensi tidak disimpan tanpa foto bukti kehadiran.
4. **Penyusulan Manual (Dispensasi)**: Panitia PSDI memiliki kewenangan menandai presensi secara `Manual` bila fungsionaris mengalami kendala teknis pada kamera perangkatnya.
5. **Sanksi Ketidakhadiran (Alpha Kegiatan)**: Fungsionaris yang wajib hadir namun tidak melakukan presensi sampai status acara `Selesai` otomatis dikenakan sanksi **Denda Kegiatan sebesar Rp50.000**.

## 6. Catatan Operasional Presensi
- Sebelum fitur digunakan, jalankan `php artisan migrate`, `php artisan storage:link`, dan `npm run build`.
- Scheduler harus berjalan agar sesi otomatis berubah tepat waktu. Jalankan `php artisan schedule:work` pada lingkungan lokal; image Docker aplikasi menjalankan scheduler bersama web server.
- Kamera memerlukan izin pengguna dan konteks aman browser (`HTTPS` atau `localhost`). Jika kamera tidak tersedia, kode kegiatan dapat dimasukkan manual.
- Isi QR Code harus sama dengan nilai `qr_code` pada data kegiatan. Hasil scan diverifikasi server sebelum presensi disimpan.
- Token QR baru diterbitkan ketika sesi dibuka. Menutup sesi langsung menonaktifkan token tersebut.
- Waktu disimpan dalam UTC dan ditampilkan dalam zona waktu WIB.
- PSDI dapat membuka menu **Rekap Presensi** untuk melihat jumlah Hadir/Izin/Alpha, persentase kehadiran per fungsionaris, dan mengunduh rincian seluruh agenda sebagai CSV.
- Rekap memasukkan anggota, bendahara/ADKEU, PSDI, dan PresBEM. Untuk agenda selesai, anggota tanpa catatan Hadir/Izin dihitung Alpha agar kelengkapan status rekap mencapai 100%; agenda aktif dan mendatang tidak masuk persentase akhir.
