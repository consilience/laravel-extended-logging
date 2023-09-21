<?php

namespace Consilience\Laravel\ExtendedLogging\Processor;

/**
 * Add the currently running job name to the log messages.
 */

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Consilience\Laravel\ExtendedLogging\LoggingService;

class JobNameProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
    {
        $jobName = app(LoggingService::class)->getJobName();

        if ($jobName) {
            $record->extra['job_name'] = $jobName;
        }

        return $record;
    }
}
