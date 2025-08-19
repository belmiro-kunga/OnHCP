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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_integration_id')->constrained('external_integrations')->onDelete('cascade');
            $table->string('sync_type'); // full, incremental, manual, scheduled
            $table->string('direction'); // import, export, bidirectional
            $table->string('status'); // started, running, completed, failed, cancelled
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration')->nullable(); // Duração em segundos
            $table->integer('records_processed')->default(0);
            $table->integer('records_created')->default(0);
            $table->integer('records_updated')->default(0);
            $table->integer('records_deleted')->default(0);
            $table->integer('records_failed')->default(0);
            $table->json('summary')->nullable(); // Resumo detalhado da sincronização
            $table->json('errors')->nullable(); // Lista de erros encontrados
            $table->json('warnings')->nullable(); // Lista de avisos
            $table->text('error_message')->nullable(); // Mensagem de erro principal
            $table->json('metadata')->nullable(); // Metadados adicionais
            $table->string('triggered_by')->nullable(); // user_id, system, scheduler
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Usuário que iniciou
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['external_integration_id', 'status']);
            $table->index(['status', 'started_at']);
            $table->index('started_at');
            $table->index('completed_at');
            $table->index('sync_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
