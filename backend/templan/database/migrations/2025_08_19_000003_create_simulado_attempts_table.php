<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simulado_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulado_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('current_question')->default(0);
            $table->json('answers')->nullable(); // { [questionIndex]: 'A' }
            $table->unsignedInteger('time_remaining')->nullable(); // seconds
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->boolean('passed')->nullable();
            $table->json('result')->nullable();
            $table->timestamps();

            $table->foreign('simulado_id')->references('id')->on('simulados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulado_attempts');
    }
};
