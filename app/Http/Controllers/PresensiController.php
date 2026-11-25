<?php

namespace App\Http\Controllers;

use App\Models\IzinPresensi;
use App\Models\Kegiatan;
use App\Models\PresensiKegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PresensiController extends Controller
{
    /**
     * Show QR scanner and attendance page for members / presbem
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Only sessions that have been explicitly opened may be attended.
        $kegiatanAktif = Kegiatan::query()
            ->where('status', Kegiatan::STATUS_BERLANGSUNG)
            ->where(function ($query): void {
                $query->whereNull('qr_expires_at')
                    ->orWhere('qr_expires_at', '>=', now());
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        // User's attendance records
        $riwayatPresensi = PresensiKegiatan::with('kegiatan')
            ->where('user_id', $user->id)
            ->orderByDesc('waktu_presensi')
            ->get();

        // User's permission (izin) requests
        $izinSaya = IzinPresensi::with(['kegiatan', 'reviewer'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Kegiatan yang dapat diajukan izin (kegiatan aktif atau akan datang)
        $kegiatanBisaIzin = Kegiatan::query()
            ->whereIn('status', [Kegiatan::STATUS_BERLANGSUNG, Kegiatan::STATUS_AKAN_DATANG])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $activeTab = $request->query('tab', 'scan');

        $viewName = $user->isPresbem() ? 'presbem.presensi-kegiatan' : 'anggota.presensi-kegiatan';

        return view($viewName, [
            'kegiatanAktif' => $kegiatanAktif,
            'kegiatanBisaIzin' => $kegiatanBisaIzin,
            'riwayatPresensi' => $riwayatPresensi,
            'izinSaya' => $izinSaya,
            'activeTab' => $activeTab,
            'currentTime' => now()->timezone('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm:ss') . ' WIB',
        ]);
    }

    /**
     * Process member attendance submission with QR code and selfie photo
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        // Basic validation
        $request->validate([
            'qr_code' => ['required', 'string', 'max:100'],
            'bukti_foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'bukti_foto_base64' => ['nullable', 'string', 'max:7000000'],
        ]);

        if (! $request->hasFile('bukti_foto') && empty($request->input('bukti_foto_base64'))) {
            throw ValidationException::withMessages([
                'bukti_foto' => 'Foto selfie bukti kehadiran wajib diunggah atau diambil menggunakan kamera.',
            ]);
        }

        // Find activity by QR Code
        $qrCode = trim($request->input('qr_code'));
        $kegiatan = Kegiatan::where('qr_code', $qrCode)->first();

        if (! $kegiatan) {
            throw ValidationException::withMessages([
                'qr_code' => 'QR Code kegiatan tidak valid atau tidak terdaftar.',
            ]);
        }

        $photoPath = null;
        try {
            [$kegiatan, $presensi] = DB::transaction(function () use ($kegiatan, $request, $user, &$photoPath): array {
                $kegiatan = Kegiatan::query()->lockForUpdate()->findOrFail($kegiatan->id);

                if ($kegiatan->status !== Kegiatan::STATUS_BERLANGSUNG) {
                    throw ValidationException::withMessages([
                        'qr_code' => 'Sesi kegiatan "' . $kegiatan->nama . '" belum dibuka atau telah ditutup.',
                    ]);
                }

                if ($kegiatan->qr_expires_at && now()->greaterThan($kegiatan->qr_expires_at)) {
                    throw ValidationException::withMessages([
                        'qr_code' => 'Kode QR telah kadaluarsa. Silakan minta panitia kegiatan untuk memperbarui QR Code.',
                    ]);
                }

                if ($kegiatan->hasUserAttended($user->id)) {
                    throw ValidationException::withMessages([
                        'qr_code' => 'Anda sudah tercatat melakukan presensi untuk kegiatan "' . $kegiatan->nama . '". Presensi hanya dapat dilakukan 1 kali.',
                    ]);
                }

                if ($request->hasFile('bukti_foto')) {
                    $photoPath = $request->file('bukti_foto')->store('presensi-bukti', 'public');
                } else {
                    $photoPath = $this->storeBase64Photo($request->input('bukti_foto_base64'));
                }

                if (! $photoPath) {
                    throw new RuntimeException('Foto selfie gagal disimpan.');
                }

                $waktuPresensi = now();
                $presensi = PresensiKegiatan::create([
                    'kegiatan_id' => $kegiatan->id,
                    'user_id' => $user->id,
                    'waktu_presensi' => $waktuPresensi,
                    'bukti_foto' => $photoPath,
                    'metode' => PresensiKegiatan::METHOD_QR,
                    'status' => PresensiKegiatan::STATUS_HADIR,
                    'catatan' => $request->input('catatan'),
                ]);

                return [$kegiatan, $presensi];
            });
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        $waktuPresensi = $presensi->waktu_presensi->timezone('Asia/Jakarta');
        $message = "Presensi kegiatan '{$kegiatan->nama}' berhasil dicatat pada {$waktuPresensi->format('H:i:s')} WIB!";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $presensi->id,
                    'kegiatan' => $kegiatan->nama,
                    'waktu_presensi' => $waktuPresensi->format('d/m/Y H:i:s'),
                    'bukti_foto' => $presensi->bukti_foto_url,
                    'status' => 'Hadir',
                ],
            ]);
        }

        $redirectRoute = $user->isPresbem() ? 'presbem.presensi-kegiatan' : 'anggota.presensi-kegiatan';

        return redirect()->route($redirectRoute, ['tab' => 'riwayat'])
            ->with('success', $message);
    }

    /**
     * Show combined attendance history (Piket & Kegiatan)
     */
    public function history(Request $request): View
    {
        $user = $request->user();

        $kegiatanRiwayat = Kegiatan::query()
            ->with(['presensis' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->get();

        $kegiatanSelesai = $kegiatanRiwayat
            ->where('status', Kegiatan::STATUS_SELESAI);
        $statusPresensi = static function (Kegiatan $kegiatan): string {
            $presensi = $kegiatan->presensis->first();

            if ($presensi?->status === PresensiKegiatan::STATUS_HADIR) {
                return PresensiKegiatan::STATUS_HADIR;
            }

            if ($presensi?->status === PresensiKegiatan::STATUS_IZIN) {
                return PresensiKegiatan::STATUS_IZIN;
            }

            if ($presensi?->status === PresensiKegiatan::STATUS_TIDAK_HADIR
                || $kegiatan->status === Kegiatan::STATUS_SELESAI) {
                return PresensiKegiatan::STATUS_TIDAK_HADIR;
            }

            return 'belum-presensi';
        };
        $totalKegiatanSelesai = $kegiatanSelesai->count();
        $totalHadir = $kegiatanSelesai
            ->filter(fn (Kegiatan $kegiatan): bool => $statusPresensi($kegiatan) === PresensiKegiatan::STATUS_HADIR)
            ->count();
        $totalIzin = $kegiatanSelesai
            ->filter(fn (Kegiatan $kegiatan): bool => $statusPresensi($kegiatan) === PresensiKegiatan::STATUS_IZIN)
            ->count();
        $totalAlpha = $totalKegiatanSelesai - $totalHadir - $totalIzin;

        $activeTab = in_array($request->query('tab'), ['piket', 'kegiatan'], true)
            ? $request->query('tab')
            : 'kegiatan';
        $viewName = $user->isPresbem() ? 'presbem.riwayat-presensi' : 'anggota.riwayat-presensi';

        return view($viewName, [
            'kegiatanRiwayat' => $kegiatanRiwayat,
            'activeTab' => $activeTab,
            'ringkasanKegiatan' => [
                'hadir' => $totalHadir,
                'izin' => $totalIzin,
                'alpha' => $totalAlpha,
                'selesai' => $totalKegiatanSelesai,
                'persentase' => $totalKegiatanSelesai > 0
                    ? (int) round($totalHadir / $totalKegiatanSelesai * 100)
                    : 0,
            ],
        ]);
    }

    /**
     * Submit activity attendance permission (izin) request
     */
    public function ajukanIzin(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'kegiatan_id' => ['required', 'integer', 'exists:kegiatans,id'],
            'alasan' => ['required', 'string', 'min:5', 'max:2000'],
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'kegiatan_id.required' => 'Pilih kegiatan yang ingin diajukan izin.',
            'alasan.required' => 'Tuliskan alasan izin tidak dapat menghadiri kegiatan.',
            'alasan.min' => 'Alasan izin minimal 5 karakter.',
            'bukti.required' => 'Unggah dokumen bukti perizinan (surat sakit/dispensasi/tugas).',
            'bukti.mimes' => 'Bukti perizinan harus berupa berkas JPG, PNG, atau PDF.',
            'bukti.max' => 'Ukuran berkas bukti perizinan maksimal 2 MB.',
        ]);

        $kegiatan = Kegiatan::findOrFail($data['kegiatan_id']);

        if ($kegiatan->hasUserAttended($user->id)) {
            throw ValidationException::withMessages([
                'kegiatan_id' => 'Anda sudah tercatat presensi hadir untuk kegiatan ini.',
            ]);
        }

        $existingIzin = IzinPresensi::where('kegiatan_id', $kegiatan->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingIzin && $existingIzin->status === IzinPresensi::STATUS_DISETUJUI) {
            throw ValidationException::withMessages([
                'kegiatan_id' => 'Pengajuan izin Anda untuk kegiatan ini telah disetujui sebelumnya.',
            ]);
        }

        if ($existingIzin && $existingIzin->status === IzinPresensi::STATUS_MENUNGGU) {
            throw ValidationException::withMessages([
                'kegiatan_id' => 'Pengajuan izin Anda masih dalam status Menunggu Validasi oleh PSDI.',
            ]);
        }

        $buktiPath = $request->file('bukti')->store('izin-bukti', 'public');
        if (! $buktiPath) {
            throw new RuntimeException('Gagal mengunggah berkas bukti perizinan.');
        }

        IzinPresensi::updateOrCreate(
            ['kegiatan_id' => $kegiatan->id, 'user_id' => $user->id],
            [
                'alasan' => $data['alasan'],
                'bukti_path' => $buktiPath,
                'status' => IzinPresensi::STATUS_MENUNGGU,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'catatan_reviewer' => null,
            ]
        );

        $redirectRoute = $user->isPresbem() ? 'presbem.presensi-kegiatan' : 'anggota.presensi-kegiatan';

        return redirect()->route($redirectRoute, ['tab' => 'izin'])
            ->with('success', 'Pengajuan izin untuk kegiatan "' . $kegiatan->nama . '" berhasil dikirim dan kini berstatus Menunggu Validasi PSDI.');
    }

    /**
     * View or download permission document proof
     */
    public function buktiIzin(Request $request, IzinPresensi $izin)
    {
        $user = $request->user();

        // Ensure user is authorized to see the document (owner or PSDI/admin/presbem)
        if ($user->id !== $izin->user_id && ! $user->hasRole(['psdi', 'presbem', 'admin', 'adkeu', 'bendahara'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat dokumen ini.');
        }

        $disk = Storage::disk('public');
        abort_unless($disk->exists($izin->bukti_path), 404, 'Berkas bukti izin tidak ditemukan.');

        return $disk->response($izin->bukti_path);
    }

    /**
     * Stream or download selfie photo proof
     */
    public function bukti(Request $request, PresensiKegiatan $presensi)
    {
        $user = $request->user();

        // Ensure user is authorized to see the photo (owner or organizers)
        if ($user->id !== $presensi->user_id && ! $user->hasRole(['psdi', 'bendahara', 'adkeu', 'presbem'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat foto ini.');
        }

        $disk = Storage::disk('public');
        abort_unless($disk->exists($presensi->bukti_foto), 404, 'Berkas foto tidak ditemukan.');

        return $disk->response($presensi->bukti_foto);
    }

    /**
     * Decode and save base64 encoded photo from webcam
     */
    private function storeBase64Photo(string $base64String): string
    {
        if (! preg_match('/^data:image\/(jpeg|png|webp);base64,([A-Za-z0-9+\/=]+)$/', $base64String, $matches)) {
            throw ValidationException::withMessages([
                'bukti_foto' => 'Format foto selfie tidak valid. Gunakan foto JPG, PNG, atau WebP.',
            ]);
        }

        $binaryData = base64_decode($matches[2], true);
        if ($binaryData === false || strlen($binaryData) > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'bukti_foto' => 'Ukuran foto selfie maksimal 5 MB.',
            ]);
        }

        $imageInfo = getimagesizefromstring($binaryData);
        $mime = $imageInfo['mime'] ?? null;
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (! $binaryData || ! isset($extensions[$mime]) || $mime !== 'image/' . $matches[1]) {
            throw ValidationException::withMessages([
                'bukti_foto' => 'Isi foto selfie tidak valid atau formatnya tidak didukung.',
            ]);
        }

        $fileName = 'presensi-bukti/' . Str::uuid() . '.' . $extensions[$mime];
        if (! Storage::disk('public')->put($fileName, $binaryData)) {
            throw new RuntimeException('Foto selfie gagal disimpan.');
        }

        return $fileName;
    }
}
