<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Membuat tabel `izin_presensis` untuk sistem pengajuan izin kegiatan.
     * Anggota bisa mengajukan izin tidak hadir, dan PSDI bisa menyetujui/menolak.
     * Jika disetujui, status presensi pada `presensi_kegiatans` akan diubah ke 'izin'.
     */
    public function up(): void
    {
        Schema::create('izin_presensis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kegiatan_id')
                ->constrained('kegiatans')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('alasan');

            $table->string('bukti_path')->nullable();

            // Status: menunggu | disetujui | ditolak
            $table->string('status')->default('menunggu');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->text('catatan_reviewer')->nullable();

            $table->timestamps();

            // Satu anggota hanya boleh punya satu pengajuan izin per kegiatan
            $table->unique(['kegiatan_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_presensis');
    }
};
