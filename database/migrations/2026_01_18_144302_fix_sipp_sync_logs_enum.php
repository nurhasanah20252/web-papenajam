<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support dropping columns with indexes directly
        // We need to recreate the table
        Schema::dropIfExists('sipp_sync_logs_new');

        Schema::create('sipp_sync_logs_new', function (Blueprint $table) {
            $table->id();
            $table->string('sync_type')->default('full');
            $table->string('sync_mode')->default('incremental');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->unsignedInteger('records_fetched')->default(0);
            $table->unsignedInteger('records_updated')->default(0);
            $table->unsignedInteger('records_created')->default(0);
            $table->unsignedInteger('records_skipped')->default(0);
            $table->text('error_message')->nullable();
            $table->enum('created_by', ['system', 'user'])->default('system');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('sync_type', 'idx_sipp_sync_logs_sync_type');
            $table->index('start_time', 'idx_sipp_sync_logs_start_time');
            $table->index('error_message', 'idx_sipp_sync_logs_error_msg');
        });

        // Copy existing data (only for tables that already have data)
        if (Schema::hasTable('sipp_sync_logs')) {
            DB::statement('INSERT INTO sipp_sync_logs_new (id, sync_type, start_time, end_time, records_fetched, records_updated, records_created, error_message, created_by, metadata, created_at, updated_at) SELECT id, sync_type, start_time, end_time, records_fetched, records_updated, records_created, error_message, created_by, metadata, created_at, updated_at FROM sipp_sync_logs');
        }

        // Drop old table and rename new one
        Schema::dropIfExists('sipp_sync_logs');
        Schema::rename('sipp_sync_logs_new', 'sipp_sync_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sipp_sync_logs_new');

        Schema::create('sipp_sync_logs_new', function (Blueprint $table) {
            $table->id();
            $table->enum('sync_type', ['full', 'incremental'])->default('full');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->unsignedInteger('records_fetched')->default(0);
            $table->unsignedInteger('records_updated')->default(0);
            $table->unsignedInteger('records_created')->default(0);
            $table->text('error_message')->nullable();
            $table->enum('created_by', ['system', 'user'])->default('system');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('sync_type', 'idx_sipp_sync_logs_sync_type_down');
            $table->index('start_time', 'idx_sipp_sync_logs_start_time_down');
            $table->index('error_message', 'idx_sipp_sync_logs_error_msg_down');
        });

        DB::statement('INSERT INTO sipp_sync_logs_new (id, sync_type, start_time, end_time, records_fetched, records_updated, records_created, error_message, created_by, metadata, created_at, updated_at) SELECT id, sync_type, start_time, end_time, records_fetched, records_updated, records_created, error_message, created_by, metadata, created_at, updated_at FROM sipp_sync_logs');

        Schema::dropIfExists('sipp_sync_logs');
        Schema::rename('sipp_sync_logs_new', 'sipp_sync_logs');
    }
};
