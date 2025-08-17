<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('job_title')->nullable();
            $table->string('ad_group')->nullable();
            $table->unsignedBigInteger('role_id');
            $table->integer('priority')->default(100);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->index(['active','priority']);
            $table->index(['department_id','job_title']);
            $table->index(['ad_group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_mappings');
    }
};
