<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\PresensiKegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PsdiKegiatanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_psdi_can_create_an_activity_before_opening_its_attendance_session(): void
    {
        $psdi = User::factory()->create(['role' => 'psdi']);

        $this->actingAs($psdi)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('psdi.kegiatan.store'), [
                'nama' => 'Pelatihan Kepemimpinan',
                'tanggal' => '2026-11-20',
                'jenis' => 'Pelatihan',
                'jam_mulai' => '09:00',
                'jam_selesai' => '12:00',
            ])
            ->assertRedirect(route('psdi.kegiatan'))
            ->assertSessionHas('success');

        $kegiatan = Kegiatan::firstOrFail();
        $this->assertSame('Pelatihan Kepemimpinan', $kegiatan->nama);
        $this->assertSame('Pelatihan', $kegiatan->jenis);
        $this->assertSame(Kegiatan::STATUS_AKAN_DATANG, $kegiatan->status);
        $this->assertNotEmpty($kegiatan->qr_code);
        $this->assertSame($psdi->id, $kegiatan->created_by);

        $this->actingAs($psdi)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('psdi.kegiatan'))
            ->assertOk()
            ->assertSee('Pelatihan Kepemimpinan')
            ->assertSee('Buka Sesi Sekarang')
            ->assertSee('name="jam_mulai"', false)
            ->assertSee('name="jam_selesai"', false)
            ->assertDontSee('Lihat QR Presensi');
    }

    public function test_opening_a_session_activates_and_displays_its_qr_code(): void
    {
        $psdi = User::factory()->create(['role' => 'psdi']);
        $member = User::factory()->create(['role' => 'anggota']);
        $kegiatan = $this->createActivity();
        $inactiveCode = $kegiatan->qr_code;

        $this->actingAs($psdi)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('psdi.kegiatan.sesi.buka', $kegiatan))
            ->assertRedirect(route('psdi.kegiatan'))
            ->assertSessionHas('success')
            ->assertSessionHas('show_qr_for', $kegiatan->id);

        $kegiatan->refresh();
        $this->assertSame(Kegiatan::STATUS_BERLANGSUNG, $kegiatan->status);
        $this->assertNotSame($inactiveCode, $kegiatan->qr_code);

        $this->actingAs($psdi)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('psdi.kegiatan'))
            ->assertOk()
            ->assertSee('Lihat QR Presensi')
            ->assertSee($kegiatan->qr_code)
            ->assertSee('id="qrCanvas"', false);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.presensi-kegiatan'))
            ->assertOk()
            ->assertSee($kegiatan->qr_code);

        $this->post(route('anggota.presensi-kegiatan.store'), [
            'qr_code' => $kegiatan->qr_code,
            'bukti_foto' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertRedirect(route('anggota.presensi-kegiatan', ['tab' => 'riwayat']));

        $this->assertDatabaseHas('presensi_kegiatans', [
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $member->id,
            'status' => PresensiKegiatan::STATUS_HADIR,
        ]);
        $attendance = PresensiKegiatan::where('kegiatan_id', $kegiatan->id)->firstOrFail();
        $this->assertNotNull($attendance->waktu_presensi);
        Storage::disk('public')->assertExists($attendance->bukti_foto);
    }

    public function test_activity_end_time_must_be_after_its_start_time(): void
    {
        $psdi = User::factory()->create(['role' => 'psdi']);

        $this->actingAs($psdi)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('psdi.kegiatan.store'), [
                'nama' => 'Workshop',
                'tanggal' => '2026-11-20',
                'jenis' => 'Pelatihan',
                'jam_mulai' => '12:00',
                'jam_selesai' => '09:00',
            ])
            ->assertSessionHasErrors(['jam_selesai']);

        $this->assertDatabaseCount('kegiatans', 0);
    }

    public function test_member_cannot_attend_before_a_session_is_opened(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $kegiatan = $this->createActivity();

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => $kegiatan->qr_code,
                'bukti_foto' => UploadedFile::fake()->image('selfie.jpg'),
            ])
            ->assertSessionHasErrors(['qr_code']);

        $this->assertDatabaseCount('presensi_kegiatans', 0);
    }

    public function test_closing_a_session_invalidates_its_qr_for_attendance(): void
    {
        $psdi = User::factory()->create(['role' => 'psdi']);
        $member = User::factory()->create(['role' => 'anggota']);
        $kegiatan = $this->createActivity([
            'status' => Kegiatan::STATUS_BERLANGSUNG,
        ]);

        $this->actingAs($psdi)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('psdi.kegiatan.sesi.tutup', $kegiatan))
            ->assertRedirect(route('psdi.kegiatan'))
            ->assertSessionHas('success');

        $kegiatan->refresh();
        $this->assertSame(Kegiatan::STATUS_SELESAI, $kegiatan->status);
        $this->assertNotNull($kegiatan->qr_expires_at);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.presensi-kegiatan.store'), [
                'qr_code' => $kegiatan->qr_code,
                'bukti_foto' => UploadedFile::fake()->image('selfie.jpg'),
            ])
            ->assertSessionHasErrors(['qr_code']);

        $this->assertDatabaseCount('presensi_kegiatans', 0);
    }

    private function createActivity(array $overrides = []): Kegiatan
    {
        return Kegiatan::create([
            'nama' => 'Kegiatan Uji',
            'jenis' => 'Seminar',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_AKAN_DATANG,
            'qr_code' => 'KODE-UJI-'.uniqid(),
            ...$overrides,
        ]);
    }
}
