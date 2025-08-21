<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\SimuladoRepositoryInterface;
use App\Repositories\SimuladoRepository;
use App\Contracts\SimuladoQuestionServiceInterface;
use App\Services\SimuladoQuestionService;
use App\Contracts\CacheServiceInterface;
use App\Services\CacheService;
use App\Contracts\ScoringStrategyInterface;
use App\Services\Scoring\StandardScoringStrategy;
use App\Contracts\NotificationChannelInterface;
use App\Contracts\NotificationServiceInterface;
use App\Services\NotificationService;
use App\Services\Notifications\EmailNotificationChannel;
use App\Services\Notifications\DatabaseNotificationChannel;

class SimuladoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(SimuladoRepositoryInterface::class, SimuladoRepository::class);
        
        // Service bindings
        $this->app->bind(SimuladoQuestionServiceInterface::class, SimuladoQuestionService::class);
        $this->app->bind(CacheServiceInterface::class, CacheService::class);
        
        // Scoring strategy binding (default)
        $this->app->bind(ScoringStrategyInterface::class, StandardScoringStrategy::class);
        
        // Bind notification service interface
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
        
        // Notification channels (can be extended)
        $this->app->when(EmailNotificationChannel::class)
                  ->needs(NotificationChannelInterface::class)
                  ->give(EmailNotificationChannel::class);
                  
        $this->app->when(DatabaseNotificationChannel::class)
                  ->needs(NotificationChannelInterface::class)
                  ->give(DatabaseNotificationChannel::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}