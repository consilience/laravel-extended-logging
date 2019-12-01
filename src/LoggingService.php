<?php

namespace Consilience\Laravel\ExtendedLogging;

/**
 *
 */

class LoggingService
{
    protected $jobName;

    public function setJobName(string $jobName)
    {
        $this->jobName = $jobName;
    }

    public function getJobName()
    {
        return $this->jobName;
    }

    public function resetJobName()
    {
        $this->jobName = null;
    }
}
