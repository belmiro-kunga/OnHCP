<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simulado_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulado_id');
            $table->text('statement');
            $table->json('options'); // [{id:'A', text:''}, ...]
            $table->string('correct_answer'); // e.g., 'A'
            $table->text('explanation')->nullable();
            $table->unsignedInteger('q_order')->default(0);
            $table->timestamps();

            $table->foreign('simulado_id')->references('id')->on('simulados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulado_questions');
    }
};
