<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ip_policies', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['whitelist', 'blacklist']);
            $table->string('ip_cidr'); // e.g., 203.0.113.0/24 or 203.0.113.25/32
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->index(['type']);
            $table->unique(['type', 'ip_cidr']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_policies');
    }
};
