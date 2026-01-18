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
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->string('case_type', 100);
            $table->unsignedInteger('total_cases')->default(0);
            $table->unsignedInteger('resolved_cases')->default(0);
            $table->unsignedInteger('pending_cases')->default(0);
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->index('case_type');
            $table->unique(['year', 'month', 'case_type']);
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
