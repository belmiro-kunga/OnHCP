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
        // Todos os índices necessários já existem no banco de dados
        // Esta migração foi criada para documentar a otimização de performance
        // mas não precisa adicionar novos índices pois eles já foram criados
        
        // Índices existentes verificados:
        // - certificates: status_issued_at, user_id_status, course_id_status
        // - courses: category_id_status, category_id_created_at
        // - course_lessons: course_module_id_sort_index
        // - course_modules: course_id_sort_index
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nenhum índice foi adicionado, então nada para remover
    }
};