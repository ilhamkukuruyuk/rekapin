<?php

namespace App\Console\Commands;

use App\Models\PiketSchedule;
use Illuminate\Console\Command;

class SyncPiketAttendance extends Command
{
    protected $signature = 'piket:sync-attendance';

    protected $description = 'Mark missed piket schedules as alpha once the shift has passed in WIB';

    public function handle(): int
    {
        $updated = PiketSchedule::syncPastSchedules();

        $this->info("Piket schedules marked as alpha: {$updated}.");

        return self::SUCCESS;
    }
}
