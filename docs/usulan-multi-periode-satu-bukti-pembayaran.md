# Usulan Revisi: Bayar Beberapa Bulan dengan Satu Bukti

> **Status: sudah diimplementasikan.** Bagian sebelum/sesudah di bawah mendokumentasikan rancangan awal dan perubahan aktualnya.

## Hasil implementasi

- Halaman pembayaran anggota dan PresBEM kini selalu menyediakan form untuk memilih satu atau beberapa bulan, termasuk saat tagihan bulan berjalan sudah lunas atau sedang menunggu verifikasi.
- Layout anggota memuat stack script halaman sehingga tombol tambah/hapus periode berfungsi di halaman anggota.
- Satu bukti diunggah sekali dan digunakan oleh satu baris pembayaran per bulan. ADKEU tetap memproses dan menyetujui setiap baris secara terpisah.
- Server menolak periode duplikat/masa depan dan periode yang sudah lunas/menunggu verifikasi. Penyimpanan seluruh periode berlangsung dalam satu transaksi; kegagalan membersihkan file unggahan baru.
- Pengajuan ulang pembayaran yang ditolak tidak menghapus bukti lama jika masih digunakan oleh periode lain.
- Tidak ada perubahan skema database.
- Pengujian fitur mencakup UI, pengajuan multi-bulan, persetujuan terpisah, atomicity, validasi periode, dan bukti yang dipakai bersama.

## Tujuan

Anggota dapat mengajukan pembayaran untuk satu atau beberapa bulan sekaligus dengan **satu file bukti transfer**. Sistem tetap menyimpan dan memverifikasi pembayaran **per bulan**, sehingga status lunas, denda, riwayat, laporan kas, dan transaksi kas masuk yang sudah ada tetap bekerja per periode.

### Keputusan rancangan

- Form memakai beberapa pemilih bulan yang bisa ditambah/dihapus. Setiap bulan tetap menerima format yang sama seperti input sekarang (`YYYY-MM`) dan tidak boleh lebih dari bulan berjalan.
- Pengguna mengunggah satu bukti untuk seluruh bulan yang dipilih.
- Satu baris `cash_payments` tetap dibuat atau diperbarui untuk setiap bulan. Baris-baris tersebut menunjuk ke bukti yang sama.
- ADKEU tetap menyetujui atau menolak **setiap bulan secara terpisah**. Persetujuan satu bulan mencatat satu transaksi kas masuk untuk bulan itu.
- Bulan yang sudah disetujui atau masih menunggu verifikasi tidak bisa diajukan ulang. Bulan yang ditolak dapat diajukan ulang.
- Tidak perlu mengubah skema database: unique constraint `user_id + period` yang ada tetap dipakai.
- Jika bukti lama dipakai oleh beberapa baris pembayaran, file lama hanya boleh dihapus setelah tidak ada baris lain yang merujuknya. Ini mencegah bukti untuk bulan lain ikut hilang saat pengajuan ulang.

## Perubahan tampilan

### Sebelum

Form hanya memiliki satu pemilih periode dan satu input bukti:

```blade
<div>
    <label for="period" class="mb-1.5 block text-xs font-semibold text-gray-700">Periode kas</label>
    <input id="period" name="period" type="month" required max="{{ now()->format('Y-m') }}" value="{{ old('period', now()->format('Y-m')) }}">
    @error('period')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="proof" class="mb-1.5 block text-xs font-semibold text-gray-700">Bukti transfer</label>
    <input id="proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" required>
    @error('proof')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
```

### Sesudah — implementasi

Sediakan beberapa input bulan dalam satu form. Tombol **Tambah bulan** menambah pemilih, tombol **Hapus** menghapus pemilih tersebut; bagian bukti tetap satu input. Setiap pemilih mempertahankan batas maksimal bulan berjalan dan styling Tailwind yang sudah dipakai halaman.

```blade
<div class="sm:col-span-2">
    <p class="mb-1.5 text-xs font-semibold text-gray-700">Periode kas</p>
    <div data-period-list class="space-y-2">
        @foreach(is_array(old('periods')) ? old('periods') : [now()->format('Y-m')] as $period)
            <div class="flex gap-2">
                <input name="periods[]" type="month" required
                    max="{{ now()->format('Y-m') }}" value="{{ $period }}"
                    class="min-w-0 flex-1 rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm">
                <button type="button" data-remove-period
                    class="rounded-xl border px-3 text-sm">Hapus</button>
            </div>
        @endforeach
    </div>
    <button type="button" data-add-period
        class="mt-2 text-sm font-semibold text-[#1A6B6B]">+ Tambah bulan</button>
    @error('periods')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    @error('periods.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div class="sm:col-span-2">
    <label for="proof" class="mb-1.5 block text-xs font-semibold text-gray-700">Satu bukti transfer untuk semua periode</label>
    <input id="proof" name="proof" type="file"
        accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" required>
    @error('proof')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
```

