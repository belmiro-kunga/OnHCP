<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simulados', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->default(3600); // seconds
            $table->unsignedTinyInteger('min_score')->default(70); // percentage
            $table->unsignedSmallInteger('max_attempts')->default(1);
            $table->string('type')->default('exam');
            $table->boolean('allow_navigation')->default(true);
            $table->boolean('allow_save_progress')->default(true);
            $table->boolean('show_feedback')->default(true);
            $table->string('status')->default('active'); // active|archived
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulados');
    }
};
