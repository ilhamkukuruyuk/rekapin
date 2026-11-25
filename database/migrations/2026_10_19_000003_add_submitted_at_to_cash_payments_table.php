<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cash_payments', 'submitted_at')) {
            Schema::table('cash_payments', function (Blueprint $table) {
                $table->timestamp('submitted_at')->nullable();
            });
        }

        DB::table('cash_payments')
            ->whereNull('submitted_at')
            ->update(['submitted_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('cash_payments', 'submitted_at')) {
            Schema::table('cash_payments', function (Blueprint $table) {
                $table->dropColumn('submitted_at');
            });
        }
    }
};
