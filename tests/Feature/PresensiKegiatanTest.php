<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\PresensiKegiatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresensiKegiatanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_member_can_view_presensi_kegiatan_page_with_active_events(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $kegiatan = Kegiatan::create([
            'nama' => 'Seminar Kepemimpinan BEM',
            'deskripsi' => 'Pengembangan jiwa kepemimpinan mahasiswa',
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'lokasi' => 'Aula FILKOM',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'SEMINAR-KPM-2025',
            'qr_expires_at' => now()->addHours(3),
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.presensi-kegiatan'))
            ->assertOk()
            ->assertSee('Presensi Kegiatan')
            ->assertSee('Seminar Kepemimpinan BEM')
            ->assertSee('SEMINAR-KPM-2025')
            ->assertSee('Foto Selfie Bukti Kehadiran');
    }

    public function test_member_can_record_attendance_with_valid_qr_and_uploaded_selfie_photo(): void
    {
        Carbon::setTestNow('2026-10-28 10:15:30');

        $member = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'anggota',
        ]);

        $kegiatan = Kegiatan::create([
            'nama' => 'Workshop UI/UX Design',
            'deskripsi' => 'Pelatihan riset dan desain antarmuka',
            'tanggal' => '2026-10-28',
            'jam_mulai' => '10:00',
            'jam_selesai' => '13:00',
            'lokasi' => 'Lab Komputer 301',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'WORKSHOP-UIUX-2026',
            'qr_expires_at' => now()->addHours(2),
        ]);

        $selfie = UploadedFile::fake()->image('selfie_budi.jpg', 640, 480);

        $response = $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'WORKSHOP-UIUX-2026',
                'bukti_foto' => $selfie,
            ]);

        $response->assertRedirect(route('anggota.presensi-kegiatan', ['tab' => 'riwayat']));
        $response->assertSessionHas('success');

        // Verify Acceptance Criteria 1: Presensi tersimpan ke database
        $this->assertDatabaseHas('presensi_kegiatans', [
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $member->id,
            'status' => PresensiKegiatan::STATUS_HADIR,
            'metode' => PresensiKegiatan::METHOD_QR,
        ]);

        // Verify Acceptance Criteria 2: Menangkap waktu absensi dan bukti foto
        $attendance = PresensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('user_id', $member->id)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('2026-10-28 10:15:30', $attendance->waktu_presensi->format('Y-m-d H:i:s'));
        $this->assertNotNull($attendance->bukti_foto);
        Storage::disk('public')->assertExists($attendance->bukti_foto);
    }

    public function test_member_can_record_attendance_with_webcam_base64_photo(): void
    {
        Carbon::setTestNow('2026-10-28 14:00:00');

        $member = User::factory()->create(['role' => 'anggota']);

        $kegiatan = Kegiatan::create([
            'nama' => 'Pelatihan Public Speaking',
            'tanggal' => '2026-10-28',
            'lokasi' => 'Gedung F',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'SPEAKING-2026',
        ]);

        $selfie = UploadedFile::fake()->image('selfie.png', 1, 1);
        $fakeBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($selfie->getRealPath()));

        $response = $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'SPEAKING-2026',
                'bukti_foto_base64' => $fakeBase64,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $attendance = PresensiKegiatan::first();
        $this->assertNotNull($attendance);
        $this->assertEquals($member->id, $attendance->user_id);
        $this->assertEquals('2026-10-28 14:00:00', $attendance->waktu_presensi->format('Y-m-d H:i:s'));
        Storage::disk('public')->assertExists($attendance->bukti_foto);
    }

    public function test_attendance_rejects_invalid_base64_photo_data(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        Kegiatan::create([
            'nama' => 'Seminar BEM',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'SEMINAR-INVALID-IMAGE',
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'SEMINAR-INVALID-IMAGE',
                'bukti_foto_base64' => 'data:image/png;base64,not-an-image',
            ])
            ->assertSessionHasErrors(['bukti_foto']);

        $this->assertDatabaseCount('presensi_kegiatans', 0);
    }

    public function test_attendance_requires_valid_qr_code(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $selfie = UploadedFile::fake()->image('selfie.jpg');

        $response = $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'QR-TIDAK-VALID-123',
                'bukti_foto' => $selfie,
            ]);

        $response->assertSessionHasErrors(['qr_code']);
        $this->assertDatabaseCount('presensi_kegiatans', 0);
    }

    public function test_attendance_requires_selfie_photo(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        Kegiatan::create([
            'nama' => 'Rapat Pleno BEM',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Sekretariat BEM',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'PLENO-2026',
        ]);

        $response = $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'PLENO-2026',
                // Bukti foto dikosongkan
            ]);

        $response->assertSessionHasErrors(['bukti_foto']);
        $this->assertDatabaseCount('presensi_kegiatans', 0);
    }

    public function test_member_cannot_submit_duplicate_attendance_for_same_event(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $kegiatan = Kegiatan::create([
            'nama' => 'FGD Roadmap BEM 2026',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Ruang Rapat',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'FGD-2026',
        ]);

        $selfie1 = UploadedFile::fake()->image('selfie1.jpg');
        $selfie2 = UploadedFile::fake()->image('selfie2.jpg');

        // Submission 1: Berhasil
        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'FGD-2026',
                'bukti_foto' => $selfie1,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('presensi_kegiatans', 1);

        // Submission 2: Harus ditolak
        $response = $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'FGD-2026',
                'bukti_foto' => $selfie2,
            ]);

        $response->assertSessionHasErrors(['qr_code']);
        $this->assertDatabaseCount('presensi_kegiatans', 1);
    }

    public function test_cannot_record_attendance_for_completed_event(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        Kegiatan::create([
            'nama' => 'Acara Masa Lalu',
            'tanggal' => now()->subDays(5)->toDateString(),
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_SELESAI,
            'qr_code' => 'SELESAI-2026',
        ]);

        $response = $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'SELESAI-2026',
                'bukti_foto' => UploadedFile::fake()->image('selfie.jpg'),
            ]);

        $response->assertSessionHasErrors(['qr_code']);
        $this->assertDatabaseCount('presensi_kegiatans', 0);
    }

    public function test_cannot_record_attendance_if_qr_is_expired(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        Kegiatan::create([
            'nama' => 'Acara Rolling QR',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'ROLLING-QR-EXPIRED',
            'qr_expires_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => 'ROLLING-QR-EXPIRED',
                'bukti_foto' => UploadedFile::fake()->image('selfie.jpg'),
            ]);

        $response->assertSessionHasErrors(['qr_code']);
        $this->assertDatabaseCount('presensi_kegiatans', 0);
    }

    public function test_member_can_view_attendance_history_in_riwayat_presensi(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $kegiatan = Kegiatan::create([
            'nama' => 'Seminar Integritas Akademik',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Gedung A',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'INTEGRITAS-2026',
        ]);

        $attendance = PresensiKegiatan::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $member->id,
            'waktu_presensi' => now(),
            'bukti_foto' => 'presensi-bukti/selfie_dummy.jpg',
            'metode' => PresensiKegiatan::METHOD_QR,
            'status' => PresensiKegiatan::STATUS_HADIR,
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.riwayat-presensi'))
            ->assertOk()
            ->assertSee('Seminar Integritas Akademik')
            ->assertSee('Hadir')
            ->assertSee('Lihat Foto');
    }

    public function test_member_sees_all_event_attendance_statuses_and_personal_attendance_rate(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $activities = collect([
            [
                'nama' => 'Agenda Hadir',
                'status' => Kegiatan::STATUS_SELESAI,
                'qr_code' => 'HISTORY-HADIR',
                'attendance_status' => PresensiKegiatan::STATUS_HADIR,
            ],
            [
                'nama' => 'Agenda Izin',
                'status' => Kegiatan::STATUS_SELESAI,
                'qr_code' => 'HISTORY-IZIN',
                'attendance_status' => PresensiKegiatan::STATUS_IZIN,
            ],
            [
                'nama' => 'Agenda Alpha',
                'status' => Kegiatan::STATUS_SELESAI,
                'qr_code' => 'HISTORY-ALPHA',
                'attendance_status' => null,
            ],
            [
                'nama' => 'Agenda Mendatang',
                'status' => Kegiatan::STATUS_AKAN_DATANG,
                'qr_code' => 'HISTORY-UPCOMING',
                'attendance_status' => null,
            ],
        ])->map(function (array $data) use ($member): Kegiatan {
            $attendanceStatus = $data['attendance_status'];
            unset($data['attendance_status']);

            $activity = Kegiatan::create([
                'tanggal' => now()->toDateString(),
                'lokasi' => 'Aula BEM',
                ...$data,
            ]);

            if ($attendanceStatus !== null) {
                PresensiKegiatan::create([
                    'kegiatan_id' => $activity->id,
                    'user_id' => $member->id,
                    'waktu_presensi' => now(),
                    'bukti_foto' => 'presensi-bukti/' . strtolower($attendanceStatus) . '.jpg',
                    'metode' => PresensiKegiatan::METHOD_MANUAL,
                    'status' => $attendanceStatus,
                ]);
            }

            return $activity;
        });

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.riwayat-presensi'))
            ->assertOk()
            ->assertSee('Agenda Hadir')
            ->assertSee('Agenda Izin')
            ->assertSee('Agenda Alpha')
            ->assertSee('Agenda Mendatang')
            ->assertSee('Hadir')
            ->assertSee('Izin')
            ->assertSee('Alpha')
            ->assertSee('Belum presensi')
            ->assertSee('33%')
            ->assertSee('3 kegiatan selesai');
    }

    public function test_presbem_can_view_event_attendance_history(): void
    {
        $presbem = User::factory()->create(['role' => 'presbem']);

        Kegiatan::create([
            'nama' => 'Agenda PresBEM Selesai',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Aula BEM',
            'status' => Kegiatan::STATUS_SELESAI,
            'qr_code' => 'PRESBEM-HISTORY',
        ]);

        $this->actingAs($presbem)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('presbem.riwayat-presensi'))
            ->assertOk()
            ->assertSee('Agenda PresBEM Selesai')
            ->assertSee('Alpha')
            ->assertSee('Kehadiran (1 kegiatan selesai)');
    }

    public function test_presbem_can_also_record_attendance(): void
    {
        $presbem = User::factory()->create(['role' => 'presbem']);

        Kegiatan::create([
            'nama' => 'Konsolidasi Akbar',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Gazebo FILKOM',
            'status' => Kegiatan::STATUS_BERLANGSUNG,
            'qr_code' => 'KONSOLIDASI-2026',
        ]);

        $response = $this->actingAs($presbem)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('presbem.presensi-kegiatan.store'), [
                'qr_code' => 'KONSOLIDASI-2026',
                'bukti_foto' => UploadedFile::fake()->image('presbem_selfie.png'),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('presensi_kegiatans', [
            'user_id' => $presbem->id,
            'status' => PresensiKegiatan::STATUS_HADIR,
        ]);
    }

    public function test_guest_cannot_access_or_submit_presensi(): void
    {
        $this->get(route('anggota.presensi-kegiatan'))
            ->assertRedirect(route('login'));

        $this->post(route('anggota.presensi-kegiatan.store'), [
            'qr_code' => 'TEST',
        ])->assertRedirect(route('login'));
    }
}
