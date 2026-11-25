<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\PresensiKegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiRekapTest extends TestCase
{
    use RefreshDatabase;

    public function test_psdi_can_view_centralized_attendance_statistics_with_complete_status_coverage(): void
    {
        $hadir = User::factory()->create([
            'name' => 'Anggota Hadir',
            'nim' => '1001',
            'role' => 'anggota',
        ]);
        $izin = User::factory()->create([
            'name' => 'Anggota Izin',
            'nim' => '1002',
            'role' => 'presbem',
        ]);
        User::factory()->create(['role' => 'admin']);
        $organizer = User::factory()->create(['role' => 'psdi']);

        $rapat = Kegiatan::create([
            'nama' => 'Rapat Selesai',
            'tanggal' => now()->subDay()->toDateString(),
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_SELESAI,
            'qr_code' => 'REKAP-RAPAT',
        ]);
        $seminar = Kegiatan::create([
            'nama' => 'Seminar Selesai',
            'tanggal' => now()->subDays(2)->toDateString(),
            'lokasi' => 'Gedung A',
            'status' => Kegiatan::STATUS_SELESAI,
            'qr_code' => 'REKAP-SEMINAR',
        ]);
        Kegiatan::create([
            'nama' => 'Workshop Berlangsung',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Lab',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'REKAP-WORKSHOP',
        ]);

        PresensiKegiatan::create([
            'kegiatan_id' => $rapat->id,
            'user_id' => $hadir->id,
            'waktu_presensi' => now()->subDay(),
            'bukti_foto' => 'presensi-bukti/hadir.jpg',
            'status' => PresensiKegiatan::STATUS_HADIR,
            'metode' => PresensiKegiatan::METHOD_QR,
        ]);
        PresensiKegiatan::create([
            'kegiatan_id' => $rapat->id,
            'user_id' => $izin->id,
            'waktu_presensi' => now()->subDay(),
            'bukti_foto' => 'presensi-bukti/izin.jpg',
            'status' => PresensiKegiatan::STATUS_IZIN,
            'metode' => PresensiKegiatan::METHOD_MANUAL,
        ]);

        $this->actingAs($organizer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('psdi.rekap-presensi'))
            ->assertOk()
            ->assertSee('Anggota Hadir')
            ->assertSee('Anggota Izin')
            ->assertSee('Rapat Selesai')
            ->assertSee('Seminar Selesai')
            ->assertSee('Workshop Berlangsung')
            ->assertSee('17%')
            ->assertSee('100%')
            ->assertSee('6 status dievaluasi dari 2 agenda selesai.');
    }

    public function test_psdi_can_download_csv_attendance_report_with_inferred_alpha(): void
    {
        $member = User::factory()->create([
            'name' => 'Anggota Rekap',
            'nim' => '22001',
            'role' => 'anggota',
        ]);
        $organizer = User::factory()->create(['role' => 'psdi']);
        $event = Kegiatan::create([
            'nama' => 'Kegiatan Export',
            'tanggal' => now()->subDay()->toDateString(),
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_SELESAI,
            'qr_code' => 'REKAP-EXPORT',
        ]);

        $response = $this->actingAs($organizer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('psdi.rekap-presensi.export'));

        $response->assertOk()
            ->assertDownload();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Anggota Rekap', $csv);
        $this->assertStringContainsString('Kegiatan Export', $csv);
        $this->assertStringContainsString('Alpha', $csv);
        $this->assertStringContainsString('100%', $csv);
        $this->assertStringContainsString('22001', $csv);
    }

    public function test_non_psdi_user_cannot_access_centralized_attendance_report(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('psdi.rekap-presensi'))
            ->assertRedirect(route('anggota.dashboard'));
    }

    public function test_presbem_can_view_live_attendance_report_and_export_it(): void
    {
        $presbem = User::factory()->create(['role' => 'presbem']);
        $member = User::factory()->create([
            'role' => 'anggota',
            'name' => 'Anggota Evaluasi',
            'nim' => '22002',
        ]);
        $event = Kegiatan::create([
            'nama' => 'Evaluasi Organisasi',
            'tanggal' => now()->subDay()->toDateString(),
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_SELESAI,
            'qr_code' => 'REKAP-PRESBEM',
        ]);
        PresensiKegiatan::create([
            'kegiatan_id' => $event->id,
            'user_id' => $member->id,
            'waktu_presensi' => now()->subDay(),
            'bukti_foto' => 'presensi-bukti/evaluasi.jpg',
            'status' => PresensiKegiatan::STATUS_HADIR,
            'metode' => PresensiKegiatan::METHOD_QR,
        ]);

        $this->actingAs($presbem)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('presbem.rekap-presensi'))
            ->assertOk()
            ->assertSee('Evaluasi Organisasi')
            ->assertSee('Anggota Evaluasi')
            ->assertSee('Kehadiran per Kegiatan')
            ->assertSee('Cetak / Simpan PDF')
            ->assertSee(route('presbem.rekap-presensi.export'));

        $response = $this->get(route('presbem.rekap-presensi.export'))
            ->assertOk()
            ->assertDownload();
        $this->assertStringContainsString('Evaluasi Organisasi', $response->streamedContent());
    }
}
