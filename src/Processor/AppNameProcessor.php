<?php

namespace Consilience\Laravel\ExtendedLogging\Processor;

/**
 * Add the local user ID to extra if a user is logged in.
 */

use Monolog\LogRecord;
use Monolog\ResettableInterface;
use Monolog\Processor\ProcessorInterface;

class AppNameProcessor implements ProcessorInterface
{
    protected $application;
    protected $subsystem;

    public function __construct()
    {
        $this->application = config('app.name');
        $this->subsystem = config('app.subsystem');
    }

    public function __invoke(LogRecord $record)
    {
        // Add system and subsystem names.

        if ($this->application) {
            $record->extra['application'] = $this->application;
        }

        if ($this->subsystem) {
            $record->extra['subsystem'] = $this->subsystem;
        }

        return $record;
    }
}
