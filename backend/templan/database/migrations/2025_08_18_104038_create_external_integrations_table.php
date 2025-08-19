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
        Schema::create('external_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome da integração
            $table->string('type'); // Tipo: ldap, active_directory, hr_system, erp, etc.
            $table->string('provider')->nullable(); // Provedor: Microsoft, Oracle, SAP, etc.
            $table->text('description')->nullable();
            $table->json('configuration'); // Configurações específicas da integração
            $table->json('credentials')->nullable(); // Credenciais criptografadas
            $table->string('status')->default('inactive'); // active, inactive, error, testing
            $table->string('sync_frequency')->nullable(); // hourly, daily, weekly, manual
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('next_sync_at')->nullable();
            $table->json('sync_settings')->nullable(); // Configurações de sincronização
            $table->json('field_mappings')->nullable(); // Mapeamento de campos
            $table->json('filters')->nullable(); // Filtros de sincronização
            $table->boolean('auto_sync')->default(false);
            $table->boolean('bidirectional')->default(false); // Sincronização bidirecional
            $table->integer('timeout')->default(30); // Timeout em segundos
            $table->integer('retry_attempts')->default(3);
            $table->json('error_handling')->nullable(); // Configurações de tratamento de erro
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index(['type', 'status']);
            $table->index('status');
            $table->index('last_sync_at');
            $table->index('next_sync_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_integrations');
    }
};
