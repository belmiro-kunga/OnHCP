<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('product');
            $table->unsignedInteger('seats_total')->default(0);
            $table->unsignedInteger('seats_used')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('license_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamps();
            $table->unique(['license_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_assignments');
        Schema::dropIfExists('licenses');
    }
};
