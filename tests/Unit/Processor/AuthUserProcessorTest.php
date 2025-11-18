<?php

namespace Consilience\Laravel\ExtendedLogging\Tests\Unit\Processor;

use Monolog\LogRecord;
use Monolog\Level;
use Illuminate\Support\Facades\Auth;
use Consilience\Laravel\ExtendedLogging\Processor\AuthUserProcessor;
use Consilience\Laravel\ExtendedLogging\Tests\TestCase;

class AuthUserProcessorTest extends TestCase
{
    public function test_does_not_add_user_id_when_not_authenticated(): void
    {
        $processor = new AuthUserProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        $this->assertArrayNotHasKey('local_user_id', $result->extra);
    }

    public function test_adds_user_id_when_authenticated(): void
    {
        // Create a mock user
        $user = new class {
            public $id = 123;
        };

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);

        $processor = new AuthUserProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        $this->assertArrayHasKey('local_user_id', $result->extra);
        $this->assertEquals(123, $result->extra['local_user_id']);
    }

    public function test_caches_user_id_after_first_retrieval(): void
    {
        $user = new class {
            public $id = 456;
        };

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $processor = new AuthUserProcessor();

        // First call
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );
        $result = $processor($record);
        $this->assertEquals(456, $result->extra['local_user_id']);

        // Second call - should use cached value
        $result = $processor($record);
        $this->assertEquals(456, $result->extra['local_user_id']);
    }

    public function test_reset_clears_cached_user_id(): void
    {
        $user = new class {
            public $id = 789;
        };

        Auth::shouldReceive('check')->twice()->andReturn(true);
        Auth::shouldReceive('user')->twice()->andReturn($user);

        $processor = new AuthUserProcessor();

        // First call
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );
        $result = $processor($record);
        $this->assertEquals(789, $result->extra['local_user_id']);

        // Reset
        $processor->reset();

        // Second call after reset - should fetch again
        $result = $processor($record);
        $this->assertEquals(789, $result->extra['local_user_id']);
    }

    public function test_handles_exceptions_gracefully(): void
    {
        Auth::shouldReceive('check')->andThrow(new \Exception('Auth error'));

        $processor = new AuthUserProcessor();
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $processor($record);

        // Should not throw exception and should not add user_id
        $this->assertArrayNotHasKey('local_user_id', $result->extra);
    }
}
