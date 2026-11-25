<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('cash_transactions')) {
            return;
        }

        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->enum('type', ['masuk', 'keluar']);
            $table->date('transaction_date');
            $table->string('description', 150);
            $table->unsignedBigInteger('amount');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['transaction_date', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('migrations')->where('migration', '2026_10_19_000000_create_cash_transactions_table')->exists()) {
            return;
        }

        Schema::dropIfExists('cash_transactions');
    }
};