Kontrol **Tambah bulan/Hapus** perlu sedikit JavaScript untuk menambah dan menghapus baris pemilih. Validasi server tetap menjadi acuan—termasuk saat browser mengirim periode yang sama lebih dari sekali.

Contoh perilaku tombol dengan JavaScript biasa:

```html
<script>
    const periodList = document.querySelector('[data-period-list]');

    if (periodList) {
        document.querySelector('[data-add-period]')?.addEventListener('click', () => {
            const row = periodList.querySelector('[data-period-row]')?.cloneNode(true);
            if (!row) return;

            row.querySelector('input').value = '';
            periodList.append(row);
        });

        periodList.addEventListener('click', (event) => {
            if (event.target.closest('[data-remove-period]')
                && periodList.querySelectorAll('[data-period-row]').length > 1) {
                event.target.closest('[data-period-row]').remove();
            }
        });
    }
</script>
```

Setiap baris pemilih pada Blade perlu diberi atribut `data-period-row` agar cocok dengan contoh di atas.

## Perubahan kode

### Sebelum — request dan validasi periode di `CashPaymentController::submit`

```php
$data = $request->validate([
    'period' => ['required', 'date_format:Y-m'],
    'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
]);

$period = Carbon::createFromFormat('!Y-m', $data['period'])->startOfMonth();
if ($period->greaterThan(now()->startOfMonth())) {
    throw ValidationException::withMessages([
        'period' => 'Periode pembayaran tidak boleh di masa depan.',
    ]);
}
```

Saat ini controller mengambil satu pembayaran berdasarkan satu periode, lalu menyimpan satu baris:

```php
$payment = CashPayment::query()
    ->where('user_id', $request->user()->id)
    ->whereDate('period', $period->toDateString())
    ->first();

// ...

CashPayment::create([
    'user_id' => $request->user()->id,
    'period' => $period->toDateString(),
    'amount' => CashPayment::AMOUNT,
    'late_fee_amount' => $lateFeeAmount,
    'proof_path' => $proofPath,
    'submitted_at' => $submittedAt,
    'status' => CashPayment::STATUS_PENDING,
]);
```

### Sesudah — validasi beberapa periode

Ganti `period` menjadi `periods[]`, pastikan ada minimal satu periode, tolak periode duplikat, dan validasi setiap periode tidak di masa depan. Aturan file tetap satu file dengan batas ukuran dan tipe yang sama.

```php
$data = $request->validate([
    'periods' => ['required', 'array', 'min:1'],
    'periods.*' => ['required', 'date_format:Y-m', 'distinct'],
    'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
]);

$periods = collect($data['periods'])
    ->map(fn (string $value): Carbon => Carbon::createFromFormat('!Y-m', $value)->startOfMonth())
    ->values();

if ($periods->contains(fn (Carbon $period): bool => $period->greaterThan(now()->startOfMonth()))) {
    throw ValidationException::withMessages([
        'periods' => 'Periode pembayaran tidak boleh di masa depan.',
    ]);
}
```

### Sesudah — penyimpanan

Unggah file sekali, lalu proses seluruh periode dalam satu transaksi database. Untuk setiap periode, cari baris anggota tersebut: tolak status `approved` dan `pending`; buat baris baru bila belum ada; atau reset baris `rejected` menjadi `pending`. Denda dihitung masing-masing berdasarkan periode dan waktu pengajuan yang sama.

