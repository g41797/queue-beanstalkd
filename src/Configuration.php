<?php

declare(strict_types=1);

namespace G41797\Queue\Beanstalkd;

final class Configuration
{
    private array $config = [];

    public function __construct(
        array $config = []
    ) {
        $this->config = array_replace(self::default(), $config);
    }

    public function update(array $config): self
    {
        $this->config = array_replace($this->config, $config);
        return $this;
    }

    public function raw(): array
    {
        return $this->config;
    }

    static public function default(): array
    {
        return [
            'host' => '127.0.0.1',
            'port' => 11300,
            'timeout' => null,
            'persisted' => false,
        ];
    }
}
