<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('simulado_questions', function (Blueprint $table) {
            $table->string('q_type', 30)->default('multiple_choice')->after('statement');
            $table->unsignedInteger('weight')->default(1)->after('q_type');
            $table->string('difficulty', 10)->default('medium')->after('weight');
            $table->index(['simulado_id', 'q_order']);
        });
    }

    public function down(): void
    {
        Schema::table('simulado_questions', function (Blueprint $table) {
            $table->dropIndex(['simulado_id', 'q_order']);
            $table->dropColumn(['q_type', 'weight', 'difficulty']);
        });
    }
};
