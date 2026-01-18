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
            $table->string('case_number', 100)->nullable();
            $table->string('case_title', 255)->nullable();
            $table->string('case_type', 100)->nullable();
            $table->string('judge_name', 255)->nullable();
            $table->string('court_room', 100)->nullable();
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->string('status', 50)->default('scheduled');
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->string('sipp_case_id', 100)->nullable();
            $table->string('sync_status', 50)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index('scheduled_date');
            $table->index('case_number');
            $table->index('status');
            $table->index(['scheduled_date', 'scheduled_time']);
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
