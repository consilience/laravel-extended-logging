<?php

namespace Consilience\Laravel\ExtendedLogging\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Consilience\Laravel\ExtendedLogging\LoggingServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LoggingServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set up any configuration needed for tests
        config()->set('app.name', 'test-application');
        config()->set('app.subsystem', 'test-subsystem');
    }
}
