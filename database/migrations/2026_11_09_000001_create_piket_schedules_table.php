<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piket_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location', 255);
            $table->string('attendance_status', 20)->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->string('proof_path')->nullable();
            $table->text('attendance_note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date', 'start_time']);
            $table->index(['date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piket_schedules');
    }
};
