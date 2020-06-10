<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use LogicItLab\Salesforce\MapperBundle\Mapper;
use PhpAmqpLib\Message\AMQPMessage;
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

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param Mapper $mapper
     * @param MappedBulkSaver $mappedBulkSaver
     * @param LoggerInterface $logger
     * @codeCoverageIgnore
     */
    public function __construct(Mapper $mapper, MappedBulkSaver $mappedBulkSaver, LoggerInterface $logger)
    {
        $this->mapper = $mapper;
        $this->mappedBulkSaver = $mappedBulkSaver;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $message
     * @return mixed
     * @throws \Throwable
     */
    public function execute(AMQPMessage $message)
    {
        try {
            $payload = unserialize($message->body);

            if (count($payload) == 1) {
                $this->mapper->save($payload[0]);
                $returnValue = $payload[0];
            } else {
                foreach ($payload as $model) {
                    $this->mappedBulkSaver->save($model);
                }

                $returnValue = $this->mappedBulkSaver->flush();
            }
        } catch (\Throwable $e) {
            $this->logger->error('SafeSalesforceSaver - message: ' . $e->getMessage() . ' - body: ' . $message->body . ' - end of SafeSalesforceSaver message');

            if (strpos($e->getMessage(), 'unable to obtain exclusive access') !== false) {
                throw $e;
            }
            $returnValue = serialize($e->getMessage());
        }

        return $returnValue;
    }
}