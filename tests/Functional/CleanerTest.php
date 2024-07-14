<?php

declare(strict_types=1);

namespace G41797\Queue\Beanstalkd\Functional;

use G41797\Queue\Beanstalkd\Broker;

class CleanerTest extends FunctionalTestCase
{
    public function testPurgeQueue(): void
    {

    }


    static public function purgeQueue(): bool
    {
        try {
            $broker = new Broker();

            while (true) {
                $job = $broker->pull(2.0);
                if (null == $job){
                    return true;
                }
            }
        }
        catch (\Throwable $exception) {
            return false;
        }

        return true;
    }
}
