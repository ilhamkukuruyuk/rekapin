<?php

namespace App\Http\Controllers;

use App\Models\IzinPresensi;
use App\Models\Kegiatan;
use App\Models\PresensiKegiatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function dashboard(): View
    {
        return view('psdi.dashboard', [
            'kegiatans' => Kegiatan::query()
                ->withCount('presensis')
                ->orderByDesc('tanggal')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ]);
    }

    public function index(): View
    {
        return view('psdi.kegiatan', [
            'kegiatans' => Kegiatan::query()
                ->withCount('presensis')
                ->orderByDesc('tanggal')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jenis' => ['required', 'string', 'max:100'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'lokasi' => ['nullable', 'string', 'max:255'],
        ]);

        Kegiatan::create([
            ...$validated,
            'lokasi' => ($validated['lokasi'] ?? null) ?: 'Belum ditentukan',
            'status' => Kegiatan::STATUS_AKAN_DATANG,
            'qr_code' => Str::random(48),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('psdi.kegiatan')->with('success', 'Kegiatan berhasil disimpan. Buka sesi untuk menampilkan QR presensi.');
    }

    public function openSession(Kegiatan $kegiatan): RedirectResponse
    {
        DB::transaction(function () use ($kegiatan): void {
            $kegiatan = Kegiatan::query()->lockForUpdate()->findOrFail($kegiatan->id);

            if ($kegiatan->status !== Kegiatan::STATUS_AKAN_DATANG) {
                throw ValidationException::withMessages([
                    'sesi' => 'Sesi hanya dapat dibuka untuk kegiatan yang belum dimulai atau ditutup.',
                ]);
            }

            $kegiatan->update([
                'status' => Kegiatan::STATUS_BERLANGSUNG,
                'qr_code' => Str::random(48),
                'qr_expires_at' => null,
            ]);
        });

        return redirect()->route('psdi.kegiatan')
            ->with('success', 'Sesi berhasil dibuka. QR presensi sekarang aktif.')
            ->with('show_qr_for', $kegiatan->id);
    }

    public function closeSession(Kegiatan $kegiatan): RedirectResponse
    {
        DB::transaction(function () use ($kegiatan): void {
            $kegiatan = Kegiatan::query()->lockForUpdate()->findOrFail($kegiatan->id);

            if ($kegiatan->status !== Kegiatan::STATUS_BERLANGSUNG) {
                throw ValidationException::withMessages([
                    'sesi' => 'Hanya sesi yang sedang aktif yang dapat ditutup.',
                ]);
            }

            $kegiatan->update([
                'status' => Kegiatan::STATUS_SELESAI,
                'qr_expires_at' => now(),
            ]);
        });

        return redirect()->route('psdi.kegiatan')->with('success', 'Sesi berhasil ditutup. QR presensi tidak dapat digunakan lagi.');
    }

    public function perizinan(Request $request): View
    {
        $status = $request->query('status', 'all');

        $query = IzinPresensi::with(['kegiatan', 'user', 'reviewer'])
            ->orderByDesc('created_at');

        if (in_array($status, [IzinPresensi::STATUS_MENUNGGU, IzinPresensi::STATUS_DISETUJUI, IzinPresensi::STATUS_DITOLAK], true)) {
            $query->where('status', $status);
        }

        $izins = $query->get();

        $counts = [
            'total' => IzinPresensi::count(),
            'menunggu' => IzinPresensi::where('status', IzinPresensi::STATUS_MENUNGGU)->count(),
            'disetujui' => IzinPresensi::where('status', IzinPresensi::STATUS_DISETUJUI)->count(),
            'ditolak' => IzinPresensi::where('status', IzinPresensi::STATUS_DITOLAK)->count(),
        ];

        return view('psdi.perizinan', [
            'izins' => $izins,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function reviewIzin(Request $request, IzinPresensi $izin): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:setujui,tolak'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['action'] === 'tolak' && empty(trim($data['catatan'] ?? ''))) {
            throw ValidationException::withMessages([
                'catatan' => 'Mohon sertakan alasan/catatan penolakan izin.',
            ]);
        }

        DB::transaction(function () use ($izin, $data, $request): void {
            $lockedIzin = IzinPresensi::lockForUpdate()->findOrFail($izin->id);

            if ($data['action'] === 'setujui') {
                $lockedIzin->update([
                    'status' => IzinPresensi::STATUS_DISETUJUI,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'catatan_reviewer' => $data['catatan'] ?? null,
                ]);

                // Buat atau update record presensi fungsionaris menjadi 'izin'
                PresensiKegiatan::updateOrCreate(
                    ['kegiatan_id' => $lockedIzin->kegiatan_id, 'user_id' => $lockedIzin->user_id],
                    [
                        'waktu_presensi' => now(),
                        'bukti_foto' => $lockedIzin->bukti_path,
                        'metode' => PresensiKegiatan::METHOD_MANUAL,
                        'status' => PresensiKegiatan::STATUS_IZIN,
                        'catatan' => 'Izin disetujui PSDI: ' . $lockedIzin->alasan,
                    ]
                );
            } else {
                $lockedIzin->update([
                    'status' => IzinPresensi::STATUS_DITOLAK,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'catatan_reviewer' => $data['catatan'],
                ]);

                // Jika ada record presensi izin, hapus
                PresensiKegiatan::where('kegiatan_id', $lockedIzin->kegiatan_id)
                    ->where('user_id', $lockedIzin->user_id)
                    ->where('status', PresensiKegiatan::STATUS_IZIN)
                    ->delete();
            }
        });

        $message = $data['action'] === 'setujui'
            ? 'Pengajuan izin fungsionaris berhasil disetujui.'
            : 'Pengajuan izin fungsionaris berhasil ditolak.';

        return redirect()->route('psdi.perizinan')->with('success', $message);
    }
}
