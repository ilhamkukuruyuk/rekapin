<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\PresensiKegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PresensiRekapController extends Controller
{
    private const ROLE_FUNGSIONARIS = ['anggota', 'bendahara', 'adkeu', 'psdi', 'presbem'];

    public function index(Request $request): View
    {
        return view('psdi.rekap-presensi', $this->reportData() + [
            'layout' => match (true) {
                $request->routeIs('presbem.*') => 'layouts.presbem',
                $request->routeIs('bendahara.*') => 'layouts.bendahara',
                default => 'layouts.psdi',
            },
            'exportRoute' => match (true) {
                $request->routeIs('presbem.*') => 'presbem.rekap-presensi.export',
                $request->routeIs('bendahara.*') => 'bendahara.rekap-presensi.export',
                default => 'psdi.rekap-presensi.export',
            },
        ]);
    }

    public function export(): StreamedResponse
    {
        $report = $this->reportData();
        $filename = 'rekap-presensi-bem-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.csv';

        return Response::streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Gagal membuka aliran berkas rekap presensi.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Nama',
                'NIM',
                'Email',
                'Departemen',
                'Kegiatan',
                'Tanggal Kegiatan',
                'Status Presensi',
                'Waktu Presensi (WIB)',
                'Metode',
                'Persentase Kehadiran Anggota',
                'Kelengkapan Rekap (%)',
            ]);

            foreach ($report['kegiatan'] as $kegiatan) {
                foreach ($report['anggota'] as $anggota) {
                    $presensi = $kegiatan->presensis->firstWhere('user_id', $anggota->id);
                    $status = $this->attendanceStatus($kegiatan, $presensi);
                    $waktu = $presensi?->waktu_presensi
                        ?->copy()
                        ->timezone('Asia/Jakarta')
                        ->format('Y-m-d H:i:s');

                    fputcsv($output, array_map(
                        $this->csvSafeValue(...),
                        [
                            $anggota->name,
                            $anggota->nim,
                            $anggota->email,
                            $anggota->departemen,
                            $kegiatan->nama,
                            $kegiatan->tanggal->format('Y-m-d'),
                            $status,
                            $waktu,
                            $presensi?->metode === PresensiKegiatan::METHOD_QR ? 'QR Code' : ($presensi ? 'Manual' : ''),
                            $report['ringkasan'][$anggota->id]['persentase'].'%',
                            $kegiatan->status === Kegiatan::STATUS_SELESAI ? '100%' : '',
                        ],
                    ));
                }
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{
     *     kegiatan: Collection<int, Kegiatan>,
     *     anggota: Collection<int, User>,
     *     ringkasan: array<int, array{hadir: int, izin: int, alpha: int, persentase: int}>,
     *     ringkasanUmum: array{totalHadir: int, totalIzin: int, totalAlpha: int, rataRata: int, diBawah75: int, kelengkapan: int, totalStatus: int, totalKegiatanSelesai: int}
     * }
     */
    private function reportData(): array
    {
        $anggota = User::query()
            ->whereIn('role', self::ROLE_FUNGSIONARIS)
            ->orderBy('name')
            ->get();

        $kegiatan = Kegiatan::query()
            ->with(['presensis' => fn ($query) => $query
                ->whereHas('user', fn ($userQuery) => $userQuery->whereIn('role', self::ROLE_FUNGSIONARIS))
                ->select([
                    'id',
                    'kegiatan_id',
                    'user_id',
                    'waktu_presensi',
                    'metode',
                    'status',
                ])])
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->get();

        $kegiatanSelesai = $kegiatan->where('status', Kegiatan::STATUS_SELESAI);
        $grafikKegiatan = $kegiatanSelesai->take(12)->reverse()->values();
        $ringkasan = [];
        foreach ($anggota as $user) {
            $hadir = 0;
            $izin = 0;
            $alpha = 0;

            foreach ($kegiatanSelesai as $event) {
                match ($this->attendanceStatus($event, $event->presensis->firstWhere('user_id', $user->id))) {
                    'Hadir' => $hadir++,
                    'Izin' => $izin++,
                    'Alpha' => $alpha++,
                };
            }

            $ringkasan[$user->id] = [
                'hadir' => $hadir,
                'izin' => $izin,
                'alpha' => $alpha,
                'persentase' => $kegiatanSelesai->isEmpty()
                    ? 0
                    : (int) round($hadir / $kegiatanSelesai->count() * 100),
            ];
        }

        $totalStatus = $anggota->count() * $kegiatanSelesai->count();
        $totalHadir = array_sum(array_column($ringkasan, 'hadir'));
        $totalIzin = array_sum(array_column($ringkasan, 'izin'));
        $totalAlpha = array_sum(array_column($ringkasan, 'alpha'));

        return [
            'kegiatan' => $kegiatan,
            'anggota' => $anggota,
            'ringkasan' => $ringkasan,
            'ringkasanUmum' => [
                'totalHadir' => $totalHadir,
                'totalIzin' => $totalIzin,
                'totalAlpha' => $totalAlpha,
                'rataRata' => $anggota->isEmpty() || $kegiatanSelesai->isEmpty()
                    ? 0
                    : (int) round($totalHadir / $totalStatus * 100),
                'diBawah75' => collect($ringkasan)->where('persentase', '<', 75)->count(),
                'kelengkapan' => $totalStatus === 0
                    ? 0
                    : (int) round(($totalHadir + $totalIzin + $totalAlpha) / $totalStatus * 100),
                'totalStatus' => $totalStatus,
                'totalKegiatanSelesai' => $kegiatanSelesai->count(),
            ],
            'grafik' => [
                'labels' => $grafikKegiatan->map(fn (Kegiatan $event): string => $event->nama)->all(),
                'hadir' => $grafikKegiatan->map(fn (Kegiatan $event): int => $event->presensis
                    ->where('status', PresensiKegiatan::STATUS_HADIR)
                    ->count())->all(),
                'izin' => $grafikKegiatan->map(fn (Kegiatan $event): int => $event->presensis
                    ->where('status', PresensiKegiatan::STATUS_IZIN)
                    ->count())->all(),
                'alpha' => $grafikKegiatan->map(fn (Kegiatan $event): int => max(
                    0,
                    $anggota->count()
                        - $event->presensis->whereIn('status', [
                            PresensiKegiatan::STATUS_HADIR,
                            PresensiKegiatan::STATUS_IZIN,
                        ])->count()
                ))->all(),
            ],
        ];
    }

    private function attendanceStatus(Kegiatan $kegiatan, ?PresensiKegiatan $presensi): string
    {
        if ($presensi?->status === PresensiKegiatan::STATUS_HADIR) {
            return 'Hadir';
        }

        if ($presensi?->status === PresensiKegiatan::STATUS_IZIN) {
            return 'Izin';
        }

        if ($presensi?->status === PresensiKegiatan::STATUS_TIDAK_HADIR) {
            return 'Alpha';
        }

        if ($kegiatan->status === Kegiatan::STATUS_SELESAI) {
            return 'Alpha';
        }

        return 'Belum presensi';
    }

    private function csvSafeValue(?string $value): string
    {
        $value = $value ?? '';

        return preg_match('/^[=+\-@\t\r]/', $value) === 1
            ? "'".$value
            : $value;
    }
}
