<?php

namespace App\Services;

use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Facades\Cache;

class CacheService implements CacheServiceInterface
{
    private const SIMULADO_CACHE_PREFIX = 'simulados_';
    private const USER_CACHE_PREFIX = 'user_simulados_';
    private const CATEGORY_CACHE_PREFIX = 'simulados_category_';
    
    public function clearSimuladoCache(): void
    {
        $keys = [
            $this->generateSimuladoCacheKey(),
            $this->generateSimuladoCacheKey('active'),
            $this->generateSimuladoCacheKey('archived'),
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function clearUserSimuladoCache(int $userId): void
    {
        Cache::forget($this->generateUserCacheKey($userId));
    }

    public function clearCategoryCache(int $categoryId): void
    {
        Cache::forget($this->generateCategoryCacheKey($categoryId));
    }

    public function clearAllCache(): void
    {
        // Limpar cache de simulados
        $this->clearSimuladoCache();
        
        // Limpar cache de categorias (padrão simples para demonstração)
        for ($i = 1; $i <= 100; $i++) {
            $this->clearCategoryCache($i);
        }
        
        // Limpar cache de usuários (padrão simples para demonstração)
        for ($i = 1; $i <= 1000; $i++) {
            $this->clearUserSimuladoCache($i);
        }
    }

    public function generateSimuladoCacheKey(?string $status = null): string
    {
        return self::SIMULADO_CACHE_PREFIX . 'all_' . ($status ?? 'all');
    }

    public function generateUserCacheKey(int $userId): string
    {
        return self::USER_CACHE_PREFIX . $userId;
    }

    public function generateCategoryCacheKey(int $categoryId): string
    {
        return self::CATEGORY_CACHE_PREFIX . $categoryId;
    }
}