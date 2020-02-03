<?php

namespace Comsave\SafeSalesforceSaver\Consumer;

use LogicItLab\Salesforce\MapperBundle\Mapper;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AsyncSfSaveConsumer
 * @package Comsave\SafeSalesforceSaver\Consumer
 */
class AsyncSfSaveConsumer implements ConsumerInterface
{
    /* @var Mapper */
    private $mapper;

    /**
     * @param Mapper $mapper
     * @codeCoverageIgnore
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Exception
     */
    public function execute(AMQPMessage $msg)
    {
        $this->mapper->save(unserialize($msg->body)['model']);
    }
}