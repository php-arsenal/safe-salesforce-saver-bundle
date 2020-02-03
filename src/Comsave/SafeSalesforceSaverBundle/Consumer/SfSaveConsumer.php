<?php

namespace Comsave\SafeSalesforceSaver\Consumer;

use LogicItLab\Salesforce\MapperBundle\Mapper;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Class SfSaveConsumer
 * @package Comsave\SafeSalesforceSaver\Consumer
 */
class SfSaveConsumer implements ConsumerInterface
{
    /* @var Mapper */
    private $mapper;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param Mapper $mapper
     * @param LoggerInterface $logger
     * @codeCoverageIgnore
     */
    public function __construct(Mapper $mapper, LoggerInterface $logger)
    {
        $this->mapper = $mapper;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Exception
     */
    public function execute(AMQPMessage $msg)
    {
        try{
            $this->mapper->save(unserialize($msg->body)['model']);
        } catch (\Exception $e) {
            $this->logger->critical('Failed to save to Salesforce: ' . $e->getMessage());
            throw $e;
        }
    }
}