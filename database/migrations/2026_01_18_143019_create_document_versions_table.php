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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->string('version', 50);
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->unsignedInteger('file_size');
            $table->string('file_type', 100)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('checksum', 64)->nullable();
            $table->text('changelog')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('document_id');
            $table->index('version');
            $table->index('is_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
