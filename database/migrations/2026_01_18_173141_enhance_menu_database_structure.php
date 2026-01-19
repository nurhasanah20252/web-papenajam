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
        Schema::table('menus', function (Blueprint $table) {
            $table->json('locations')->nullable()->after('location');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->json('display_rules')->nullable()->after('conditions');
            $table->string('type', 50)->nullable()->after('url_type');
            $table->string('target', 20)->default('_self')->after('target_blank');
            $table->string('class_name')->nullable()->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['display_rules', 'type', 'target', 'class_name']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('locations');
        });
    }
};
