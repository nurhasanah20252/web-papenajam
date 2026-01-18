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
        $roles = ['super_admin', 'admin', 'author', 'designer', 'subscriber'];

        Schema::table('users', function (Blueprint $table) use ($roles) {
            $table->enum('role', $roles)->default('subscriber')->after('email');

            $table->json('permissions')->nullable()->after('role');

            $table->timestamp('last_login_at')->nullable()->after('remember_token');

            $table->boolean('profile_completed')->default(false)->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'permissions', 'last_login_at', 'profile_completed']);
        });
    }
};
