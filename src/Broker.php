<?php

declare(strict_types=1);

namespace G41797\Queue\Beanstalkd;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Ramsey\Uuid\Uuid;

use Yiisoft\Queue\Enum\JobStatus;
use Yiisoft\Queue\Message\IdEnvelope;
use Yiisoft\Queue\Message\JsonMessageSerializer;
use Yiisoft\Queue\Message\MessageInterface;

use Interop\Queue\Producer;

use Enqueue\Pheanstalk\PheanstalkContext;
use Enqueue\Pheanstalk\PheanstalkConsumer;
use Enqueue\Pheanstalk\PheanstalkConnectionFactory;
use Enqueue\Pheanstalk\PheanstalkDestination;

use G41797\Queue\Beanstalkd\Configuration as BrokerConfiguration;
use G41797\Queue\Beanstalkd\Exception\NotSupportedStatusMethodException;
use G41797\Queue\Beanstalkd\Exception\NotConnectedBeanstalkdException;


class Broker implements BrokerInterface
{
    public const SUBSCRIPTION_NAME = 'jobs';

    private string $queueName;

    private JsonMessageSerializer $serializer;

    public function __construct(
        private string                 $channelName = Adapter::DEFAULT_CHANNEL_NAME,
        public ?BrokerConfiguration    $configuration = null,
        public ?LoggerInterface        $logger = null
    ) {
        $this->serializer = new JsonMessageSerializer();

        $this->queueName = $this->channelName;

        if (null == $configuration) {
            $this->configuration = new BrokerConfiguration();
        }

        if (null == $logger) {
            $this->logger = new NullLogger();
        }

        return;
    }

    static public function default(): Broker
    {
        return new Broker();
    }

    public function withChannel(string $channel): BrokerInterface
    {
        if ($channel == $this->channelName) {
            return $this;
        }

        return new self($channel, $this->configuration, $this->logger);
    }

    private ?Producer $producer = null;
    public function push(MessageInterface $job): ?IdEnvelope
    {
        $this->prepare();

        if ($this->producer == null) {
            $this->producer = $this->beanstalkd->createProducer();
        }

        $env = $this->submit($job);

        if ($env == null)
        {
            $this->producer = null;
        }

        return $env;
    }

    private function submit(MessageInterface $job): ?IdEnvelope
    {
        try {
            $jobId      = Uuid::uuid7()->toString();
            $payload    = $this->serializer->serialize($job);

            $beanstalkdMsg     = $this->beanstalkd->createMessage(body: $payload, properties:['jobid' => $jobId]);

            $this->producer->send($this->queue, $beanstalkdMsg);

            return new IdEnvelope($job, $jobId);
        }
        catch (\Throwable ) {
            return null;
        }
    }

    public function jobStatus(string $id): ?JobStatus
    {
        throw new NotSupportedStatusMethodException();
    }

    private ?PheanstalkConsumer $receiver = null;

    public function pull(float $timeout): ?IdEnvelope
    {
        $this->prepare();

        if ($this->receiver == null)
        {
            $this->receiver = $this->beanstalkd->createConsumer($this->queue);
        }

        try
        {
            $beanstalkdMsg = $this->receiver->receive((int)(ceil($timeout*1000.0)));

            if (null == $beanstalkdMsg) { return null;}

            $job    = $this->serializer->unserialize($beanstalkdMsg->getBody());
            $jid    = $beanstalkdMsg->getProperty('jobid');

            $this->receiver->acknowledge($beanstalkdMsg);

            return new IdEnvelope($job, $jid);
        }
        catch (\Exception $exc) {
            $this->receiver = null;
            return null;
        }
    }
    public function clean(): int
    {
        $count = 0;

        while (true)
        {
            $recv = $this->pull(1.0);
            if ($recv == null)
            {
                break;
            }

            $count += 1;
        }

        return $count;
    }

    public function done(string $id): bool
    {
        return !empty($id);
    }

    public ?PheanstalkContext      $beanstalkd    = null;
    public PheanstalkDestination   $queue;

    private function prepare(): void
    {
        try
        {
            $this->init();
            return;
        }
        catch (\Exception $exc) {
            throw new NotConnectedBeanstalkdException();
        }
    }

    private function init(): void
    {
        if ($this->beanstalkd !== null)
        {
            return;
        }

        $beanstalkd = (new PheanstalkConnectionFactory($this->configuration->raw()))->createContext();
        $this->queue = $beanstalkd->createQueue($this->queueName);
        $this->beanstalkd   = $beanstalkd;

        return;
    }

}
