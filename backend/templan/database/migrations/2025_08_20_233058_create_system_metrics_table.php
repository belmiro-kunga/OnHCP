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
        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type'); // Tipo de métrica (simulados, usuarios, sistema, etc.)
            $table->string('metric_name'); // Nome específico da métrica
            $table->json('metric_data'); // Dados da métrica em formato JSON
            $table->decimal('metric_value', 15, 2)->nullable(); // Valor numérico principal
            $table->string('period_type')->default('daily'); // daily, weekly, monthly, yearly
            $table->date('period_date'); // Data do período
            $table->json('metadata')->nullable(); // Metadados adicionais
            $table->timestamps();
            
            // Índices para performance
            $table->index(['metric_type', 'period_date']);
            $table->index(['metric_name', 'period_date']);
            $table->index(['period_type', 'period_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_metrics');
    }
};
