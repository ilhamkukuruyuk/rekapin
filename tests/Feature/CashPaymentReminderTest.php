<?php

namespace Tests\Feature;

use App\Mail\OverdueCashPaymentReminder;
use App\Models\CashPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CashPaymentReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Carbon::setTestNow('2026-10-19 10:00:00');
        config(['cash.deadline_day' => 1]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_member_history_marks_unpaid_and_late_submissions_after_deadline(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);
        CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => 'cash-payment-proofs/late.pdf',
            'submitted_at' => '2026-10-02 09:00:00',
            'status' => CashPayment::STATUS_APPROVED,
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.riwayat-pembayaran'))
            ->assertOk()
            ->assertSee('Lunas · Terlambat')
            ->assertSee('Terlambat');

        $this->assertSame('2026-10-01 23:59:59', CashPayment::deadlineFor(now())->format('Y-m-d H:i:s'));
    }

    public function test_overdue_reminder_emails_are_sent_once_per_member_and_period(): void
    {
        Mail::fake();
        $member = User::factory()->create(['role' => 'anggota']);
        $approvedMember = User::factory()->create(['role' => 'presbem']);
        $this->settleEarlierPeriods($approvedMember);
        CashPayment::create([
            'user_id' => $approvedMember->id,
            'period' => now()->startOfMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => 'cash-payment-proofs/paid.pdf',
            'submitted_at' => '2026-10-01 12:00:00',
            'status' => CashPayment::STATUS_APPROVED,
        ]);
        $this->assertDatabaseCount('cash_payments', 10);

        $this->artisan('cash:send-overdue-reminders')->assertSuccessful();

        Mail::assertSent(OverdueCashPaymentReminder::class, function (OverdueCashPaymentReminder $mail) use ($member): bool {
            return $mail->hasTo($member->email)
                && $mail->overduePeriods->contains(fn (Carbon $period): bool => $period->format('Y-m') === '2026-10');
        });
        Mail::assertSent(OverdueCashPaymentReminder::class, 1);
        $this->assertDatabaseHas('cash_payment_reminders', [
            'user_id' => $member->id,
            'period' => '2026-10-01',
        ]);
        $this->assertDatabaseMissing('cash_payment_reminders', [
            'user_id' => $approvedMember->id,
            'period' => '2026-10-01',
        ]);

        $this->artisan('cash:send-overdue-reminders')->assertSuccessful();
        Mail::assertSent(OverdueCashPaymentReminder::class, 1);
    }

    public function test_reminder_is_repeated_only_after_seven_days_if_still_unpaid(): void
    {
        Mail::fake();
        User::factory()->create(['role' => 'anggota']);

        $this->artisan('cash:send-overdue-reminders')->assertSuccessful();
        Mail::assertSent(OverdueCashPaymentReminder::class, 1);

        Carbon::setTestNow(now()->addDays(6));
        $this->artisan('cash:send-overdue-reminders')->assertSuccessful();
        Mail::assertSent(OverdueCashPaymentReminder::class, 1);

        Carbon::setTestNow(now()->addDays(2));
        $this->artisan('cash:send-overdue-reminders')->assertSuccessful();
        Mail::assertSent(OverdueCashPaymentReminder::class, 2);
    }

    public function test_reminders_are_not_sent_before_deadline_or_while_payment_is_pending(): void
    {
        Mail::fake();
        $member = User::factory()->create(['role' => 'anggota']);
        $this->settleEarlierPeriods($member);
        Carbon::setTestNow('2026-10-01 12:00:00');

        $this->artisan('cash:send-overdue-reminders')->assertSuccessful();
        Mail::assertNothingSent();

        Carbon::setTestNow('2026-10-02 12:00:00');
        CashPayment::create([
            'user_id' => $member->id,
            'period' => now()->startOfMonth(),
            'amount' => CashPayment::AMOUNT,
            'proof_path' => 'cash-payment-proofs/pending.pdf',
            'submitted_at' => '2026-10-01 12:00:00',
            'status' => CashPayment::STATUS_PENDING,
        ]);

        $this->artisan('cash:send-overdue-reminders')->assertSuccessful();
        Mail::assertNothingSent();
    }

    private function settleEarlierPeriods(User $member): void
    {
        foreach (range(1, 9) as $month) {
            CashPayment::create([
                'user_id' => $member->id,
                'period' => Carbon::create(2026, $month, 1),
                'amount' => CashPayment::AMOUNT,
                'proof_path' => "cash-payment-proofs/paid-{$month}.pdf",
                'submitted_at' => Carbon::create(2026, $month, 1, 12),
                'status' => CashPayment::STATUS_APPROVED,
            ]);
        }
    }
}
