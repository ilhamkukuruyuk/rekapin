<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use HasFactory;

    public const STATUS_AKAN_DATANG = 'akan-datang';
    public const STATUS_BERLANGSUNG = 'berlangsung';
    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'nama',
        'jenis',
        'deskripsi',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'status',
        'qr_code',
        'qr_expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'qr_expires_at' => 'datetime',
        ];
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(PresensiKegiatan::class, 'kegiatan_id');
    }

    public function izinPresensis(): HasMany
    {
        return $this->hasMany(IzinPresensi::class, 'kegiatan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeBerlangsung(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BERLANGSUNG);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_BERLANGSUNG, self::STATUS_AKAN_DATANG]);
    }

    public function isAvailableForAttendance(): bool
    {
        if ($this->status !== self::STATUS_BERLANGSUNG) {
            return false;
        }

        if ($this->qr_expires_at && now()->greaterThan($this->qr_expires_at)) {
            return false;
        }

        return true;
    }

    public function hasUserAttended(int $userId): bool
    {
        return $this->presensis()->where('user_id', $userId)->exists();
    }
}
