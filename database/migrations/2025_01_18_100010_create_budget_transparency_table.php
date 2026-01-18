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
        Schema::create('budget_transparency', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('document_path', 500)->nullable();
            $table->timestamps();

            $table->index('year');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_transparency');
    }
};
