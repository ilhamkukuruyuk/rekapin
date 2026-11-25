<?php

namespace Tests\Feature;

use App\Models\CashPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashPaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_member_can_submit_a_transfer_proof_for_monthly_dues(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => [now()->format('Y-m')],
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'))
            ->assertSessionHas('success');

        $payment = CashPayment::firstOrFail();
        $this->assertSame($member->id, $payment->user_id);
        $this->assertSame(CashPayment::AMOUNT, $payment->amount);
        $this->assertSame(CashPayment::STATUS_PENDING, $payment->status);
        $this->assertNotNull($payment->submitted_at);
        Storage::disk('local')->assertExists($payment->proof_path);
    }

    public function test_member_can_submit_one_proof_for_multiple_months_and_each_month_is_reviewed_separately(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $treasurer = User::factory()->create(['role' => 'adkeu']);
        $periods = [
            now()->startOfMonth()->subMonth()->format('Y-m'),
            now()->format('Y-m'),
        ];

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.bayar-kas'))
            ->assertOk()
            ->assertSee('periods[]')
            ->assertSee('Tambah bulan')
            ->assertSee('Satu bukti transfer untuk semua periode')
            ->assertSee('periodList.append', false);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => $periods,
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'))
            ->assertSessionHas('success', 'Bukti pembayaran untuk 2 periode berhasil dikirim untuk diverifikasi ADKEU.');

        $payments = CashPayment::query()->where('user_id', $member->id)->orderBy('period')->get();
        $this->assertCount(2, $payments);
        $this->assertSame($payments[0]->proof_path, $payments[1]->proof_path);
        $this->assertSame(CashPayment::STATUS_PENDING, $payments[0]->status);
        $this->assertSame(CashPayment::STATUS_PENDING, $payments[1]->status);
        $this->assertSame(CashPayment::AMOUNT, $payments[0]->amount);
        $this->assertSame(CashPayment::AMOUNT, $payments[1]->amount);
        Storage::disk('local')->assertExists($payments[0]->proof_path);

        $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('bendahara.validasi-pembayaran.approve', $payments[0]))
            ->assertRedirect(route('bendahara.validasi-pembayaran'));

        $this->assertDatabaseHas('cash_payments', [
            'id' => $payments[0]->id,
            'status' => CashPayment::STATUS_APPROVED,
        ]);
        $this->assertDatabaseHas('cash_payments', [
            'id' => $payments[1]->id,
            'status' => CashPayment::STATUS_PENDING,
        ]);
        $this->assertDatabaseCount('cash_transactions', 1);
        Storage::disk('local')->assertExists($payments[0]->proof_path);
    }

    public function test_treasurer_can_approve_multiple_months_in_one_action(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $treasurer = User::factory()->create(['role' => 'adkeu']);
        $proofPath = UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf')
            ->store('cash-payment-proofs', 'local');
        $payments = collect([
            now()->startOfMonth()->subMonth(),
            now()->startOfMonth(),
        ])->map(fn (Carbon $period): CashPayment => CashPayment::create([
            'user_id' => $member->id,
            'period' => $period,
            'amount' => CashPayment::AMOUNT,
            'proof_path' => $proofPath,
            'status' => CashPayment::STATUS_PENDING,
        ]));

        $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('bendahara.validasi-pembayaran'))
            ->assertOk()
            ->assertSee('Setujui Pembayaran Terpilih')
            ->assertSee('name="payments[]"', false);

        $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('bendahara.validasi-pembayaran.approve-multiple'), [
                'payments' => $payments->pluck('id')->all(),
            ])
            ->assertRedirect(route('bendahara.validasi-pembayaran'))
            ->assertSessionHas('success', '2 pembayaran berhasil disetujui dan dicatat sebagai kas masuk.');

        $this->assertSame(
            [CashPayment::STATUS_APPROVED, CashPayment::STATUS_APPROVED],
            $payments->map(fn (CashPayment $payment): string => $payment->fresh()->status)->all(),
        );
        $this->assertSame(
            [$treasurer->id, $treasurer->id],
            $payments->map(fn (CashPayment $payment): int => $payment->fresh()->reviewed_by)->all(),
        );
        $this->assertDatabaseCount('cash_transactions', 2);
    }

    public function test_multi_period_submission_is_atomic_when_a_period_is_already_pending(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $pendingProof = UploadedFile::fake()->create('pending.pdf', 100, 'application/pdf')
            ->store('cash-payment-proofs', 'local');
        CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => $pendingProof,
            'status' => CashPayment::STATUS_PENDING,
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->from(route('anggota.bayar-kas'))
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => [
                    now()->startOfMonth()->subMonth()->format('Y-m'),
                    now()->format('Y-m'),
                ],
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'))
            ->assertSessionHasErrors('periods');

        $this->assertDatabaseCount('cash_payments', 1);
        $this->assertDatabaseMissing('cash_payments', [
            'user_id' => $member->id,
            'period' => now()->startOfMonth()->subMonth()->toDateString(),
        ]);
        $this->assertCount(1, Storage::disk('local')->files('cash-payment-proofs'));
        Storage::disk('local')->assertExists($pendingProof);
    }

    public function test_payment_submission_rejects_duplicate_and_future_periods(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp]);

        $this->from(route('anggota.bayar-kas'))
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => [now()->format('Y-m'), now()->format('Y-m')],
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'))
            ->assertSessionHasErrors('periods.1');

        $this->from(route('anggota.bayar-kas'))
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => [now()->addMonth()->format('Y-m')],
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'))
            ->assertSessionHasErrors('periods');

        $this->assertDatabaseCount('cash_payments', 0);
    }

    public function test_payment_proofs_use_the_configured_private_filesystem_disk(): void
    {
        config(['cash.proof_disk' => 'private']);
        Storage::fake('private');
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => [now()->format('Y-m')],
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'));

        Storage::disk('private')->assertExists(CashPayment::firstOrFail()->proof_path);
    }

    public function test_late_fee_is_flat_per_started_month_after_the_payment_deadline(): void
    {
        config(['cash.late_fee_per_month' => 10000]);
        // Deadline periode Oktober 2026 = 31 Okt 2026 23:59:59
        $period = Carbon::parse('2026-10-01');

        $this->assertSame(0, CashPayment::lateFeeFor($period, Carbon::parse('2026-10-31 23:59:59')));
        $this->assertSame(10000, CashPayment::lateFeeFor($period, Carbon::parse('2026-11-01 00:00:00')));
        $this->assertSame(10000, CashPayment::lateFeeFor($period, Carbon::parse('2026-11-30 23:59:59')));
        $this->assertSame(20000, CashPayment::lateFeeFor($period, Carbon::parse('2026-12-01 00:00:00')));
        $this->assertSame(30000, CashPayment::lateFeeFor($period, Carbon::parse('2027-01-01 00:00:00')));
    }

    public function test_account_late_fee_is_charged_once_for_eleven_months_of_arrears(): void
    {
        // Setelah akhir bulan November 2026 (30 Nov) → semua periode Jan–Nov terlambat
        Carbon::setTestNow('2026-12-01 10:00:00');
        config(['cash.late_fee_per_month' => 10000]);
        $member = User::factory()->create(['role' => 'anggota']);

        $summary = CashPayment::accountLateFeeSummaryFor($member->id, now());

        $this->assertSame(11, $summary['late_months']);
        $this->assertSame(110000, $summary['fee_amount']);
    }

    public function test_multi_period_submission_charges_per_period_denda(): void
    {
        // Setelah akhir Nov 2026 → okt & nov terlambat, 11 bulan total (jan–nov)
        Carbon::setTestNow('2026-12-01 10:00:00');
        config(['cash.late_fee_per_month' => 10000]);
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => ['2026-10', '2026-11'],
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'));

        // Denda dihitung per periode yang terlambat (10k masing-masing), bukan lump-sum 110k
        $this->assertSame(20000, CashPayment::query()->where('user_id', $member->id)->sum('late_fee_amount'));
        $this->assertSame(10000, CashPayment::query()->whereDate('period', '2026-10-01')->value('late_fee_amount'));
        $this->assertSame(10000, CashPayment::query()->whereDate('period', '2026-11-01')->value('late_fee_amount'));

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => ['2026-09'],
                'proof' => UploadedFile::fake()->create('follow-up.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'));

        $this->assertSame(30000, CashPayment::query()->where('user_id', $member->id)->sum('late_fee_amount'));

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.denda'))
            ->assertOk()
            ->assertSee('Rp110.000')
            ->assertDontSee('Rp220.000');
    }

    public function test_member_can_choose_to_pay_kas_only_without_late_fee(): void
    {
        Carbon::setTestNow('2026-12-01 10:00:00');
        config(['cash.late_fee_per_month' => 10000]);
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'payment_type' => 'kas_only',
                'periods' => ['2026-10'],
                'amount' => 20000,
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'));

        $payment = CashPayment::query()->where('user_id', $member->id)->whereDate('period', '2026-10-01')->firstOrFail();
        $this->assertSame(20000, $payment->amount);
        $this->assertSame(0, $payment->late_fee_amount);
    }

    public function test_member_can_choose_to_pay_denda_only(): void
    {
        Carbon::setTestNow('2026-12-01 10:00:00');
        config(['cash.late_fee_per_month' => 10000]);
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'payment_type' => 'denda_only',
                'periods' => ['2026-10'],
                'amount' => 10000,
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'));

        $payment = CashPayment::query()->where('user_id', $member->id)->whereDate('period', '2026-10-01')->firstOrFail();
        $this->assertSame(0, $payment->amount);
        $this->assertSame(10000, $payment->late_fee_amount);
    }

    public function test_manual_amount_allocates_smartly_to_kas_and_denda(): void
    {
        Carbon::setTestNow('2026-12-01 10:00:00');
        config(['cash.late_fee_per_month' => 10000]);
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'payment_type' => 'kas_dan_denda',
                'periods' => ['2026-10'],
                'amount' => 30000,
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'));

        $payment = CashPayment::query()->where('user_id', $member->id)->whereDate('period', '2026-10-01')->firstOrFail();
        $this->assertSame(20000, $payment->amount);
        $this->assertSame(10000, $payment->late_fee_amount);
    }

    public function test_member_can_submit_denda_even_if_kas_is_pending_or_approved(): void
    {
        Carbon::setTestNow('2026-12-01 10:00:00');
        config(['cash.late_fee_per_month' => 10000]);
        $member = User::factory()->create(['role' => 'anggota']);

        // Kas Pokok untuk Oktober 2026 sudah diajukan dan sedang pending
        $kasProof = UploadedFile::fake()->create('kas.pdf', 100, 'application/pdf')
            ->store('cash-payment-proofs', 'local');
        CashPayment::create([
            'user_id' => $member->id,
            'period' => '2026-10-01',
            'amount' => CashPayment::AMOUNT,
            'late_fee_amount' => 0,
            'proof_path' => $kasProof,
            'status' => CashPayment::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        // Pengajuan denda_only untuk periode yang sama TIDAK BOLEH diblokir
        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'payment_type' => 'denda_only',
                'periods' => ['2026-10'],
                'amount' => 10000,
                'proof' => UploadedFile::fake()->create('denda.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'))
            ->assertSessionDoesntHaveErrors();

        // Keduanya tersimpan di database sebagai catatan pengajuan terpisah
        $octPayments = CashPayment::query()
            ->where('user_id', $member->id)
            ->whereDate('period', '2026-10-01')
            ->get();

        $this->assertCount(2, $octPayments);
        $this->assertTrue($octPayments->contains(fn ($p) => $p->amount === 20000 && $p->late_fee_amount === 0));
        $this->assertTrue($octPayments->contains(fn ($p) => $p->amount === 0 && $p->late_fee_amount === 10000));
    }

    public function test_late_fee_is_saved_and_included_in_cash_income_after_approval(): void
    {
        // Okt 2026 deadline = 31 Okt 23:59:59; test berjalan 4 Nov → terlambat 1 bulan
        Carbon::setTestNow('2026-11-04 10:00:00');
        config(['cash.late_fee_per_month' => 10000]);
        $member = User::factory()->create(['role' => 'anggota']);
        $treasurer = User::factory()->create(['role' => 'adkeu']);
        foreach (Carbon::parse('2026-01-01')->monthsUntil('2026-09-01') as $period) {
            CashPayment::create([
                'user_id' => $member->id,
                'period' => $period,
                'amount' => CashPayment::AMOUNT,
                'late_fee_amount' => 0,
                'proof_path' => 'cash-payment-proofs/'.$period->format('Y-m').'.pdf',
                'submitted_at' => $period->copy()->startOfMonth()->addHours(10),
                'status' => CashPayment::STATUS_APPROVED,
            ]);
        }

        $expectedTotal = 'Rp'.number_format(CashPayment::AMOUNT + 10000, 0, ',', '.');
        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.bayar-kas'))
            ->assertOk()
            ->assertSee($expectedTotal)
            ->assertSee('Denda akun (1 bulan keterlambatan) Rp10.000');

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => ['2026-10'],
                'proof' => UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'));

        $payment = CashPayment::query()->whereDate('period', '2026-10-01')->firstOrFail();
        $this->assertSame(10000, $payment->late_fee_amount);
        $this->assertDatabaseHas('cash_payments', [
            'id' => $payment->id,
            'late_fee_amount' => 10000,
        ]);

        $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('bendahara.validasi-pembayaran.approve', $payment))
            ->assertRedirect(route('bendahara.validasi-pembayaran'));

        $this->assertDatabaseHas('cash_transactions', [
            'user_id' => $member->id,
            'type' => 'masuk',
            'amount' => CashPayment::AMOUNT + 10000,
        ]);
    }

    public function test_member_can_review_late_fee_details_and_status_tracks_payment_verification(): void
    {
        // Deadline Jan 2026 = 31 Jan 2026; test berjalan 4 Feb → jan terlambat 1 bln
        // Deadline Feb 2026 = 29 Feb 2026; test berjalan 4 Feb → feb belum terlambat → submitted on 3 Feb (sebelum deadline)
        Carbon::setTestNow('2026-02-04 10:00:00');
        config(['cash.late_fee_per_month' => 10000]);
        $member = User::factory()->create(['role' => 'anggota']);
        $treasurer = User::factory()->create(['role' => 'adkeu']);

        CashPayment::create([
            'user_id' => $member->id,
            'period' => '2026-01-01',
            'amount' => CashPayment::AMOUNT,
            'late_fee_amount' => 10000,
            'proof_path' => 'cash-payment-proofs/january.pdf',
            'submitted_at' => '2026-01-03 10:00:00',
            'status' => CashPayment::STATUS_APPROVED,
        ]);
        $pendingPayment = CashPayment::create([
            'user_id' => $member->id,
            'period' => '2026-02-01',
            'amount' => CashPayment::AMOUNT,
            'late_fee_amount' => 10000,
            'proof_path' => 'cash-payment-proofs/february.pdf',
            'submitted_at' => '2026-02-03 10:00:00',
            'status' => CashPayment::STATUS_PENDING,
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.denda'))
            ->assertOk()
            ->assertSee('Januari 2026')
            ->assertSee('Februari 2026')
            ->assertSee('Lunas · Terverifikasi')
            ->assertSee('Menunggu verifikasi')
            ->assertSee('Rp20.000')
            ->assertSee('Akumulasi 1 bulan keterlambatan');

        $this->get(route('anggota.dashboard'))
            ->assertOk()
            ->assertSee('Rp10.000')
            ->assertSee('1 tagihan');
        $this->get(route('anggota.profile'))
            ->assertOk()
            ->assertSee('Rp10.000')
            ->assertSee('1 tagihan');

        $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('bendahara.validasi-pembayaran.approve', $pendingPayment))
            ->assertRedirect(route('bendahara.validasi-pembayaran'));

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.denda'))
            ->assertOk()
            ->assertSee('Rp20.000')
            ->assertSee('Denda lunas dan terverifikasi')
            ->assertDontSee('Menunggu verifikasi');
    }

    public function test_member_can_view_monthly_payment_status_and_complete_payment_history(): void
    {
        // Jalankan di awal bulan berikutnya agar bulan ini sudah melewati deadline akhir bulan
        Carbon::setTestNow('2026-12-01 10:00:00');

        $member = User::factory()->create(['role' => 'anggota']);
        $reviewer = User::factory()->create(['role' => 'adkeu']);
        // Periode bulan lalu = Oktober 2026 (deadline 31 Okt, sudah lewat → terlambat)
        $approvedPayment = CashPayment::create([
            'user_id' => $member->id,
            'period' => Carbon::parse('2026-10-01'),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => 'cash-payment-proofs/approved.pdf',
            'status' => CashPayment::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => Carbon::parse('2026-11-16 10:00:00'),
        ]);
        // Periode bulan ini = November 2026 (deadline 30 Nov, sudah lewat di 1 Des → terlambat)
        $pendingPayment = CashPayment::create([
            'user_id' => $member->id,
            'period' => Carbon::parse('2026-11-01'),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => 'cash-payment-proofs/pending.pdf',
            'status' => CashPayment::STATUS_PENDING,
        ]);
        CashPayment::create([
            'user_id' => User::factory()->create(['role' => 'anggota'])->id,
            'period' => Carbon::parse('2026-11-01'),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => 'cash-payment-proofs/other-member.pdf',
            'status' => CashPayment::STATUS_APPROVED,
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.riwayat-pembayaran'))
            ->assertOk()
            ->assertSee('Status Pembayaran Bulanan')
            ->assertSee('November 2026')
            ->assertSee('Terlambat · Menunggu verifikasi')
            ->assertSee('Daftar Riwayat Transaksi Pembayaran Kas')
            ->assertSee($reviewer->name)
            ->assertSee($approvedPayment->created_at->format('d/m/Y H:i'))
            ->assertSee($pendingPayment->created_at->format('d/m/Y H:i'))
            ->assertSee('Total 2 transaksi')
            ->assertDontSee('other-member.pdf');
    }

    public function test_presbem_can_view_own_monthly_payment_status_and_history(): void
    {
        $presbem = User::factory()->create(['role' => 'presbem']);

        $this->actingAs($presbem)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('presbem.riwayat-pembayaran'))
            ->assertOk()
            ->assertSee('Riwayat Pembayaran')
            ->assertSee('Terlambat');
    }

    public function test_bendahara_can_approve_payment_and_it_is_recorded_as_cash_income(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $treasurer = User::factory()->create(['role' => 'bendahara']);
        $proofPath = UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf')
            ->store('cash-payment-proofs', 'local');
        $payment = CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => $proofPath,
            'status' => CashPayment::STATUS_PENDING,
        ]);

        $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('bendahara.validasi-pembayaran.approve', $payment))
            ->assertRedirect(route('bendahara.validasi-pembayaran'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cash_payments', [
            'id' => $payment->id,
            'status' => CashPayment::STATUS_APPROVED,
            'reviewed_by' => $treasurer->id,
        ]);
        $this->assertDatabaseHas('cash_transactions', [
            'user_id' => $member->id,
            'created_by' => $treasurer->id,
            'type' => 'masuk',
            'amount' => CashPayment::AMOUNT,
        ]);
        $this->assertDatabaseCount('cash_transactions', 1);
    }

    public function test_bendahara_can_reject_payment_and_member_can_resubmit_proof_without_removing_a_shared_file(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $treasurer = User::factory()->create(['role' => 'adkeu']);
        $firstProof = UploadedFile::fake()->create('first.pdf', 100, 'application/pdf')
            ->store('cash-payment-proofs', 'local');
        $payment = CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => $firstProof,
            'status' => CashPayment::STATUS_PENDING,
        ]);
        $otherPayment = CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth()->subMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => $firstProof,
            'status' => CashPayment::STATUS_PENDING,
        ]);

        $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('bendahara.validasi-pembayaran.reject', $payment), [
                'rejection_reason' => 'Bukti transfer tidak terbaca.',
            ])
            ->assertRedirect(route('bendahara.validasi-pembayaran'));

        $this->assertDatabaseHas('cash_payments', [
            'id' => $payment->id,
            'status' => CashPayment::STATUS_REJECTED,
            'rejection_reason' => 'Bukti transfer tidak terbaca.',
        ]);
        $this->assertDatabaseCount('cash_transactions', 0);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => [now()->format('Y-m')],
                'proof' => UploadedFile::fake()->create('replacement.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'));

        $this->assertDatabaseHas('cash_payments', [
            'id' => $payment->id,
            'status' => CashPayment::STATUS_PENDING,
            'rejection_reason' => null,
        ]);
        $this->assertDatabaseHas('cash_payments', [
            'id' => $otherPayment->id,
            'proof_path' => $firstProof,
        ]);
        Storage::disk('local')->assertExists($firstProof);
    }

    public function test_payment_submission_rejects_invalid_files_and_duplicate_pending_requests(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->from(route('anggota.bayar-kas'))
            ->post(route('anggota.bayar-kas.submit'), [
                'periods' => [now()->format('Y-m')],
                'proof' => UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload'),
            ])
            ->assertRedirect(route('anggota.bayar-kas'))
            ->assertSessionHasErrors('proof');

        $proofPath = UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf')
            ->store('cash-payment-proofs', 'local');
        CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => $proofPath,
            'status' => CashPayment::STATUS_PENDING,
        ]);

        $this->post(route('anggota.bayar-kas.submit'), [
            'periods' => [now()->format('Y-m')],
            'proof' => UploadedFile::fake()->create('another.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('periods');
    }

    public function test_only_cash_team_can_view_uploaded_evidence_and_review_payments(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        $treasurer = User::factory()->create(['role' => 'bendahara']);
        $proofPath = UploadedFile::fake()->create('transfer.pdf', 100, 'application/pdf')
            ->store('cash-payment-proofs', 'local');
        $payment = CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => $proofPath,
            'status' => CashPayment::STATUS_PENDING,
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('bendahara.validasi-pembayaran.evidence', $payment))
            ->assertRedirect(route('anggota.dashboard'));

        $this->actingAs($treasurer)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('bendahara.validasi-pembayaran.evidence', $payment))
            ->assertOk();
    }
}
