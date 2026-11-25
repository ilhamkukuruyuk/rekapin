<?php

namespace App\Console\Commands;

use App\Mail\OverdueCashPaymentReminder;
use App\Models\CashPayment;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOverdueCashPaymentReminders extends Command
{
    protected $signature = 'cash:send-overdue-reminders';

    protected $description = 'Email members with overdue unpaid monthly cash dues';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) config('cash.reminder_interval_days', 7));
        $currentPeriod = now()->startOfMonth();
        $periods = collect(CarbonPeriod::create(now()->startOfYear(), '1 month', $currentPeriod))
            ->filter(fn (Carbon $period): bool => now()->greaterThan(CashPayment::deadlineFor($period)))
            ->values();

        if ($periods->isEmpty()) {
            $this->info('Belum ada periode kas yang melewati tenggat.');

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        User::query()
            ->whereIn('role', ['anggota', 'presbem'])
            ->chunkById(100, function ($members) use ($periods, $cutoff, $currentPeriod, &$sent, &$failed): void {
                foreach ($members as $member) {
                    $payments = CashPayment::query()
                        ->where('user_id', $member->id)
                        ->whereYear('period', $currentPeriod->year)
                        ->get()
                        ->keyBy(fn (CashPayment $payment): string => $payment->period->format('Y-m'));

                    $eligiblePeriods = $periods->filter(function (Carbon $period) use ($member, $payments, $cutoff): bool {
                        $payment = $payments->get($period->format('Y-m'));
                        if ($payment?->status === CashPayment::STATUS_APPROVED
                            || $payment?->status === CashPayment::STATUS_PENDING) {
                            return false;
                        }

                        return ! DB::table('cash_payment_reminders')
                            ->where('user_id', $member->id)
                            ->whereDate('period', $period->toDateString())
                            ->where('sent_at', '>', $cutoff)
                            ->exists();
                    })->values();

                    if ($eligiblePeriods->isEmpty()) {
                        continue;
                    }

                    try {
                        Mail::to($member->email)->send(
                            new OverdueCashPaymentReminder($member->name, $eligiblePeriods),
                        );

                        $sentAt = now();
                        foreach ($eligiblePeriods as $period) {
                            DB::table('cash_payment_reminders')->updateOrInsert(
                                [
                                    'user_id' => $member->id,
                                    'period' => $period->toDateString(),
                                ],
                                [
                                    'sent_at' => $sentAt,
                                    'updated_at' => $sentAt,
                                    'created_at' => $sentAt,
                                ],
                            );
                        }

                        $sent++;
                    } catch (Throwable $exception) {
                        $failed++;
                        Log::error('Failed to send overdue cash payment reminder.', [
                            'user_id' => $member->id,
                            'email' => $member->email,
                            'periods' => $eligiblePeriods->map(fn (Carbon $period) => $period->toDateString())->all(),
                            'exception' => $exception,
                        ]);
                        $this->error("Gagal mengirim pengingat kepada {$member->email}.");
                    }
                }
            });

        $this->info("Pengingat terkirim ke {$sent} anggota; gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
