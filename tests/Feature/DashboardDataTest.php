<?php

namespace Tests\Feature;

use App\Models\CashTransaction;
use App\Models\Kegiatan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_keeps_demo_accounts_without_creating_sample_activities(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'claudio@bem-filkom.ub.ac.id']);
        $this->assertDatabaseCount('kegiatans', 0);
    }

    public function test_member_dashboard_shows_recorded_upcoming_activities_instead_of_sample_activities(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $this->createActivity('Kegiatan Terdaftar');

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.dashboard'))
            ->assertOk()
            ->assertSee('Kegiatan Terdaftar')
            ->assertDontSee('Pelatihan Public Speaking')
            ->assertDontSee('87%');
    }

    public function test_psdi_dashboard_shows_recorded_activities_and_empty_state_without_samples(): void
    {
        $psdi = User::factory()->create(['role' => 'psdi']);

        $this->actingAs($psdi)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('psdi.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada kegiatan.')
            ->assertDontSee('Seminar Kepemimpinan');

        $this->createActivity('Kegiatan PSDI');

        $this->actingAs($psdi)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('psdi.dashboard'))
            ->assertOk()
            ->assertSee('Kegiatan PSDI')
            ->assertDontSee('Workshop UI/UX Design');
    }

    public function test_bendahara_dashboard_uses_recorded_cash_transactions(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);
        CashTransaction::create([
            'user_id' => null,
            'created_by' => $bendahara->id,
            'type' => 'masuk',
            'transaction_date' => now()->toDateString(),
            'description' => 'Pemasukan tercatat',
            'amount' => 12345,
        ]);

        $this->actingAs($bendahara)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('bendahara.dashboard'))
            ->assertOk()
            ->assertSee('Pemasukan tercatat')
            ->assertSee('Rp12.345')
            ->assertDontSee('Rp28.450.000');
    }

    public function test_presbem_dashboard_and_profile_use_database_and_account_data(): void
    {
        $presbem = User::factory()->create([
            'role' => 'presbem',
            'name' => 'Presbem Aktif',
        ]);
        $this->createActivity('Agenda Presbem');

        $this->actingAs($presbem)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('presbem.dashboard'))
            ->assertOk()
            ->assertSee('Agenda Presbem')
            ->assertDontSee('Rp28.450.000')
            ->assertDontSee('Seminar Kepemimpinan');

        $this->actingAs($presbem)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('presbem.profile'))
            ->assertOk()
            ->assertSee('Presbem Aktif')
            ->assertDontSee('M. Daffa Riyadlussalam');
    }

    private function createActivity(string $name): Kegiatan
    {
        return Kegiatan::create([
            'nama' => $name,
            'jenis' => 'Workshop',
            'tanggal' => now('Asia/Jakarta')->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'lokasi' => 'Aula',
            'status' => Kegiatan::STATUS_AKAN_DATANG,
            'qr_code' => 'KODE-UJI-'.uniqid(),
        ]);
    }
}
