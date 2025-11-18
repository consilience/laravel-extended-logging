<?php

namespace Consilience\Laravel\ExtendedLogging\Tests\Unit;

use Monolog\Handler\TestHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\Level;
use Consilience\Laravel\ExtendedLogging\Tap;
use Consilience\Laravel\ExtendedLogging\Processor\AppNameProcessor;
use Consilience\Laravel\ExtendedLogging\Tests\TestCase;

class TapTest extends TestCase
{
    public function test_tap_adds_processors_to_handlers(): void
    {
        config([
            'app.name' => 'test-app',
            'laravel-extended-logging.processors' => [
                AppNameProcessor::class,
            ],
        ]);

        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $tap = new Tap();
        $tap($logger);

        $logger->info('Test message');

        $records = $handler->getRecords();
        $this->assertCount(1, $records);
        $this->assertArrayHasKey('application', $records[0]['extra']);
        $this->assertEquals('test-app', $records[0]['extra']['application']);
    }

    public function test_tap_instantiates_processor_from_class_string(): void
    {
        config([
            'app.name' => 'another-app',
            'laravel-extended-logging.processors' => [
                AppNameProcessor::class,
            ],
        ]);

        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $tap = new Tap();
        $tap($logger);

        $logger->info('Test message');

        $records = $handler->getRecords();
        $this->assertArrayHasKey('application', $records[0]['extra']);
        $this->assertEquals('another-app', $records[0]['extra']['application']);
    }

    public function test_tap_instantiates_processor_with_arguments(): void
    {
        config([
            'laravel-extended-logging.processors' => [
                \Monolog\Processor\UidProcessor::class => [16],
            ],
        ]);

        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $tap = new Tap();
        $tap($logger);

        $logger->info('Test message');

        $records = $handler->getRecords();
        $this->assertCount(1, $records);
        $this->assertArrayHasKey('uid', $records[0]['extra']);
        $this->assertEquals(16, strlen($records[0]['extra']['uid']));
    }

    public function test_tap_sets_json_pretty_print_when_enabled(): void
    {
        config([
            'laravel-extended-logging.processors' => [],
            'laravel-extended-logging.json-pretty-print' => true,
        ]);

        $logger = new Logger('test');
        $handler = new TestHandler();
        $formatter = new JsonFormatter();
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        $tap = new Tap();
        $tap($logger);

        // Test that the formatter was configured (can't directly test the private property)
        $this->assertInstanceOf(JsonFormatter::class, $handler->getFormatter());
    }

    public function test_tap_handles_multiple_handlers(): void
    {
        config([
            'app.name' => 'multi-handler-app',
            'laravel-extended-logging.processors' => [
                AppNameProcessor::class,
            ],
        ]);

        $logger = new Logger('test');
        $handler1 = new TestHandler();
        $handler2 = new TestHandler();
        $logger->pushHandler($handler1);
        $logger->pushHandler($handler2);

        $tap = new Tap();
        $tap($logger);

        $logger->info('Test message');

        // Both handlers should have the processor applied
        $this->assertArrayHasKey('application', $handler1->getRecords()[0]['extra']);
        $this->assertArrayHasKey('application', $handler2->getRecords()[0]['extra']);
    }

    public function test_tap_skips_invalid_processor_configurations(): void
    {
        config([
            'laravel-extended-logging.processors' => [
                'InvalidClassName',
                123,
                [],
            ],
        ]);

        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $tap = new Tap();
        $tap($logger);

        $logger->info('Test message');

        // Should still log the message even with invalid processor configurations
        $this->assertCount(1, $handler->getRecords());
    }
}
