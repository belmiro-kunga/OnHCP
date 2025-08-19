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
        Schema::create('organizational_structure', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Usuário
            $table->unsignedBigInteger('department_id')->nullable(); // Departamento
            $table->unsignedBigInteger('job_position_id')->nullable(); // Cargo
            $table->unsignedBigInteger('manager_id')->nullable(); // Gestor direto
            $table->unsignedBigInteger('substitute_id')->nullable(); // Substituto
            $table->string('employment_type')->default('full_time'); // Tipo de contrato
            $table->date('start_date')->nullable(); // Data de início
            $table->date('end_date')->nullable(); // Data de fim (se aplicável)
            $table->decimal('salary', 10, 2)->nullable(); // Salário
            $table->string('cost_center')->nullable(); // Centro de custo
            $table->json('reporting_structure')->nullable(); // Estrutura de reporte em JSON
            $table->json('permissions')->nullable(); // Permissões específicas
            $table->string('external_employee_id')->nullable(); // ID no sistema externo
            $table->boolean('active')->default(true); // Status ativo/inativo
            $table->timestamp('sync_at')->nullable(); // Última sincronização
            $table->timestamps();
            
            // Índices
            $table->index(['user_id']);
            $table->index(['department_id']);
            $table->index(['job_position_id']);
            $table->index(['manager_id']);
            $table->index(['active']);
            $table->index(['external_employee_id']);
            
            // Chaves estrangeiras
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('job_position_id')->references('id')->on('job_positions')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('substitute_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizational_structure');
    }
};
