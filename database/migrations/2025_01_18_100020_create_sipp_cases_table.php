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
        Schema::create('sipp_cases', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable();
            $table->string('case_number', 100)->nullable();
            $table->string('case_title', 255)->nullable();
            $table->string('case_type', 100)->nullable();
            $table->date('register_date')->nullable();
            $table->string('register_number', 100)->nullable();
            $table->enum('case_status', ['pending', 'in_progress', 'postponed', 'closed'])->default('pending');
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->json('plaintiff')->nullable();
            $table->json('defendant')->nullable();
            $table->json('attorney')->nullable();
            $table->text('subject_matter')->nullable();
            $table->date('last_hearing_date')->nullable();
            $table->date('next_hearing_date')->nullable();
            $table->date('final_decision_date')->nullable();
            $table->text('decision_summary')->nullable();
            $table->json('document_references')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('sync_status', ['pending', 'success', 'error'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index('external_id');
            $table->index('case_number');
            $table->index('case_type');
            $table->index('case_status');
            $table->index('register_date');
            $table->index('sync_status');
            $table->index(['case_status', 'register_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sipp_cases');
    }
};
