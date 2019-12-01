<?php

namespace Consilience\Laravel\ExtendedLogging;

/**
 *
 */

use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Queue;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        // Register the listeners for the queues, as a way to monitor the jobs.

        Queue::before(function (JobProcessing $event) {
            // $event->connectionName
            // $event->job
            // $event->job->payload()

            $displayName = $event->job->payload()['displayName'] ?? null;

            if (is_string($displayName)) {
                app(LoggingService::class)->setJobName($displayName);
            }
        });

        Queue::after(function (JobProcessed $event) {
            // $event->connectionName
            // $event->job
            // $event->job->payload()

            app(LoggingService::class)->resetJobName();
        });

        Queue::failing(function (JobFailed $event) {
            // $event->connectionName
            // $event->job
            // $event->exception

            app(LoggingService::class)->resetJobName();
        });
    }

    public function register()
    {
        $this->app->singleton(LoggingService::class, function ($app) {
            return new LoggingService();
        });
    }
}
