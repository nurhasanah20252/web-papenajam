<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('court_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable()->comment('ID from SIPP API');
            $table->string('case_number', 100)->nullable();
            $table->string('case_title', 255)->nullable();
            $table->string('case_type', 100)->nullable();
            $table->string('judge_name', 255)->nullable();
            $table->string('court_room', 100)->nullable();
            $table->string('room_code', 50)->nullable();
            $table->date('schedule_date')->nullable();
            $table->time('schedule_time')->nullable();
            $table->enum('schedule_status', ['scheduled', 'postponed', 'cancelled', 'completed'])->default('scheduled');
            $table->json('parties')->nullable()->comment('JSON: penggugat, tergugat, kuasa_hukum');
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('sync_status', ['pending', 'success', 'error'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index('external_id');
            $table->index('schedule_date');
            $table->index('case_number');
            $table->index('schedule_status');
            $table->index('sync_status');
            $table->index(['schedule_date', 'schedule_time']);
            $table->index(['schedule_date', 'sync_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_schedules');
    }
};
