<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use LogicItLab\Salesforce\MapperBundle\Mapper;
use PhpAmqpLib\Message\AMQPMessage;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use Psr\Log\LoggerInterface;

/**
 * Class SafeSalesforceSaverServer
 * @package Comsave\SafeSalesforceSaverBundle\Consumer
 */
class SafeSalesforceSaverServer
{
    /** @var Mapper */
    private $mapper;

    /** @var MappedBulkSaver */
    private $mappedBulkSaver;

    /**
     * @var LoggerInterface
     */
    private $logger;

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

        if (!is_iterable($payload)) {
            $this->mapper->save($payload);
            $returnValue = serialize($payload);
        } else {
            foreach ($payload as $model) {
                $this->mappedBulkSaver->save($model);
            }

            $returnValue = serialize($this->mappedBulkSaver->flush());
        }

        return $returnValue;
    }
}