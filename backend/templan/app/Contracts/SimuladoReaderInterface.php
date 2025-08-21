<?php

namespace App\Contracts;

use App\Models\Simulado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SimuladoReaderInterface
{
    /**
     * Busca todos os simulados com filtros opcionais
     */
    public function findAll(?string $status = null): Collection;

    /**
     * Busca simulados com paginação
     */
    public function findPaginated(?string $status = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Busca um simulado por ID
     */
    public function findById(int $id): ?Simulado;

    /**
     * Busca simulados por categoria
     */
    public function findByCategory(int $categoryId): Collection;

    /**
     * Busca simulados ativos
     */
    public function findActive(): Collection;

    /**
     * Busca simulados arquivados
     */
    public function findArchived(): Collection;
}