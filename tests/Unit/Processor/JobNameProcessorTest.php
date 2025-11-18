<?php

namespace Consilience\Laravel\ExtendedLogging\Tests\Unit\Processor;

use Monolog\LogRecord;
use Monolog\Level;
use Consilience\Laravel\ExtendedLogging\LoggingService;
use Consilience\Laravel\ExtendedLogging\Processor\JobNameProcessor;
use Consilience\Laravel\ExtendedLogging\Tests\TestCase;

class JobNameProcessorTest extends TestCase
{
    public function test_adds_job_name_to_log_record_when_set(): void
    {
        $loggingService = app(LoggingService::class);
        $loggingService->setJobName('App\\Jobs\\TestJob');

        $processor = new JobNameProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        $this->assertArrayHasKey('job_name', $result->extra);
        $this->assertEquals('App\\Jobs\\TestJob', $result->extra['job_name']);
    }

    public function test_does_not_add_job_name_when_not_set(): void
    {
        $loggingService = app(LoggingService::class);
        $loggingService->resetJobName();

        $processor = new JobNameProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        $this->assertArrayNotHasKey('job_name', $result->extra);
    }

    public function test_job_name_updates_correctly(): void
    {
        $loggingService = app(LoggingService::class);
        $processor = new JobNameProcessor();

        $loggingService->setJobName('FirstJob');
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);
        $this->assertEquals('FirstJob', $result->extra['job_name']);

        $loggingService->setJobName('SecondJob');
        $result = $processor($record);
        $this->assertEquals('SecondJob', $result->extra['job_name']);
    }
}
