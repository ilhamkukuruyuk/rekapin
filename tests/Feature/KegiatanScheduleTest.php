<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-20 08:59:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_session_starts_automatically_at_the_scheduled_start_time(): void
    {
        $kegiatan = $this->createActivity();
        $inactiveQrCode = $kegiatan->qr_code;
        Carbon::setTestNow(Carbon::parse('2026-11-20 09:00:00', 'Asia/Jakarta'));

        $this->artisan('kegiatan:sync-sessions')->assertSuccessful();

        $kegiatan->refresh();
        $this->assertSame(Kegiatan::STATUS_BERLANGSUNG, $kegiatan->status);
        $this->assertNotSame($inactiveQrCode, $kegiatan->qr_code);
        $this->assertNull($kegiatan->qr_expires_at);
    }

    public function test_session_ends_automatically_at_the_scheduled_end_time(): void
    {
        $kegiatan = $this->createActivity([
            'status' => Kegiatan::STATUS_BERLANGSUNG,
        ]);
        Carbon::setTestNow(Carbon::parse('2026-11-20 12:00:00', 'Asia/Jakarta'));

        $this->artisan('kegiatan:sync-sessions')->assertSuccessful();

        $kegiatan->refresh();
        $this->assertSame(Kegiatan::STATUS_SELESAI, $kegiatan->status);
        $this->assertNotNull($kegiatan->qr_expires_at);
    }

    public function test_missed_session_is_marked_finished_without_activating_its_qr(): void
    {
        $kegiatan = $this->createActivity();
        $inactiveQrCode = $kegiatan->qr_code;
        Carbon::setTestNow(Carbon::parse('2026-11-20 12:01:00', 'Asia/Jakarta'));

        $this->artisan('kegiatan:sync-sessions')->assertSuccessful();

        $kegiatan->refresh();
        $this->assertSame(Kegiatan::STATUS_SELESAI, $kegiatan->status);
        $this->assertSame($inactiveQrCode, $kegiatan->qr_code);
        $this->assertNotNull($kegiatan->qr_expires_at);
    }

    private function createActivity(array $overrides = []): Kegiatan
    {
        return Kegiatan::create([
            'nama' => 'Kegiatan Terjadwal',
            'jenis' => 'Workshop',
            'tanggal' => '2026-11-20',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_AKAN_DATANG,
            'qr_code' => 'KODE-UJI-'.uniqid(),
            ...$overrides,
        ]);
    }
}
