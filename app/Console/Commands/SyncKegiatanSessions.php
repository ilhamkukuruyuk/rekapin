<?php

namespace App\Console\Commands;

use App\Models\Kegiatan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncKegiatanSessions extends Command
{
    protected $signature = 'kegiatan:sync-sessions';

    protected $description = 'Open and close activity attendance sessions according to their WIB schedule';

    public function handle(): int
    {
        $now = Carbon::now('Asia/Jakarta');
        $started = 0;
        $finished = 0;

        $kegiatanIds = Kegiatan::query()
            ->whereIn('status', [Kegiatan::STATUS_AKAN_DATANG, Kegiatan::STATUS_BERLANGSUNG])
            ->whereDate('tanggal', '<=', $now->toDateString())
            ->whereNotNull('jam_mulai')
            ->whereNotNull('jam_selesai')
            ->pluck('id');

        foreach ($kegiatanIds as $kegiatanId) {
            DB::transaction(function () use ($kegiatanId, $now, &$started, &$finished): void {
                $kegiatan = Kegiatan::query()->lockForUpdate()->find($kegiatanId);

                if (! $kegiatan || ! in_array($kegiatan->status, [
                    Kegiatan::STATUS_AKAN_DATANG,
                    Kegiatan::STATUS_BERLANGSUNG,
                ], true)) {
                    return;
                }

                $tanggal = $kegiatan->tanggal->toDateString();
                $jamMulai = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$kegiatan->jam_mulai}", 'Asia/Jakarta');
                $jamSelesai = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$kegiatan->jam_selesai}", 'Asia/Jakarta');

                if ($now->lt($jamMulai)) {
                    return;
                }

                if ($now->greaterThanOrEqualTo($jamSelesai)) {
                    $kegiatan->update([
                        'status' => Kegiatan::STATUS_SELESAI,
                        'qr_expires_at' => $kegiatan->qr_expires_at ?? now(),
                    ]);
                    $finished++;

                    return;
                }

                if ($kegiatan->status === Kegiatan::STATUS_AKAN_DATANG) {
                    $kegiatan->update([
                        'status' => Kegiatan::STATUS_BERLANGSUNG,
                        'qr_code' => Str::random(48),
                        'qr_expires_at' => null,
                    ]);
                    $started++;
                }
            });
        }

        $this->info("Sesi dibuka: {$started}; sesi diselesaikan: {$finished}.");

        return self::SUCCESS;
    }
}
