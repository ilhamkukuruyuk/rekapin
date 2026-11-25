<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // Add a regular index first so the foreign key constraint on user_id is satisfied
            Schema::table('cash_payments', function (Blueprint $table) {
                $table->index(['user_id', 'period'], 'cash_payments_user_id_period_index');
            });

            // Now safe to drop the unique index
            Schema::table('cash_payments', function (Blueprint $table) {
                $table->dropUnique('cash_payments_user_id_period_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('cash_payments', function (Blueprint $table) {
                $table->unique(['user_id', 'period'], 'cash_payments_user_id_period_unique');
            });

            Schema::table('cash_payments', function (Blueprint $table) {
                $table->dropIndex('cash_payments_user_id_period_index');
            });
        }
    }
};
