<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Dokumentasi: Perubahan konstanta CashPayment::AMOUNT dari 50000 → 20000.
     *
     * Perubahan ini dilakukan di Model PHP (app/Models/CashPayment.php),
     * bukan di database schema, sehingga migration ini tidak mengubah schema apapun.
     *
     * Referensi:
     * - CashPayment::AMOUNT = 20000  (kas pokok per bulan)
     * - CashPayment::LATE_FEE_AMOUNT = 10000  (denda per bulan terlambat)
     * - Config: cash.late_fee_per_month = 10000
     * - Seeder: gunakan CashPayment::AMOUNT (20000) sebagai default amount
     */
    public function up(): void
    {
        // Tidak ada perubahan schema. Lihat app/Models/CashPayment.php.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada perubahan schema untuk di-rollback.
    }
};
