<?php

namespace Consilience\Laravel\ExtendedLogging\Tests\Unit;

use Consilience\Laravel\ExtendedLogging\LoggingService;
use Consilience\Laravel\ExtendedLogging\Tests\TestCase;

class LoggingServiceTest extends TestCase
{
    protected LoggingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoggingService();
    }

    public function test_can_set_job_name(): void
    {
        $this->service->setJobName('TestJob');

        $this->assertEquals('TestJob', $this->service->getJobName());
    }

    public function test_can_get_job_name(): void
    {
        $this->assertNull($this->service->getJobName());

        $this->service->setJobName('AnotherJob');

        $this->assertEquals('AnotherJob', $this->service->getJobName());
    }

    public function test_can_reset_job_name(): void
    {
        $this->service->setJobName('SomeJob');
        $this->assertEquals('SomeJob', $this->service->getJobName());

        $this->service->resetJobName();

        $this->assertNull($this->service->getJobName());
    }

    public function test_job_name_can_be_updated(): void
    {
        $this->service->setJobName('FirstJob');
        $this->assertEquals('FirstJob', $this->service->getJobName());

        $this->service->setJobName('SecondJob');
        $this->assertEquals('SecondJob', $this->service->getJobName());
    }
}
