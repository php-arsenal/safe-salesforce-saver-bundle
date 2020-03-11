<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use LogicItLab\Salesforce\MapperBundle\Mapper;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

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
     * @throws \Throwable
     */
    public function execute(AMQPMessage $message): void
    {
        try {
            $payload = unserialize($message->body);

            if (count($payload) == 1) {
                $this->mapper->save($payload[0]);
            } else {
                foreach ($payload as $model) {
                    $this->mappedBulkSaver->save($model);
                }
                $this->mappedBulkSaver->flush();
            }
        } catch (\Throwable $e) {
            $this->logger->error('SafeSalesforceSaver - message: '. $e->getMessage() . ' - body: ' . $message->body);
            throw $e;
        }
    }
}