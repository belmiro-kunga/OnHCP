<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simulado_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulado_id');
            $table->enum('target_type', ['course', 'class', 'user']);
            $table->unsignedBigInteger('target_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->unsignedSmallInteger('max_attempts_override')->nullable();
            $table->unsignedTinyInteger('min_score_override')->nullable();
            $table->timestamps();

            $table->foreign('simulado_id')->references('id')->on('simulados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulado_assignments');
    }
};
