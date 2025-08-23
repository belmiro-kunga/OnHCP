<?php

namespace App\Observers;

use App\Services\CacheOptimizationService;
use Illuminate\Database\Eloquent\Model;

class CacheInvalidationObserver
{
    protected $cacheService;

    public function __construct(CacheOptimizationService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Handle the model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->invalidateCache($model);
    }

    /**
     * Invalidate related cache based on model type
     */
    private function invalidateCache(Model $model): void
    {
        $modelClass = class_basename($model);
        $this->cacheService->invalidateRelatedCache($modelClass, $model->id ?? null);
    }
}