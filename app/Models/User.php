<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nim',
        'departemen',
        'angkatan',
        'jabatan',
        'avatar',
        'status_kas',
        'denda_aktif',
        'kehadiran_piket',
        'total_kas_dibayar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'denda_aktif' => 'integer',
            'kehadiran_piket' => 'integer',
            'total_kas_dibayar' => 'integer',
        ];
    }

    /**
     * Check if user is Bendahara / ADKEU
     */
    public function isBendahara(): bool
    {
        return in_array($this->role, ['bendahara', 'adkeu']);
    }

    /**
     * Check if user is PresBEM
     */
    public function isPresbem(): bool
    {
        return $this->role === 'presbem';
    }

    /**
     * Check if user is PSDI
     */
    public function isPsdi(): bool
    {
        return $this->role === 'psdi';
    }

    /**
     * Check if user is Anggota Biasa
     */
    public function isAnggota(): bool
    {
        return $this->role === 'anggota';
    }

    /**
     * Check if user matches any of the given roles
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;

        // Normalisasi adkeu & bendahara
        if (in_array('bendahara', $roles) || in_array('adkeu', $roles)) {
            $roles[] = 'bendahara';
            $roles[] = 'adkeu';
        }

        return in_array($this->role, $roles);
    }

    /**
     * Get user initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name));
        if (empty($words)) {
            return 'U';
        }

        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 1));
        }

        // Jika kata pertama singkatan seperti M., ambil huruf berikutnya
        $first = preg_replace('/[^a-zA-Z]/', '', $words[0]);
        $second = preg_replace('/[^a-zA-Z]/', '', $words[1]);

        if (empty($first) && !empty($second)) {
            return strtoupper(substr($second, 0, 1));
        }

        if (strlen($first) === 1 && isset($words[2])) {
            $third = preg_replace('/[^a-zA-Z]/', '', $words[2]);
            return strtoupper(substr($second, 0, 1) . substr($third, 0, 1));
        }

        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }

    /**
     * Get dashboard route name based on role
     */
    public function getDashboardRouteAttribute(): string
    {
        return match ($this->role) {
            'admin' => route('admin.accounts.create'),
            'adkeu', 'bendahara' => route('bendahara.dashboard'),
            'presbem' => route('presbem.dashboard'),
            'psdi' => route('psdi.dashboard'),
            default => route('anggota.dashboard'),
        };
    }

    /**
     * Get formatted role badge label
     */
    public function getRoleBadgeAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'adkeu', 'bendahara' => 'ADKEU',
            'presbem' => 'PresBEM',
            'psdi' => 'PSDI',
            default => 'Anggota Biasa',
        };
    }

    /**
     * Get user's event attendance records
     */
    public function presensiKegiatans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PresensiKegiatan::class, 'user_id');
    }

    /**
     * Get user's event permission requests
     */
    public function izinPresensis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IzinPresensi::class, 'user_id');
    }
}
