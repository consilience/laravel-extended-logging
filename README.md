[![Latest Stable Version](https://poser.pugx.org/consilience/laravel-extended-logging/v/stable)](https://packagist.org/packages/consilience/laravel-extended-logging)
[![Total Downloads](https://poser.pugx.org/consilience/laravel-extended-logging/downloads)](https://packagist.org/packages/consilience/laravel-extended-logging)
[![Latest Unstable Version](https://poser.pugx.org/consilience/laravel-extended-logging/v/unstable)](https://packagist.org/packages/consilience/laravel-extended-logging)
[![License](https://poser.pugx.org/consilience/laravel-extended-logging/license)](https://packagist.org/packages/consilience/laravel-extended-logging)

# Laravel and Lumen Extended Logging

Provide some ready-made logging extensions to make it easier to deploy
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
        'my-extended-logging-channel' => [
            //
            // monolog is the underlying driver.
            //
            'driver' => 'monolog',
            //
            // This is the handler to use within monolog, with any parameters to configure it.
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
            // Other taps from other packages can be added here to extend further.
            //
            'tap' => [
                ExtendedTap::class,
            ],
            //
            // The output formatter.
            // The standard Monolog json formatter has a good structure that is easy to parse.
            //
            'formatter' => JsonFormatter::class,
            'formatter_with' => [],
        ],
    ],
```

A shortened version of the config entry, to go into the `channels` section of `config/logging.php`:

```php
use Monolog\Handler\StreamHandler; // Most likely already present.
use Monolog\Formatter\JsonFormatter;
use Consilience\Laravel\ExtendedLogging\Tap as ExtendedTap;
```

```json
        // Use this channel for running in a container.
        // Sends all logs to stderr in a structured form, with additional metadata.
        // Can be mixed in a stack with other channels.
        'container' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stderr',
            ],
            'tap' => [
                ExtendedTap::class,
            ],
            'formatter' => JsonFormatter::class,
            'formatter_with' => [],
        ],
```

Then set `LOG_CHANNEL=container` when running in a container to send all logs to `stderr`.
Use other channels for other environments.

## Example Usage

We run Laravel and Lumen applications in a Kubernetes/Docker environment,
with all log entries being indexed by elastic search and presented by Kibana.
This lumps all our logs from multiple applications, multiple pods and containers,
and multiple jobs, into one big database.

To search and filter those log entries, it is vital for context information to
be logged in a filterable way.

The `PsrLogMessageProcessor`, included in this log tap, makes it very easy to combine
context data and log message construction.
Logging looks like this as a result, with both a context array of data, and the log
message with field replacements done:

```php
Log::debug('Product {productId} added to category {categorySlug}', [
    'productId' => $product->id,
    'productName' => $product->name,
    'categorySlug' => $category->slug,
]);
```

The generated log message would look something like this, embedded into
whatever you use to capture and wrap the log messages.

```json
  "_source": {
    "@timestamp": "2020-03-17T11:45:15.573Z",
    "stream": "stderr",
    "time": "2020-03-17T11:45:15.57341869Z",
    "message": "Product 123 added to category good-stuff",
    "context": {
      "productId": 123,
      "productName": "A Nice Slice of Cake",
      "categorySlug": "good-stuff",
    },
    "level": 100,
    "level_name": "DEBUG",
    "channel": "development",
    "datetime": {
      "date": "2020-03-17 11:45:15.572810",
      "timezone_type": 3,
      "timezone": "UTC"
    },
    "extra": {
      "memory_usage": "24 MB",
      "process_id": 1,
      "uid": "58bcec3ef88a7ceb",
      "job_name": "App\\Jobs\\ImportProductCategories",
      "application": "great-shop",
      "subsystem": "admin-app"
    },
  },
```    

## TODO

* Tests.
* Config to turn features on and off.
