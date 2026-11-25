<?php

return [
    'deadline_day' => (int) env('CASH_PAYMENT_DEADLINE_DAY', 1),
    'late_fee_per_month' => (int) env('CASH_PAYMENT_LATE_FEE_PER_MONTH', 10000),
    'reminder_interval_days' => 7,
    'proof_disk' => env('CASH_PAYMENT_PROOF_DISK', 'local'),
];
