[![Latest Stable Version](https://poser.pugx.org/consilience/laravel-extended-logging/v/stable)](https://packagist.org/packages/consilience/laravel-extended-logging)
[![Total Downloads](https://poser.pugx.org/consilience/laravel-extended-logging/downloads)](https://packagist.org/packages/consilience/laravel-extended-logging)
[![Latest Unstable Version](https://poser.pugx.org/consilience/laravel-extended-logging/v/unstable)](https://packagist.org/packages/consilience/laravel-extended-logging)
[![License](https://poser.pugx.org/consilience/laravel-extended-logging/license)](https://packagist.org/packages/consilience/laravel-extended-logging)

<!-- TOC -->

- [Laravel and Lumen Extended Logging](#laravel-and-lumen-extended-logging)
    - [Installation](#installation)
    - [Configuration](#configuration)
        - [Configuration Upgrade](#configuration-upgrade)
    - [Example Usage](#example-usage)
    - [TODO](#todo)

<!-- /TOC -->

# Laravel Extended Logging

Provide some ready-made logging extensions to make it easier to deploy
Laravel into multiple containers.

The main features are:

* A bunch of useful standard monolog processors.
* Laravel user ID, if available.
* Fully qualified class name of the job that is running.
* A sequence number so logs can be reordered when they get mixed up.
* Application name and subsystem name.
* Logs written in structured JSON.

The features applied to the logging here are lightweight, opinionated, mainly non-configurable,
and are what we have found to be very useful for our own projects.
We are happy to accept PRs for additional features, though with the consideration that this
package is not intended to be "all singing, all dancing", but rather a quick and easy install
that gets an application logging container-ready with minimal effort.

## Installation

For Laravel:

    php composer require consilience/laravel-extended-logging

Lumen requires the provider to be registered in `bootstrap/app.php` so that the
package can keep track of the name of the job currently running:

    $app->register(Consilience\Laravel\ExtendedLogging\LoggingServiceProvider::class);

## Configuration

The main configuration happens through the Laravel `config/logging.php`
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
            // Here send to a file stream.
            //
            'handler' => StreamHandler::class,
            //
            // Parameters for the monolog handler.
            // Here the file stream is stderr.
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

A more compact version of the config entry, to go into the `channels` section of `config/logging.php`:

```php
use Monolog\Handler\StreamHandler; // Most likely already present.
use Monolog\Formatter\JsonFormatter;
use Consilience\Laravel\ExtendedLogging\Tap as ExtendedTap;
```

```php
    'channels' => [

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

        //...
    ],
```

Then set `LOG_CHANNEL=container` when running in a container to send all logs to `stderr`.
Other channels may be more suitable for other environments.

Additional options are available by publishing the config file (`laravel-extended-logging.php`)
for the package:

    php artisan vendor:publish --tag=laravel-extended-logging-config

Two options are supported at this time:

* `json-pretty-print` - set to `true` to format the JSON output to be more human readable
* `processor` - a list of monolog processor classes

The list of processors, by default, will include the custom processors provided by this
package, and a few of the processors that monolog provides.
You can remove what you don't want, and add any others you may need.

If a processor accepts parameters, use this form:

    Processor::class => [parameter1, parameter2, ...],

Only positional parameters are supported at this time.

The configuration file still accepts instantiated processor objects for legacy installs.
You should change those to the uninstantiated `Processor::class` form to support configuration
cacheing.

### Configuration Upgrade

Since release 1.2.0 the processors to run have been located in the config file.
You will need to publish the config file again to use the processors.

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
