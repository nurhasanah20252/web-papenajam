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
        Schema::table('page_templates', function (Blueprint $table) {
            $table->renameColumn('structure', 'content');
            $table->renameColumn('is_default', 'is_system');
            $table->string('thumbnail')->nullable()->after('description');
            $table->foreignId('created_by')->nullable()->after('is_system')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_templates', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['created_by', 'thumbnail']);
            $table->renameColumn('is_system', 'is_default');
            $table->renameColumn('content', 'structure');
        });
    }
};
