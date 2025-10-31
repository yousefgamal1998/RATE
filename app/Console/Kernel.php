<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\FillTrailerUrls::class,
        // Ensure default categories exist — useful for CI/CD pipelines and deployments
        \App\Console\Commands\SyncCategories::class,
        // Merge duplicate categories safely (reassign movies and delete duplicates)
        \App\Console\Commands\CleanupCategories::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run the trailer fill command hourly. Adjust as needed.
        $schedule->command('trailers:fill')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
