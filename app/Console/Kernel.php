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
        $schedule->command('companies:backup-monthly')->monthlyOn(1, '02:00');
        $schedule->command('logs:prune-user-activities')->dailyAt('01:00');
        $schedule->command('companies:lock-expired-trials')->everyFifteenMinutes();
        $schedule->command('vehicles:notify-due')->dailyAt('08:00');
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
