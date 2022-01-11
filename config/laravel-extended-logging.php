<?php

use Consilience\Laravel\ExtendedLogging\Processor\AppNameProcessor;
use Consilience\Laravel\ExtendedLogging\Processor\AuthUserProcessor;
use Consilience\Laravel\ExtendedLogging\Processor\JobNameProcessor;
use Consilience\Laravel\ExtendedLogging\Tap;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\UidProcessor;

return [
    // Set to "pretty print" JSON output for easier reading during development.

    'json-pretty-print' => false,

    // Processors of Monolog\Processor\ProcessorInterface to use for each log.
    // This package provides a few, monolog provides a bunch
    // built in, and you can add your own.

    'processors' => [
        // Custom processors.

        AuthUserProcessor::class,
        AppNameProcessor::class,
        JobNameProcessor::class,

        // Standard monolog processors.

        UidProcessor::class => [Tap::UID_LENGTH],
        PsrLogMessageProcessor::class,
        ProcessIdProcessor::class,
        MemoryUsageProcessor::class,
    ],
];
