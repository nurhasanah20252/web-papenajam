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
        Schema::create('sipp_judges', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable();
            $table->string('judge_code', 50)->nullable();
            $table->string('full_name', 255);
            $table->string('title', 100)->nullable();
            $table->string('specialization', 100)->nullable();
            $table->string('chamber', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index('external_id');
            $table->index('judge_code');
            $table->index('full_name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sipp_judges');
    }
};
