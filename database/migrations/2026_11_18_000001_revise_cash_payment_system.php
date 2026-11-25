<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds `submission_group_id` (UUID string) to `cash_payments` to allow grouping
     * multiple month payments submitted in one go, while keeping the existing
     * UNIQUE(user_id, period) constraint intact so each month's record stays unique.
     *
     * NOTE: CashPayment::AMOUNT constant has been updated to 20000 in the Model.
     * Default `amount` for new payments should be 20000 (kas pokok per bulan).
     */
    public function up(): void
    {
        Schema::table('cash_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_payments', 'submission_group_id')) {
                $table->string('submission_group_id', 36)
                    ->nullable()
                    ->index()
                    ->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_payments', function (Blueprint $table) {
            if (Schema::hasColumn('cash_payments', 'submission_group_id')) {
                $table->dropIndex(['submission_group_id']);
                $table->dropColumn('submission_group_id');
            }
        });
    }
};
