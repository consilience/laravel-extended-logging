<?php

namespace Consilience\Laravel\ExtendedLogging\Tests\Feature;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Consilience\Laravel\ExtendedLogging\LoggingService;
use Consilience\Laravel\ExtendedLogging\Tests\TestCase;

class LoggingServiceProviderTest extends TestCase
{
    public function test_logging_service_is_registered_as_singleton(): void
    {
        $service1 = app(LoggingService::class);
        $service2 = app(LoggingService::class);

        $this->assertSame($service1, $service2);
    }

    public function test_config_is_merged(): void
    {
        $this->assertIsArray(config('laravel-extended-logging.processors'));
        $this->assertIsBool(config('laravel-extended-logging.json-pretty-print'));
    }

    public function test_queue_before_listener_sets_job_name(): void
    {
        $loggingService = app(LoggingService::class);
        $loggingService->resetJobName();

        $mockJob = new class {
            public function payload()
            {
                return ['displayName' => 'App\\Jobs\\TestJob'];
            }
        };

        $event = new JobProcessing('default', $mockJob);

        Queue::before(function (JobProcessing $event) {
            $displayName = $event->job->payload()['displayName'] ?? null;
            if (is_string($displayName)) {
                app(LoggingService::class)->setJobName($displayName);
            }
        });

        // Trigger the event
        event($event);

        $this->assertEquals('App\\Jobs\\TestJob', $loggingService->getJobName());
    }

    public function test_queue_after_listener_resets_job_name(): void
    {
        $loggingService = app(LoggingService::class);
        $loggingService->setJobName('App\\Jobs\\SomeJob');

        $mockJob = new class {
            public function payload()
            {
                return [];
            }
        };

        $event = new JobProcessed('default', $mockJob);

        Queue::after(function (JobProcessed $event) {
            app(LoggingService::class)->resetJobName();
        });

        // Trigger the event
        event($event);

        $this->assertNull($loggingService->getJobName());
    }

    public function test_queue_failing_listener_resets_job_name(): void
    {
        $loggingService = app(LoggingService::class);
        $loggingService->setJobName('App\\Jobs\\FailingJob');

        $mockJob = new class {
            public function payload()
            {
                return [];
            }
        };

        $exception = new \Exception('Job failed');
        $event = new JobFailed('default', $mockJob, $exception);

        Queue::failing(function (JobFailed $event) {
            app(LoggingService::class)->resetJobName();
        });

        // Trigger the event
        event($event);

        $this->assertNull($loggingService->getJobName());
    }
}
