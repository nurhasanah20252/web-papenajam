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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 255)->unique();
            $table->string('title', 255);
            $table->text('excerpt')->nullable();
            $table->json('content')->nullable();
            $table->json('meta')->nullable();
            $table->string('featured_image', 500)->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('template_id')->nullable()->constrained('page_templates')->onDelete('set null');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('slug');
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
