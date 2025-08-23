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
        Schema::create('user_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->foreignId('course_lesson_id')->constrained()->onDelete('cascade');
            $table->boolean('started')->default(false);
            $table->boolean('completed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('watch_time_seconds')->default(0); // Tempo assistido em segundos
            $table->integer('total_duration_seconds')->nullable(); // Duração total do vídeo
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->integer('attempts')->default(0); // Número de tentativas/acessos
            $table->timestamp('last_accessed_at')->nullable();
            $table->json('progress_metadata')->nullable(); // Para dados adicionais como pontos de parada, notas, etc.
            $table->timestamps();
            
            // Índices
            $table->unique(['enrollment_id', 'course_lesson_id']);
            $table->index(['completed']);
            $table->index(['started_at']);
            $table->index(['completed_at']);
            $table->index(['last_accessed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
    }
};