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
        Schema::table('users', function (Blueprint $table) {
            // Adicionar índices para melhorar performance das consultas
            $table->index('role_id');
            $table->index('department_id');
            $table->index('status');
            $table->index('created_at'); // Para ordenação
            $table->index(['name', 'email']); // Índice composto para pesquisas
            $table->index('email'); // Índice adicional para email (se não existir)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['name', 'email']);
            $table->dropIndex(['email']);
        });
    }
};