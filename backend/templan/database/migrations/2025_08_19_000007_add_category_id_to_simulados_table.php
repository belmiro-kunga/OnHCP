<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('simulados', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('description');
            $table->foreign('category_id')->references('id')->on('simulado_categories')->nullOnDelete();
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('simulados', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
