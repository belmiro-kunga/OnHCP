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
        Schema::create('video_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_lesson_id')->constrained()->onDelete('cascade');
            $table->string('original_filename');
            $table->string('s3_key');
            $table->string('upload_id')->nullable(); // For multipart uploads
            $table->enum('upload_type', ['direct', 'multipart']);
            $table->enum('status', ['uploading', 'uploaded', 'processing', 'completed', 'failed']);
            $table->bigInteger('file_size')->nullable();
            $table->string('content_type')->nullable();
            $table->json('s3_metadata')->nullable();
            $table->string('mp4_url')->nullable();
            $table->string('hls_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('processing_data')->nullable(); // Store processing job info
            $table->text('error_message')->nullable();
            $table->timestamp('upload_completed_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['course_lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_uploads');
    }
};