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
            $table->string('type', 50);
            $table->string('status', 50);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->string('triggered_by', 50)->default('system');
            $table->text('error_message')->nullable();
            $table->json('stats')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('started_at');
            $table->index(['status', 'started_at']);
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
