<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simulado_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulado_id');
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('code')->unique();
            $table->timestamp('issued_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('simulado_id')->references('id')->on('simulados')->onDelete('cascade');
            $table->foreign('attempt_id')->references('id')->on('simulado_attempts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulado_certificates');
    }
};
