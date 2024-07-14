# Yii3 Queue Adapter for Beanstalkd - simple, fast work queue


[![tests](https://github.com/g41797/queue-beanstalkd/actions/workflows/tests.yml/badge.svg)](https://github.com/g41797/queue-beanstalkd/actions/workflows/tests.yml)

## Description

Yii3 Queue Adapter for [**Beanstalkd**](https://beanstalkd.github.io/) is adapter in [Yii3 Queue Adapters family.](https://github.com/yiisoft/queue/blob/master/docs/guide/en/adapter-list.md)

Implementation of adapter is based on [enqueue/pheanstalk](https://github.com/php-enqueue/pheanstalk) library.

## Requirements

- PHP 8.2 or higher.

## Installation

The package could be installed with composer:

```shell
composer require g41797/queue-beanstalkd
```

## General usage

- As part of [Yii3 Queue Framework](https://github.com/yiisoft/queue/blob/master/docs/guide/en/README.md)
- Stand-alone


## Configuration

Default configuration:
```php
[
    'host' => '127.0.0.1',  // IP or hostname of the target server
    'port' => 11300,         // TCP/IP port of the target server
]
``` 

## Limitations

### Job Status
  [Job Status](https://github.com/yiisoft/queue/blob/master/docs/guide/en/usage.md#job-status)
```php
// Push a job into the queue and get a message ID.
$id = $queue->push(new SomeJob());

// Get job status.
$status = $queue->status($id);
```
is not supported.

## License

Yii3 Queue Adapter for Beanstalkd is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.
