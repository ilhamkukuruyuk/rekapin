<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiketSchedule extends Model
{
    public const STATUS_HADIR = 'hadir';

    public const STATUS_IZIN = 'izin';

    public const STATUS_ALPHA = 'alpha';

    protected $fillable = [
        'user_id',
        'created_by',
        'date',
        'start_time',
        'end_time',
        'location',
        'attendance_status',
        'attended_at',
        'proof_path',
        'attendance_note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'attended_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d').' '.$this->start_time, 'Asia/Jakarta');
    }

    public function endsAt(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d').' '.$this->end_time, 'Asia/Jakarta');
    }

    public static function syncPastSchedules(): int
    {
        $now = now('Asia/Jakarta');
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        return self::query()
            ->whereNull('attendance_status')
            ->where(function ($query) use ($today, $currentTime) {
                $query->where('date', '<', $today)
                    ->orWhere(function ($q) use ($today, $currentTime) {
                        $q->where('date', '=', $today)
                            ->where('end_time', '<', $currentTime);
                    });
            })
            ->update([
                'attendance_status' => self::STATUS_ALPHA,
                'attendance_note' => 'Otomatis ditandai Alpha oleh sistem karena tidak melakukan presensi hingga shift berakhir.',
            ]);
    }

    public function effectiveAttendanceStatus(): ?string
    {
        if ($this->attendance_status) {
            return $this->attendance_status;
        }

        return $this->endsAt()->isPast() ? self::STATUS_ALPHA : null;
    }
}
