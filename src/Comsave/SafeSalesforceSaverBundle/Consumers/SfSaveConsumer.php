<?php

namespace Comsave\Webservice\Core\CoreBundle\Consumer;

use LogicItLab\Salesforce\MapperBundle\Mapper;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class SfSaveConsumer
 * @package Comsave\Webservice\Core\CoreBundle\Consumer
 */
class SfSaveConsumer implements ConsumerInterface
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