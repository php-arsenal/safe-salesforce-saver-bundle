<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use LogicItLab\Salesforce\MapperBundle\Mapper;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AsyncSfSaveConsumer
 * @package Comsave\SafeSalesforceSaverBundle\Consumer
 */
class AsyncSfSaveConsumer implements ConsumerInterface
{
    /* @var Mapper */
    private $mapper;

    /** @var MappedBulkSaver */
    private $mappedBulkSaver;

    /**
     * @param Mapper $mapper
     * @param MappedBulkSaver $mappedBulkSaver
     * @codeCoverageIgnore
     */
    public function __construct(Mapper $mapper, MappedBulkSaver $mappedBulkSaver)
    {
        $this->mapper = $mapper;
        $this->mappedBulkSaver = $mappedBulkSaver;
    }

    /**
     * @param AMQPMessage $message
     * @return mixed|void
     * @throws \Exception
     */
    public function execute(AMQPMessage $message): void
    {
        $payload = unserialize($message->body);

        if (!is_iterable($payload)) {
            $this->mapper->save($payload);
        } else {
            foreach ($payload as $model) {
                $this->mappedBulkSaver->save($model);
            }
            $this->mappedBulkSaver->flush();
        }
    }
}