<?php

namespace Consilience\Laravel\ExtendedLogging;

/**
 * A tap to enable additional processors.
 */

use Monolog\Processor\ProcessorInterface;

class Tap
{
    const UID_LENGTH = 16;

    public function __invoke($logger)
    {
        // For each handler this tap has been attached to, add a series of
        // processors to inject additional data to the log record.
        // Future configuration options all support selecting which are
        // enabled.

        foreach ($logger->getHandlers() as $handler) {

            collect(config('laravel-extended-logging.processors'))
                ->each(function ($processor, $key) use ($handler) {
                    // Processor::class

                    if (is_string($processor) && class_exists($processor)) {
                        $processor = new $processor();
                    }

                    // Processor::class => [arguments, ...]

                    if (is_string($key) && is_array($processor) && class_exists($key)) {
                        $processor = new $key(...$processor);
                    }

                    // Processor (object)

                    if (! is_object($processor) || ! $processor instanceof ProcessorInterface) {
                        return;
                    }

                    $handler->pushProcessor($processor);
                });

            // Additional options.

            if (method_exists($handler->getFormatter(), 'setJsonPrettyPrint')) {
                if ((bool)config('laravel-extended-logging.json-pretty-print')) {
                    $handler->getFormatter()->setJsonPrettyPrint(true);
                } else {
                    // A workaround for a beug in older versions.
                    // See https://github.com/Seldaek/monolog/issues/1469

                    $handler->getFormatter()->setJsonPrettyPrint(true);
                    $handler->getFormatter()->setJsonPrettyPrint(false);
                }
            }
        }
    }
}
