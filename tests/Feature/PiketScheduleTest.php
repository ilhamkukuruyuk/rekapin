<?php

namespace Tests\Feature;

use App\Models\PiketSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PiketScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_bendahara_can_create_a_schedule_for_a_member(): void
    {
        $manager = User::factory()->create(['role' => 'bendahara']);
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($manager)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('bendahara.jadwal-piket.store'), [
                'user_id' => $member->id,
                'date' => now()->addDay()->toDateString(),
                'start_time' => '08:00',
                'end_time' => '10:00',
                'location' => 'Sekretariat BEM',
            ])
            ->assertRedirect(route('bendahara.jadwal-piket'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('piket_schedules', [
            'user_id' => $member->id,
            'created_by' => $manager->id,
            'location' => 'Sekretariat BEM',
        ]);
    }

    public function test_schedule_creation_rejects_overlapping_shifts_and_non_member_accounts(): void
    {
        $manager = User::factory()->create(['role' => 'adkeu']);
        $member = User::factory()->create(['role' => 'anggota']);
        $schedule = PiketSchedule::create([
            'user_id' => $member->id,
            'date' => now()->addDay(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'location' => 'Sekretariat BEM',
        ]);

        $this->actingAs($manager)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->from(route('bendahara.jadwal-piket'))
            ->post(route('bendahara.jadwal-piket.store'), [
                'user_id' => $member->id,
                'date' => $schedule->date->toDateString(),
                'start_time' => '09:30',
                'end_time' => '11:00',
                'location' => 'Sekretariat BEM',
            ])
            ->assertRedirect(route('bendahara.jadwal-piket'))
            ->assertSessionHasErrors('user_id');

        $nonMember = User::factory()->create(['role' => 'psdi']);
        $this->post(route('bendahara.jadwal-piket.store'), [
            'user_id' => $nonMember->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '15:00',
            'location' => 'Sekretariat BEM',
        ])->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('piket_schedules', 1);
    }

    public function test_member_sees_only_own_schedules_and_can_attend_during_the_shift(): void
    {
        Carbon::setTestNow('2026-11-09 09:00:00');
        $member = User::factory()->create(['role' => 'anggota']);
        $otherMember = User::factory()->create(['role' => 'anggota']);
        $schedule = PiketSchedule::create([
            'user_id' => $member->id,
            'date' => '2026-11-09',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'location' => 'Sekretariat BEM',
        ]);
        PiketSchedule::create([
            'user_id' => $otherMember->id,
            'date' => '2026-11-09',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'location' => 'Sekretariat BEM',
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.jadwal-piket'))
            ->assertOk()
            ->assertSee('Presensi Sekarang')
            ->assertSee('Sekretariat BEM')
            ->assertDontSee($otherMember->name);

        $this->post(route('anggota.jadwal-piket.presensi', $schedule), [
            'proof' => UploadedFile::fake()->image('piket.jpg'),
            'attendance_note' => 'Shift pagi',
        ])->assertRedirect(route('anggota.jadwal-piket'))
            ->assertSessionHas('success');

        $schedule->refresh();
        $this->assertSame(PiketSchedule::STATUS_HADIR, $schedule->attendance_status);
        $this->assertNotNull($schedule->attended_at);
        Storage::disk('local')->assertExists($schedule->proof_path);

        $this->get(route('anggota.jadwal-piket.bukti', $schedule))->assertOk();
    }

    public function test_member_cannot_attend_outside_shift_or_another_members_schedule(): void
    {
        Carbon::setTestNow('2026-11-09 07:59:00');
        $member = User::factory()->create(['role' => 'anggota']);
        $otherMember = User::factory()->create(['role' => 'anggota']);
        $schedule = PiketSchedule::create([
            'user_id' => $member->id,
            'date' => '2026-11-09',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'location' => 'Sekretariat BEM',
        ]);
        $otherSchedule = PiketSchedule::create([
            'user_id' => $otherMember->id,
            'date' => '2026-11-09',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'location' => 'Sekretariat BEM',
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->from(route('anggota.jadwal-piket'))
            ->post(route('anggota.jadwal-piket.presensi', $schedule), [
                'proof' => UploadedFile::fake()->image('piket.jpg'),
            ])
            ->assertSessionHasErrors('proof');

        $this->post(route('anggota.jadwal-piket.presensi', $otherSchedule), [
            'proof' => UploadedFile::fake()->image('piket.jpg'),
        ])->assertNotFound();

        $this->assertNull($schedule->fresh()->attendance_status);
    }
}
