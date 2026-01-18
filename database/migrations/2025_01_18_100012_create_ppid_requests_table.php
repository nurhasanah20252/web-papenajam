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
        Schema::create('ppid_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->string('applicant_name', 255);
            $table->string('nik', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255);
            $table->string('request_type', 100);
            $table->string('subject', 255);
            $table->text('description');
            $table->enum('status', ['submitted', 'reviewed', 'processed', 'completed', 'rejected'])->default('submitted');
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('status');
            $table->index('request_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppid_requests');
    }
};
