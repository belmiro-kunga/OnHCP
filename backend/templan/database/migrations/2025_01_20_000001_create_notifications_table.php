<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // simulado_assigned, simulado_reminder, simulado_result, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // dados específicos da notificação
            $table->timestamp('read_at')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->timestamp('scheduled_for')->nullable(); // para notificações agendadas
            $table->timestamps();
            
            // Índices
            $table->index(['user_id']);
            $table->index(['type']);
            $table->index(['read_at']);
            $table->index(['priority']);
            $table->index(['scheduled_for']);
            $table->index(['created_at']);
            
            // Chave estrangeira
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};