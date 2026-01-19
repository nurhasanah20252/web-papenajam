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
        Schema::table('pages', function (Blueprint $table) {
            $table->json('builder_content')->nullable()->after('content');
            $table->unsignedInteger('version')->default(1)->after('builder_content');
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->onDelete('set null')->after('author_id');
            $table->boolean('is_builder_enabled')->default(false)->after('page_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['last_edited_by']);
            $table->dropColumn(['builder_content', 'version', 'last_edited_by', 'is_builder_enabled']);
        });
    }
};
