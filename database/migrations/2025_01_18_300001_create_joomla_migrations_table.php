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
        Schema::create('joomla_migrations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'rolled_back'])->default('pending');
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('processed_records')->default(0);
            $table->unsignedInteger('failed_records')->default(0);
            $table->json('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('joomla_migration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('migration_id')->constrained('joomla_migrations')->onDelete('cascade');
            $table->string('type', 50);
            $table->unsignedInteger('joomla_id');
            $table->json('joomla_data')->nullable();
            $table->string('local_model', 100)->nullable();
            $table->unsignedInteger('local_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'skipped'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['migration_id', 'type']);
            $table->index(['migration_id', 'status']);
            $table->index('joomla_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joomla_migration_items');
        Schema::dropIfExists('joomla_migrations');
    }
};
