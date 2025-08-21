<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if (env('LDAP_SYNC_ENABLED', false)) {
            $schedule->command('ldap:sync-groups --limit=500')->everyThirtyMinutes()->withoutOverlapping();
        }
        
        // Enviar lembretes de prazo de simulados diariamente às 9h
        $schedule->command('simulado:send-deadline-reminders')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
