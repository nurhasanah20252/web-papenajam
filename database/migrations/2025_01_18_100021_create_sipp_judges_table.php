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
            $table->string('sipp_id', 100)->nullable()->unique();
            $table->string('name', 255);
            $table->string('position', 100)->nullable();
            $table->string('court_name', 255)->nullable();
            $table->timestamps();

            $table->index('sipp_id');
            $table->index('name');
            $table->index('position');
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
