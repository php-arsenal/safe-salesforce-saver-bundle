<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use LogicItLab\Salesforce\MapperBundle\Mapper;
use PhpAmqpLib\Message\AMQPMessage;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;

/**
 * Class SafeSalesforceSaverServer
 * @package Comsave\SafeSalesforceSaver\Consumer
 */
class SafeSalesforceSaverServer
{
    /** @var Mapper */
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

    public function execute(AMQPMessage $message)
    {
        $payload = unserialize($message->body);
        if (count($payload) == 1) {
            $this->mapper->save($payload[0]);
        } else {
            foreach ($payload as $model) {
                $this->mappedBulkSaver->save($model);
            }
            $this->mappedBulkSaver->flush();
        }
    }
}