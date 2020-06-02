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
use Consilience\Laravel\ExtendedLogging\Processor\JobNameProcessor;

class Tap
{
    protected $uidLength = 16;

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
            $handler->pushProcessor(new JobNameProcessor());

            // Standard monolog processors.

            $handler->pushProcessor(new UidProcessor($this->uidLength));
            $handler->pushProcessor(new PsrLogMessageProcessor);
            $handler->pushProcessor(new ProcessIdProcessor);
            $handler->pushProcessor(new MemoryUsageProcessor);

            // Additional options.
            $handler->getFormatter()->setJsonPrettyPrint(true);
            $handler->getFormatter()->setJsonPrettyPrint(false);

            if (method_exists($handler->getFormatter(), 'setJsonPrettyPrint')) {
                if ((bool)config('laravel-extended-logging.json-pretty-print')) {
                    $handler->getFormatter()->setJsonPrettyPrint(true);
                } else {
                    // A current bug in monolog causes `false` to toggle the flag rather than reset it.
                    $handler->getFormatter()->setJsonPrettyPrint(true);
                    $handler->getFormatter()->setJsonPrettyPrint(false);
                }
            }
        }
    }
}
