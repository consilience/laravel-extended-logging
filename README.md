
# Laravel and Lumen Extended Logging

Provide some ready-made logging extensions to make it easier to deply
Laravel and Lumen into multiple containers.

The main features are:

* A bunch of useful standard monolog processors.
* Laravel user ID, if available.
* Fully qualified class name of the job that is running.
* A sequence number so logs can be reordered when they get mixed up.
* Application name and subsystem name.

## Installation

While under early development, in `composer.json`:

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/consilience/laravel-extended-logging.git"
        }
    ]
```

Then:

    php composer require "consilience/laravel-extended-logging: *"

Lumen requires the provider to be registered in `bootstrap/app.php` so that the
package can keep track of the name of the job currently running:

    $app->register(Consilience\Laravel\ExtendedLogging\LoggingServiceProvider::class);

## Configuration

The main configiration happens through the Laravel `config/logging.php`
configuration script, by adding a channel.

```php
<?php

use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use Consilience\Laravel\ExtendedLogging\Tap as ExtendedTap;

// ...

    'channels' => [
        'stderr' => [
            //
            // monolog is the bare driver.
            //
            'driver' => 'monolog',
            //
            // This is the handler to use within monolog, with any parameters
            // to configure it.
            // Handlers can be found in \Monolog\Handler namespace.
            //
            'handler' => StreamHandler::class,
            //
            // Parameters for the monolog handler.
            //
            'with' => [
                'stream' => 'php://stderr',
            ],
            //
            // The custom tap to offer additional manipulation of the log output.
            // Multiple taps from other packages can be used to enhance further.
            //
            'tap' => [
                ExtendedTap::class,
            ],
            //
            // The output formatter.
            // The standard Monolog json formatter has a structure of its own.
            //
            'formatter' => JsonFormatter::class,
            'formatter_with' => [],
        ],
    ],
```

## TODO

* Tests.
* Config to turn features on and off.
* Start some official releases.
