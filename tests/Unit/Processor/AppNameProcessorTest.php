<?php

namespace Consilience\Laravel\ExtendedLogging\Tests\Unit\Processor;

use Monolog\LogRecord;
use Monolog\Level;
use Consilience\Laravel\ExtendedLogging\Processor\AppNameProcessor;
use Consilience\Laravel\ExtendedLogging\Tests\TestCase;

class AppNameProcessorTest extends TestCase
{
    public function test_adds_application_name_to_log_record(): void
    {
        config(['app.name' => 'test-app']);

        $processor = new AppNameProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        $this->assertArrayHasKey('application', $result->extra);
        $this->assertEquals('test-app', $result->extra['application']);
    }

    public function test_adds_subsystem_name_to_log_record(): void
    {
        config(['app.subsystem' => 'test-subsystem']);

        $processor = new AppNameProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        $this->assertArrayHasKey('subsystem', $result->extra);
        $this->assertEquals('test-subsystem', $result->extra['subsystem']);
    }

    public function test_does_not_add_application_when_not_configured(): void
    {
        config(['app.name' => null]);

        $processor = new AppNameProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        $this->assertArrayNotHasKey('application', $result->extra);
    }

    public function test_does_not_add_subsystem_when_not_configured(): void
    {
        config(['app.subsystem' => null]);

        $processor = new AppNameProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        $this->assertArrayNotHasKey('subsystem', $result->extra);
    }
}