```php
$proofDisk = (string) config('cash.proof_disk', 'local');
$disk = Storage::disk($proofDisk);
$proofPath = $request->file('proof')->store('cash-payment-proofs', $proofDisk);

if (! $proofPath) {
    throw new RuntimeException('Bukti pembayaran gagal disimpan.');
}

$oldProofPaths = [];
$submittedAt = now();

try {
    DB::transaction(function () use (
        $periods, $request, $proofPath, $submittedAt, &$oldProofPaths
    ): void {
        $payments = CashPayment::query()
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($periods): void {
                foreach ($periods as $period) {
                    $query->orWhereDate('period', $period->toDateString());
                }
            })
            ->orderBy('period')
            ->lockForUpdate()
            ->get()
            ->keyBy(fn (CashPayment $payment): string => $payment->period->format('Y-m'));

        foreach ($periods as $period) {
            $payment = $payments->get($period->format('Y-m'));

            if (in_array($payment?->status, [
                CashPayment::STATUS_APPROVED,
                CashPayment::STATUS_PENDING,
            ], true)) {
                throw ValidationException::withMessages([
                    'periods' => 'Salah satu periode sudah lunas atau sedang menunggu verifikasi.',
                ]);
            }
        }

        foreach ($periods as $period) {
            $payment = $payments->get($period->format('Y-m'));
            $oldProofPaths[] = $payment?->proof_path;
            $attributes = [
                'amount' => CashPayment::AMOUNT,
                'late_fee_amount' => CashPayment::lateFeeFor($period, $submittedAt),
                'proof_path' => $proofPath,
                'submitted_at' => $submittedAt,
                'status' => CashPayment::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
            ];

            if ($payment) {
                $payment->update($attributes);
            } else {
                CashPayment::create($attributes + [
                    'user_id' => $request->user()->id,
                    'period' => $period->toDateString(),
                ]);
            }
        }
    });
} catch (Throwable $exception) {
    $disk->delete($proofPath);
    throw $exception;
}
```

Setelah transaksi berhasil, kumpulkan path bukti lama yang tidak lagi dipakai oleh baris pembayaran mana pun; hanya path tersebut yang aman dihapus:

```php
foreach (array_unique(array_filter($oldProofPaths)) as $oldProofPath) {
    $stillReferenced = CashPayment::query()
        ->where('proof_path', $oldProofPath)
        ->exists();

    if (! $stillReferenced) {
        $disk->delete($oldProofPath);
    }
}
```

Pengecekan ini dilakukan setelah transaksi database berhasil. Pesan sukses sebaiknya menyebut jumlah periode yang diajukan.

> Cuplikan sesudah menjelaskan pola implementasi. Kode aktual mengunci dan memeriksa semua baris terpilih sebelum menulis perubahan agar satu pengajuan tidak tersimpan sebagian.

## Dampak ke alur yang sudah ada

- **Database:** tidak ada migration yang diperlukan. Unique constraint `cash_payments(user_id, period)` tetap mencegah duplikasi per bulan.
- **Validasi ADKEU:** tetap bekerja per baris/per bulan. Bukti yang sama bisa dibuka dari tiap pengajuan.
- **Persetujuan:** setiap bulan yang disetujui membuat transaksi kas masuk tersendiri, dengan nominal kas dan denda bulan tersebut.
- **Riwayat, rekap, dan denda:** tetap menerima satu baris per bulan; tidak perlu mengubah arti status bulanan.
- **Bukti ditolak:** pengajuan ulang dapat memakai file baru; bukti lama tidak dihapus jika masih dirujuk bulan lain.

## Kriteria penerimaan

1. Pengguna dapat mengirim satu periode atau beberapa periode dengan satu file.
2. Sistem membuat/memperbarui satu baris `cash_payments` untuk masing-masing periode dan menyimpan satu path bukti yang sama.
3. Periode duplikat dan periode di masa depan ditolak dengan pesan validasi.
4. Pengajuan yang memuat periode berstatus `approved` atau `pending` ditolak tanpa meninggalkan file baru atau perubahan parsial pada periode lain.
5. Periode berstatus `rejected` dapat diajukan ulang.
6. Denda dihitung per periode; tiap pembayaran tetap disetujui/ditolak dan dicatat ke kas secara terpisah.
7. Mengganti bukti untuk satu periode tidak menghapus file yang masih dipakai periode lain.
8. Pengujian mencakup pengajuan multi-bulan, file tidak valid, periode duplikat/masa depan, status yang tidak bisa diajukan ulang, dan pemakaian ulang bukti.

## Berkas yang diubah

- `resources/views/anggota/bayar-kas.blade.php` — form pemilihan beberapa bulan dan satu file bukti.
- `app/Http/Controllers/CashPaymentController.php` — validasi, pemrosesan multi-periode, dan lifecycle file bukti.
- `tests/Feature/CashPaymentVerificationTest.php` — pengujian skenario multi-periode dan kasus tepi.
- `docs/03-manajemen-kas.md` — dokumentasi alur pembayaran yang diperbarui.
