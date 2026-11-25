<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PresensiKegiatan extends Model
{
    use HasFactory;

    public const METHOD_QR = 'qr_code';
    public const METHOD_MANUAL = 'manual';

    public const STATUS_HADIR = 'hadir';
    public const STATUS_IZIN = 'izin';
    public const STATUS_TIDAK_HADIR = 'tidak-hadir';

    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'waktu_presensi',
        'bukti_foto',
        'metode',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_presensi' => 'datetime',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getBuktiFotoUrlAttribute(): string
    {
        if (empty($this->bukti_foto)) {
            return '';
        }

        if (Storage::disk('public')->exists($this->bukti_foto)) {
            return Storage::disk('public')->url($this->bukti_foto);
        }

        return asset('storage/' . ltrim($this->bukti_foto, '/'));
    }
}
