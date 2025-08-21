<?php

namespace App\Repositories;

use App\Contracts\SimuladoRepositoryInterface;
use App\Models\Simulado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SimuladoRepository implements SimuladoRepositoryInterface
{
    private const CACHE_TTL = 600; // 10 minutes

    public function findAll(?string $status = null): Collection
    {
        $cacheKey = 'simulados_all_' . ($status ?? 'all');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($status) {
            $query = Simulado::query()->with(['category'])->withCount('assignments');
            
            if ($status) {
                $query->where('status', $status);
            }
            
            return $query->orderByDesc('id')->get();
        });
    }

    public function findPaginated(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Simulado::query()->with(['category'])->withCount('assignments');
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderByDesc('id')->paginate($perPage);
    }

    public function findById(int $id): ?Simulado
    {
        return Simulado::with(['questions', 'category'])->find($id);
    }

    public function create(array $data): Simulado
    {
        $simulado = Simulado::create($data);
        $this->clearCache();
        
        return $simulado->load(['questions', 'category']);
    }

    public function update(Simulado $simulado, array $data): Simulado
    {
        $simulado->update($data);
        $this->clearCache();
        
        return $simulado->load(['questions', 'category']);
    }

    public function delete(Simulado $simulado): bool
    {
        $result = $simulado->delete();
        $this->clearCache();
        
        return $result;
    }

    public function findByCategory(int $categoryId): Collection
    {
        $cacheKey = "simulados_category_{$categoryId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($categoryId) {
            return Simulado::where('category_id', $categoryId)
                ->with(['category'])
                ->orderByDesc('id')
                ->get();
        });
    }

    public function findActive(): Collection
    {
        return $this->findAll('active');
    }

    public function findArchived(): Collection
    {
        return $this->findAll('archived');
    }

    /**
     * Limpa o cache relacionado aos simulados
     */
    private function clearCache(): void
    {
        Cache::forget('simulados_all_all');
        Cache::forget('simulados_all_active');
        Cache::forget('simulados_all_archived');
        
        // Limpar cache de categorias (padrão simples)
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget("simulados_category_{$i}");
        }
        
        // Limpar cache de usuários (padrão simples)
        for ($i = 1; $i <= 1000; $i++) {
            Cache::forget("user_simulados_{$i}");
        }
    }
}