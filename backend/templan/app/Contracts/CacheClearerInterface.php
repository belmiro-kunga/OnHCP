<?php

namespace App\Contracts;

interface CacheClearerInterface
{
    /**
     * Limpa cache relacionado aos simulados
     */
    public function clearSimuladoCache(): void;

    /**
     * Limpa cache de um usuário específico
     */
    public function clearUserSimuladoCache(int $userId): void;

    /**
     * Limpa cache de uma categoria específica
     */
    public function clearCategoryCache(int $categoryId): void;

    /**
     * Limpa todo o cache do sistema
     */
    public function clearAllCache(): void;
}