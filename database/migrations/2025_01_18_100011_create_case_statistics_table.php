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
        Schema::create('case_statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->unsignedTinyInteger('month');
            $table->string('case_type', 100);
            $table->enum('court_type', ['perdata', 'pidana', 'agama'])->default('perdata');
            $table->unsignedInteger('total_filed')->default(0);
            $table->unsignedInteger('total_resolved')->default(0);
            $table->unsignedInteger('pending_carryover')->default(0);
            $table->decimal('avg_resolution_days', 8, 2)->nullable();
            $table->decimal('settlement_rate', 5, 2)->nullable();
            $table->string('external_data_hash', 64)->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->index('case_type');
            $table->index('court_type');
            $table->unique(['year', 'month', 'case_type', 'court_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_statistics');
    }
};
