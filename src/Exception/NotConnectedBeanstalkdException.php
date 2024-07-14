<?php

declare(strict_types=1);

namespace G41797\Queue\Beanstalkd\Exception;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class NotConnectedBeanstalkdException extends \RuntimeException implements FriendlyExceptionInterface
{
    public function getName(): string
    {
        return 'Not connected to Beanstalkd.';
    }

    public function getSolution(): ?string
    {
        return 'Check your Beanstalkd configuration.';
    }
}

