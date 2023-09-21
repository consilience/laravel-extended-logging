<?php

namespace Consilience\Laravel\ExtendedLogging\Processor;

/**
 * Add the local user ID to extra if a user is logged in.
 */

use Throwable;
use Monolog\LogRecord;
use Monolog\ResettableInterface;
use Illuminate\Support\Facades\Auth;
use Monolog\Processor\ProcessorInterface;

class AuthUserProcessor implements ProcessorInterface, ResettableInterface
{
    protected $userId;

    public function __invoke(LogRecord $record)
    {
        if ($userId = $this->getUserId()) {
            $record->extra['local_user_id'] = $userId;
        }

        return $record;
    }

    public function reset(): void
    {
        $this->userId = null;

        $this->userId = $this->getUserId();
    }

    /**
     * Check if the user is logged in on each call,
     * and cache teh user ID as soon as we have one.
     */
    protected function getUserId()
    {
        if ($this->userId) {
            return $this->userId;
        }

        try {
            if (Auth::check()) {
                return $this->userId = Auth::user()->id;
            }
        } catch (Throwable $throwable) {
            // Discard exception
        }
    }
}
