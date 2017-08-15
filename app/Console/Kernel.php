<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendQueue;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\InitProductsFromCsv::class,
        Commands\ConsumeMessage::class,
        Commands\ImportProducts::class,
        Commands\MigrateValidTimeProductSupplier::class,
        Commands\EmailAlert::class,
        SendQueue::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->command('queue:send')
            ->dailyAt('02:00')
            ->sendOutputTo('queue-send_.log', true);

        $schedule->command('email:alert')
            ->dailyAt('01:30')
            ->sendOutputTo('email-alert.log', true);
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }

}
