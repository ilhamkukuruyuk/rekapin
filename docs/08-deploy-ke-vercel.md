# Deploy Laravel ke Vercel

Repository ini menggunakan Docker + FrankenPHP mengikuti panduan container PHP Vercel. Runtime PHP komunitas lama tidak dipakai.

## Konfigurasi yang disiapkan

- `Dockerfile` membangun aset Vite dan image Laravel produksi dengan PHP 8.4, PostgreSQL, dan FrankenPHP.
- `Caddyfile` hanya menyajikan `public/` dan meneruskan route Laravel ke `public/index.php`.
- `vercel.json` mendeklarasikan container dan meneruskan semua route aplikasi ke container.
- `.dockerignore` mencegah `.env`, SQLite lokal, upload, dan dependency development masuk ke image.
- Upload bukti pembayaran diarahkan ke disk private yang dapat dikonfigurasi. Foto presensi diarahkan ke disk public yang dapat dikonfigurasi.
- Migrasi tabel `sessions` ditambahkan untuk driver sesi database.
- Akun demo dan kredensial demo disembunyikan di production; `UserSeeder` menolak dijalankan di production.

## Sebelum deploy

1. Push repository ke GitHub dan import repository itu di Vercel. Pastikan **Root Directory** adalah root repository serta framework preset memilih **Container**. Simpan `Dockerfile`, `Caddyfile`, dan `vercel.json` di root.
2. Sediakan PostgreSQL terkelola. SQLite di file lokal tidak cocok untuk Vercel karena image/function bersifat sementara dan dapat berjalan di beberapa instance.
3. Siapkan object storage S3-compatible (mis. AWS S3 atau Cloudflare R2). Gunakan bucket privat untuk bukti transfer dan storage publik untuk foto presensi; jika memakai satu bucket, terapkan policy per prefix agar objek `cash-payment-proofs/` tetap privat.
4. Buat nilai `APP_KEY` satu kali secara lokal dengan `php artisan key:generate --show`. Simpan sebagai environment variable Vercel dan jangan menggantinya saat redeploy.
5. Tambahkan environment variables ini di **Vercel → Project → Settings → Environment Variables**:

   ```text
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<key yang dibuat sekali>
   APP_URL=https://<domain-produksi>
   LOG_CHANNEL=stderr

   DB_CONNECTION=pgsql
   DB_HOST=<host PostgreSQL>
   DB_PORT=5432
   DB_DATABASE=<nama database>
   DB_USERNAME=<user database>
   DB_PASSWORD=<password database>
   DB_SSLMODE=require

   SESSION_DRIVER=database
   SESSION_SECURE_COOKIE=true
   CACHE_STORE=database
   QUEUE_CONNECTION=sync

   FILESYSTEM_PUBLIC_DRIVER=s3
   FILESYSTEM_PRIVATE_DRIVER=s3
   CASH_PAYMENT_PROOF_DISK=private
   AWS_ACCESS_KEY_ID=<access key object storage>
   AWS_SECRET_ACCESS_KEY=<secret key object storage>
   AWS_DEFAULT_REGION=<region>
   AWS_PUBLIC_BUCKET=<bucket atau bucket publik>
   AWS_PRIVATE_BUCKET=<bucket atau bucket privat>
   AWS_PUBLIC_URL=https://<domain-publik-object-storage>
   AWS_ENDPOINT=<endpoint S3-compatible; kosongkan untuk AWS S3>
   AWS_USE_PATH_STYLE_ENDPOINT=true
   ```

   `AWS_PRIVATE_URL` tidak diperlukan untuk bukti pembayaran privat. Konfigurasi foto presensi mengasumsikan objek pada disk `public` dapat dibaca lewat URL `AWS_PUBLIC_URL`. Atur kredensial hanya di Vercel, bukan di repository.
6. Untuk email reminder, atur `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, dan `MAIL_FROM_ADDRESS` dengan provider SMTP produksi. Mailer default `log` tidak mengirim email.
7. Deploy Preview dahulu, lalu cek `/up`, login, upload bukti, tampilan foto presensi, sesi lintas-request, ekspor laporan, dan email. Periksa runtime logs jika ada error.

Jika memilih deploy dari terminal, pasang Docker Desktop dan Vercel CLI (`npm install --global vercel`), lalu dari root repository jalankan `vercel login`, `vercel link`, `vercel dev --local` untuk uji lokal, dan `vercel deploy` untuk Preview. Setelah semua konfigurasi production siap, jalankan `vercel deploy --prod`.

## Migrasi database dan akun awal

Jalankan migrasi **sekali** terhadap PostgreSQL production sebelum mengaktifkan traffic; jangan jalankan migrasi pada startup setiap container. Gunakan CI/release job tepercaya yang punya PHP 8.4, Composer dependencies, dan environment database production, kemudian jalankan:

```bash
php artisan migrate --force
```

Jangan jalankan `php artisan db:seed` di production: seeder tersebut memuat akun demo dan sengaja diblokir di environment production. Buat akun organisasi pertama melalui proses provisioning terkontrol (mis. Artisan Tinker dari lingkungan tepercaya yang terhubung ke database production), pakai password unik, lalu tambahkan akun lain dan ganti semua kredensial yang pernah dipakai untuk demo. Jangan buat akun production dengan password contoh dari source code.

## Batasan operasional

- File lokal dalam container tidak persisten; semua upload production wajib memakai object storage.
- Queue disetel `sync` untuk mencegah penggunaan worker database yang terus berjalan. Jika nanti job harus diproses asinkron, gunakan queue service dan worker terpisah.
- Scheduler di `bootstrap/app.php` tidak otomatis berjalan hanya karena aplikasi dideploy. Reminder harian belum otomatis terkirim sampai Vercel Cron atau scheduler eksternal dikonfigurasi untuk menjalankan command dengan mekanisme autentikasi yang aman.
- Docker tidak tersedia pada lingkungan pengembangan saat konfigurasi ini dibuat, jadi image belum dapat diuji dengan `docker build` lokal. Jalankan build lewat Vercel atau komputer yang memiliki Docker sebelum production.

## Referensi

- [Panduan resmi Vercel: Deploy Laravel dengan Docker](https://vercel.com/kb/guide/laravel-php-with-docker)
- [Panduan resmi Laravel: Deployment](https://laravel.com/docs/12.x/deployment)
