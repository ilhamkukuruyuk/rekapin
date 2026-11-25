<?php

namespace Tests\Feature;

use App\Models\CashPayment;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_presbem_can_view_database_backed_cash_summary_and_member_payment_matrix(): void
    {
        $presbem = User::factory()->create(['role' => 'presbem']);
        $member = User::factory()->create([
            'role' => 'anggota',
            'name' => 'Anggota Pelapor',
            'nim' => '22001',
        ]);
        CashTransaction::create([
            'created_by' => $presbem->id,
            'type' => 'masuk',
            'transaction_date' => now()->toDateString(),
            'description' => 'Iuran tervalidasi',
            'amount' => 50000,
        ]);
        CashTransaction::create([
            'created_by' => $presbem->id,
            'type' => 'keluar',
            'transaction_date' => now()->toDateString(),
            'description' => 'Pembelian perlengkapan',
            'amount' => 10000,
        ]);
        CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth()->toDateString(),
            'amount' => 50000,
            'proof_path' => 'cash-payment-proofs/proof.jpg',
            'status' => CashPayment::STATUS_APPROVED,
        ]);

        $this->actingAs($presbem)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('presbem.rekap-kas'))
            ->assertOk()
            ->assertSee('Rp50.000')
            ->assertSee('Rp10.000')
            ->assertSee('Rp40.000')
            ->assertSee('Anggota Pelapor')
            ->assertSee('Lunas')
            ->assertSee('Cetak / Simpan PDF')
            ->assertSee(route('presbem.rekap-kas.export'));
    }

    public function test_authorized_cash_roles_can_export_and_members_cannot_view_the_report(): void
    {
        $treasurer = User::factory()->create(['role' => 'adkeu']);
        CashTransaction::create([
            'created_by' => $treasurer->id,
            'type' => 'masuk',
            'transaction_date' => now()->toDateString(),
            'description' => '=SUM(1,1)',
            'amount' => 75000,
        ]);
        $member = User::factory()->create(['role' => 'anggota']);
        CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth()->toDateString(),
            'amount' => 50000,
            'proof_path' => 'cash-payment-proofs/approved.jpg',
            'status' => CashPayment::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('bendahara.rekap-kas.export'))
            ->assertOk()
            ->assertDownload();
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=SUM(1,1)", $csv);
        $this->assertStringContainsString('"Total Pemasukan",75000', $csv);
        $this->assertStringContainsString('Status Pembayaran Kas', $csv);
        $this->assertStringContainsString($member->name, $csv);
        $this->assertStringContainsString('Lunas', $csv);

        $member = User::factory()->create(['role' => 'anggota']);
        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('presbem.rekap-kas'))
            ->assertRedirect(route('anggota.dashboard'));
    }
}
