<?php

namespace Consilience\Laravel\ExtendedLogging;

/**
 * A tap to enable additional processors.
 */

use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\MemoryUsageProcessor;

use Consilience\Laravel\ExtendedLogging\Processor\AuthUserProcessor;
use Consilience\Laravel\ExtendedLogging\Processor\AppNameProcessor;

class Tap
{
    protected $uidLength = 16;

    public function __construct()
    {
        // TODO: set up config.
    }

    public function __invoke($logger)
    {
        // For each handler this tap has been attached to, add a series of
        // processors to inject additional data to the log record.
        // Future configuration options all support selecting which are
        // enabled.

        foreach ($logger->getHandlers() as $handler) {
            // Custom processors.

            $handler->pushProcessor(new AuthUserProcessor());
            $handler->pushProcessor(new AppNameProcessor());

            // Standard monolog processors.

            $handler->pushProcessor(new UidProcessor($this->uidLength));
            $handler->pushProcessor(new PsrLogMessageProcessor);
            $handler->pushProcessor(new ProcessIdProcessor);
            $handler->pushProcessor(new MemoryUsageProcessor);
        }
    }
}
