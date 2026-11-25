<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Murni hanya untuk data akun pengguna / autentikasi.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new \LogicException('Demo accounts must not be seeded in production.');
        }

        // ==========================================
        // DATA PENGGUNA (USERS) BERDASARKAN ROLE & DEPARTEMEN
        // Sifat Akun ADKEU & PSDI Universal / Institusional
        // ==========================================
        $usersData = [
            // --- BIRO ADKEU (UNIVERSAL / DEPARTEMENTAL - ROLE: BENDAHARA / ADKEU) ---
            [
                'email' => 'adkeu@bem-filkom.ub.ac.id',
                'name' => 'Administrasi Keuangan (ADKEU)',
                'password' => Hash::make('adkeu123'),
                'role' => 'bendahara',
                'nim' => 'BEM-ADKEU',
                'departemen' => 'ADKEU',
                'angkatan' => '2024',
                'jabatan' => 'Biro Administrasi Keuangan',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],

            // --- BIRO PSDI (UNIVERSAL / DEPARTEMENTAL - ROLE: PSDI) ---
            [
                'email' => 'psdi@bem-filkom.ub.ac.id',
                'name' => 'Pengembangan Sumber Daya Internal (PSDI)',
                'password' => Hash::make('psdi123'),
                'role' => 'psdi',
                'nim' => 'BEM-PSDI',
                'departemen' => 'PSDI',
                'angkatan' => '2024',
                'jabatan' => 'Biro Pengembangan Sumber Daya Insani',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],

            // --- PRES BEM / EKSEKUTIF (ROLE: PRESBEM) ---
            [
                'email' => 'daffa@bem-filkom.ub.ac.id',
                'name' => 'M. Daffa Riyadlussalam',
                'password' => Hash::make('presbem123'),
                'role' => 'presbem',
                'nim' => '235150700111001',
                'departemen' => 'BPI',
                'angkatan' => '2023',
                'jabatan' => 'Ketua BEM',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],
            [
                'email' => 'ahmad@bem-filkom.ub.ac.id',
                'name' => 'Ahmad Fauzi',
                'password' => Hash::make('wapresbem123'),
                'role' => 'presbem',
                'nim' => '235150700111002',
                'departemen' => 'BPI',
                'angkatan' => '2023',
                'jabatan' => 'Wakil Ketua BEM',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],

            // --- ANGGOTA BIASA (BERBAGAI KEMENTERIAN / KEBIROAN) ---
            [
                'email' => 'claudio@bem-filkom.ub.ac.id',
                'name' => 'Claudio Amadeus',
                'password' => Hash::make('anggota123'),
                'role' => 'anggota',
                'nim' => '255150700111003',
                'departemen' => 'PSDM',
                'angkatan' => '2025',
                'jabatan' => 'Staff PSDM',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],
            [
                'email' => 'budi@student.ub.ac.id',
                'name' => 'Budi Santoso',
                'password' => Hash::make('anggota123'),
                'role' => 'anggota',
                'nim' => '255150700111004',
                'departemen' => 'Medinkraf',
                'angkatan' => '2025',
                'jabatan' => 'Staff Medinkraf',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],
            [
                'email' => 'citra@student.ub.ac.id',
                'name' => 'Citra Lestari',
                'password' => Hash::make('anggota123'),
                'role' => 'anggota',
                'nim' => '255150700111005',
                'departemen' => 'Advokesma',
                'angkatan' => '2025',
                'jabatan' => 'Staff Advokesma',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],
            [
                'email' => 'dimas@student.ub.ac.id',
                'name' => 'Dimas Setiawan',
                'password' => Hash::make('anggota123'),
                'role' => 'anggota',
                'nim' => '255150700111006',
                'departemen' => 'Sosling',
                'angkatan' => '2025',
                'jabatan' => 'Staff Sosling',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],
            [
                'email' => 'fajar@student.ub.ac.id',
                'name' => 'Fajar Nugroho',
                'password' => Hash::make('anggota123'),
                'role' => 'anggota',
                'nim' => '255150700111007',
                'departemen' => 'Kastrat',
                'angkatan' => '2025',
                'jabatan' => 'Staff Kastrat',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],
            [
                'email' => 'gita@student.ub.ac.id',
                'name' => 'Gita Savitri',
                'password' => Hash::make('anggota123'),
                'role' => 'anggota',
                'nim' => '255150700111008',
                'departemen' => 'PIT',
                'angkatan' => '2025',
                'jabatan' => 'Staff PIT',
                'status_kas' => 'Lunas',
                'denda_aktif' => 0,
                'kehadiran_piket' => 100,
                'total_kas_dibayar' => 0,
            ],
        ];

        foreach ($usersData as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
