<?php

namespace G41797\Queue\Beanstalkd\Exception;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class NotSupportedStatusMethodException  extends \RuntimeException implements FriendlyExceptionInterface
{
    public function getName(): string
    {
        return 'Not supported status API.';
    }

    public function getSolution(): ?string
    {
        return 'Do not use status API';
    }
}

