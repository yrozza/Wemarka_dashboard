<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the scheduled tasks
        if ($this->app->runningInConsole()) {
            $this->scheduleTasks();
        }
    }

    /**
     * Schedule tasks
     *
     * @return void
     */
    protected function scheduleTasks()
    {
        // Access Laravel's scheduler and schedule the command to run daily
        $schedule = app(Schedule::class);

        // Schedule the command to run once daily
        $schedule->command('carts:purge-discarded')->daily();
    }
}

