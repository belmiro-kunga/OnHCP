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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_course_enrollment_id')->constrained('user_course_enrollments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique();
            $table->timestamp('issued_at');
            $table->decimal('final_grade', 5, 2);
            $table->integer('completion_hours');
            $table->string('template_version')->default('v1');
            $table->json('certificate_data')->nullable(); // Dados adicionais para o template
            $table->string('file_path')->nullable(); // Caminho do arquivo PDF gerado
            $table->string('status')->default('active'); // active, revoked, expired
            $table->timestamp('expires_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['user_id']);
            $table->index(['course_id']);
            $table->index(['certificate_number']);
            $table->index(['verification_code']);
            $table->index(['issued_at']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
