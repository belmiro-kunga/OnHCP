<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ip_locks', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->unsignedInteger('fail_count')->default(0);
            $table->timestamp('window_started_at')->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->timestamps();
            $table->unique(['ip']);
            $table->index(['locked_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_locks');
    }
};
