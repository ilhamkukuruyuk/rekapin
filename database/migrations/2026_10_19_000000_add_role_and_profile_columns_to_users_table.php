<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'role' => static fn (Blueprint $table) => $table->string('role')->default('anggota')->index(),
            'nim' => static fn (Blueprint $table) => $table->string('nim', 20)->nullable(),
            'departemen' => static fn (Blueprint $table) => $table->string('departemen', 50)->nullable(),
            'angkatan' => static fn (Blueprint $table) => $table->string('angkatan', 10)->nullable(),
            'jabatan' => static fn (Blueprint $table) => $table->string('jabatan', 100)->nullable(),
            'avatar' => static fn (Blueprint $table) => $table->string('avatar')->nullable(),
            'status_kas' => static fn (Blueprint $table) => $table->string('status_kas', 20)->default('Lunas'),
            'denda_aktif' => static fn (Blueprint $table) => $table->unsignedBigInteger('denda_aktif')->default(0),
            'kehadiran_piket' => static fn (Blueprint $table) => $table->integer('kehadiran_piket')->default(100),
            'total_kas_dibayar' => static fn (Blueprint $table) => $table->unsignedBigInteger('total_kas_dibayar')->default(0),
        ];

        foreach ($columns as $name => $addColumn) {
            if (! Schema::hasColumn('users', $name)) {
                Schema::table('users', $addColumn);
            }
        }
    }

    public function down(): void
    {
        // These columns may have existed before this compatibility migration ran.
    }
};
