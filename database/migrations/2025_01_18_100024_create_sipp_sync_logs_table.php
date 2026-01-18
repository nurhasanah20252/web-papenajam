<?php

use App\Enums\SyncStatus;
use App\Enums\SyncType;
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
        Schema::create('sipp_sync_logs', function (Blueprint $table) {
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

            $table->index('sync_type');
            $table->index('start_time');
            $table->index('error_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sipp_sync_logs');
    }
};
