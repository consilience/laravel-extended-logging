
# Laravel and Lumen Extended Logging

The purpose of this package is to provide some ready-made logging
extensions to make it easier to deply Laravel and Lumen into multiple
containers.

The main features are:

* A bunch of useful standard monolog processors
* Laravel user ID.
* Application name and subsystem name.

## Installation

Until released to packagist, in `composer.json`:

```json
    "repositories": [
        {
            "type": "path",
            "url": "packages/laravel-extended-logging"
        }
    ]
```

Then:

    php composer require consilience/laravel-extended-logging

## Configuration

The main configiration happens through the Laravel `config/logging.php`
configuration script, by adding a channel.

```php
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
            'handler' => Monolog\Handler\StreamHandler::class,
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
                Consilience\Laravel\ExtendedLogging\Tap::class,
            ],
            //
            // The output formatter.
            // The standard Monolog json formatter has a structure of its own.
            //
            'formatter' => Monolog\Formatter\JsonFormatter::class,
            'formatter_with' => [],
        ],
    ],
```

## TODO

* Tests.
* Config to turn features on and off.
* Log classname of running job.
  A listener may be needed to grap the job class when it is initiated.
