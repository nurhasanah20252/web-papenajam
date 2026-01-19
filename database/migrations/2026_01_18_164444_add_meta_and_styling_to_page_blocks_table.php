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
        Schema::table('page_blocks', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('settings');
            $table->string('css_class')->nullable()->after('meta');
            $table->string('anchor_id')->nullable()->after('css_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_blocks', function (Blueprint $table) {
            $table->dropColumn(['meta', 'css_class', 'anchor_id']);
        });
    }
};
