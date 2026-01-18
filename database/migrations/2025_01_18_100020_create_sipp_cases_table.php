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
            $table->string('sipp_case_id', 100)->nullable()->unique();
            $table->string('case_number', 100)->nullable();
            $table->string('case_title', 255)->nullable();
            $table->string('case_type', 100)->nullable();
            $table->date('registration_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('status', 50)->default('open');
            $table->string('judge_name', 255)->nullable();
            $table->string('plaintiff', 255)->nullable();
            $table->string('defendant', 255)->nullable();
            $table->decimal('claim_amount', 15, 2)->nullable();
            $table->text('decision')->nullable();
            $table->string('sync_status', 50)->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sipp_case_id');
            $table->index('case_number');
            $table->index('case_type');
            $table->index('status');
            $table->index('registration_date');
            $table->index(['status', 'registration_date']);
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
