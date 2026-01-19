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
        // Unify joomla_migrations table
        if (Schema::hasTable('joomla_migrations')) {
            Schema::table('joomla_migrations', function (Blueprint $table) {
                if (! Schema::hasColumn('joomla_migrations', 'name')) {
                    $table->string('name')->after('id')->nullable();
                }
                if (! Schema::hasColumn('joomla_migrations', 'status')) {
                    $table->string('status')->default('pending')->after('name');
                }
                if (! Schema::hasColumn('joomla_migrations', 'metadata')) {
                    $table->json('metadata')->nullable()->after('status');
                }
                if (! Schema::hasColumn('joomla_migrations', 'total_records')) {
                    $table->unsignedInteger('total_records')->default(0)->after('metadata');
                }
                if (! Schema::hasColumn('joomla_migrations', 'processed_records')) {
                    $table->unsignedInteger('processed_records')->default(0)->after('total_records');
                }
                if (! Schema::hasColumn('joomla_migrations', 'failed_records')) {
                    $table->unsignedInteger('failed_records')->default(0)->after('processed_records');
                }
                if (! Schema::hasColumn('joomla_migrations', 'progress')) {
                    $table->unsignedInteger('progress')->default(0)->after('failed_records');
                }
                if (! Schema::hasColumn('joomla_migrations', 'errors')) {
                    $table->json('errors')->nullable()->after('progress');
                }
                if (! Schema::hasColumn('joomla_migrations', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('errors');
                }
                if (! Schema::hasColumn('joomla_migrations', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('started_at');
                }

                // If old columns exist, we can keep them for now or migrate data
                // For this project, it seems safe to just ensure the new columns exist
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('joomla_migrations', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'status',
                'metadata',
                'total_records',
                'processed_records',
                'failed_records',
                'progress',
                'errors',
                'started_at',
                'completed_at',
            ]);
        });
    }
};
