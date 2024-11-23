<?php

namespace App\Console;

use App\Console\Commands\AppOptimizeClear;
use App\Console\Commands\ImportDB;
use App\Console\Commands\ResetDatabase;
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
        // Commands\Inspire::class,
        ResetDatabase::class,
        AppOptimizeClear::class,
        ImportDB::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }
}
