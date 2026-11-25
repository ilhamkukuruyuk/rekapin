<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashPayment extends Model
{
    public const AMOUNT = 20000;

    public const LATE_FEE_AMOUNT = 10000;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'submission_group_id',
        'period',
        'amount',
        'late_fee_amount',
        'proof_path',
        'submitted_at',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'amount' => 'integer',
            'late_fee_amount' => 'integer',
        ];
    }

    public static function deadlineFor(Carbon $period): Carbon
    {
        // Tenggat selalu di akhir bulan (28/29 Feb, 30/31 bulan lain) pukul 23:59:59
        return $period->copy()->endOfMonth()->setTime(23, 59, 59);
    }

    public static function lateDaysFor(Carbon $period, Carbon $date): int
    {
        $deadline = self::deadlineFor($period);
        $date = $date->copy();

        if ($date->lessThanOrEqualTo($deadline)) {
            return 0;
        }

        return (int) $deadline->startOfDay()->diffInDays($date->startOfDay());
    }

    public static function lateMonthsFor(Carbon $period, Carbon $date): int
    {
        $deadline = self::deadlineFor($period);
        $date = $date->copy();

        if ($date->lessThanOrEqualTo($deadline)) {
            return 0;
        }

        $nextDeadline = $deadline->copy();
        $months = 0;

        while ($date->greaterThan($nextDeadline)) {
            $months++;
            $nextDeadline->addMonthNoOverflow();
        }

        return $months;
    }

    public static function isPeriodOverdue(Carbon $period, ?Carbon $date = null): bool
    {
        $date = $date ?? now('Asia/Jakarta');

        return $date->greaterThan(self::deadlineFor($period));
    }

    public static function feeForPeriod(Carbon $period, ?Carbon $date = null): int
    {
        return self::isPeriodOverdue($period, $date) ? (int) config('cash.late_fee_per_month', self::LATE_FEE_AMOUNT) : 0;
    }

    public static function totalObligationForPeriod(Carbon $period, ?Carbon $date = null): int
    {
        return self::AMOUNT + self::feeForPeriod($period, $date);
    }

    public static function lateFeeFor(Carbon $period, Carbon $date): int
    {
        return self::lateMonthsFor($period, $date) * (int) config('cash.late_fee_per_month', self::LATE_FEE_AMOUNT);
    }

    /**
     * @return array{period: ?Carbon, late_days: int, late_months: int, fee_amount: int, overdue_count: int}
     */
    public static function accountLateFeeSummaryFor(int $userId, Carbon $date): array
    {
        $paymentsByPeriod = self::query()
            ->where('user_id', $userId)
            ->whereBetween('period', [
                $date->copy()->startOfYear()->toDateString(),
                $date->copy()->startOfMonth()->endOfMonth()->toDateString(),
            ])
            ->get()
            ->groupBy(fn (self $payment): string => $payment->period->format('Y-m'));

        $overdueCount = 0;
        $oldestUnpaidPeriod = null;

        foreach (CarbonPeriod::create($date->copy()->startOfYear(), '1 month', $date->copy()->startOfMonth()) as $period) {
            $periodPayments = $paymentsByPeriod->get($period->format('Y-m'), collect());

            $hasPaidOrPendingDenda = $periodPayments->contains(
                fn (self $p): bool => in_array($p->status, [self::STATUS_APPROVED, self::STATUS_PENDING], true)
                    && ($p->late_fee_amount > 0 || ! $p->isLate())
            );

            if ($hasPaidOrPendingDenda) {
                continue;
            }

            if ($date->lessThanOrEqualTo(self::deadlineFor($period))) {
                continue;
            }

            $overdueCount++;
            if (! $oldestUnpaidPeriod) {
                $oldestUnpaidPeriod = $period->copy();
            }
        }

        if ($overdueCount === 0) {
            return [
                'period' => null,
                'late_days' => 0,
                'late_months' => 0,
                'fee_amount' => 0,
                'overdue_count' => 0,
            ];
        }

        $feePerMonth = (int) config('cash.late_fee_per_month', self::LATE_FEE_AMOUNT);

        return [
            'period' => $oldestUnpaidPeriod,
            'late_days' => self::lateDaysFor($oldestUnpaidPeriod, $date),
            'late_months' => $overdueCount,
            'fee_amount' => $overdueCount * $feePerMonth,
            'overdue_count' => $overdueCount,
        ];
    }

    public function submissionDate(): Carbon
    {
        return $this->submitted_at ?? $this->created_at;
    }

    public function isLate(): bool
    {
        return $this->submissionDate()->greaterThan(self::deadlineFor($this->period));
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, [self::STATUS_APPROVED, self::STATUS_PENDING], true)) {
            return $this->isLate();
        }

        return now()->greaterThan(self::deadlineFor($this->period));
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
