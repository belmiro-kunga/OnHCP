<?php

namespace App\Contracts;

interface CacheKeyGeneratorInterface
{
    /**
     * Gera chave de cache para simulados
     */
    public function generateSimuladoCacheKey(?string $status = null): string;

    /**
     * Gera chave de cache para usuário
     */
    public function generateUserCacheKey(int $userId): string;

    /**
     * Gera chave de cache para categoria
     */
    public function generateCategoryCacheKey(int $categoryId): string;
}