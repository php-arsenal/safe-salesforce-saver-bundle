<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use Comsave\SafeSalesforceSaverBundle\Factory\ExceptionMessageFactory;
use Comsave\SafeSalesforceSaverBundle\Services\ModelSerializer;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Class SafeSalesforceSaverServer
 * @package Comsave\SafeSalesforceSaverBundle\Consumer
 */
class SafeSalesforceSaverServer
{
    /** @var MappedBulkSaver */
    private $mappedBulkSaver;

    /** @var ModelSerializer */
    private $modelSerializer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * SafeSalesforceSaverServer constructor.
     * @param MappedBulkSaver $mappedBulkSaver
     * @param ModelSerializer $modelSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        MappedBulkSaver $mappedBulkSaver,
        ModelSerializer $modelSerializer,
        LoggerInterface $logger
    ) {
        $this->mappedBulkSaver = $mappedBulkSaver;
        $this->modelSerializer = $modelSerializer;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $message
     * @return mixed
     * @throws \Throwable
     */
    public function execute(AMQPMessage $message): array
    {
        $this->logger->info(ExceptionMessageFactory::build($this, implode('. ', [
            'Consuming',
            $message->body
        ])));

        try {
            $models = $this->unserializeModels($message);

            foreach ($models as $model) {
                $this->mappedBulkSaver->save($model);
            }

            $result = $this->mappedBulkSaver->flush();
        } catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, implode('. ', [
                'Failed to save to Salesforce',
                $ex->getMessage(),
                $message->body
            ])));
            throw $ex;
        }

        $this->logger->info(ExceptionMessageFactory::build($this, implode('. ', [
            'Consumed',
            $message->body
        ])));

        return $result;
    }

    private function unserializeModels(AMQPMessage $message): array
    {
        try {
            return $this->modelSerializer->unserialize($message->body);
        }
        catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, implode('. ', [
                'Failed to unserialize message',
                $ex->getMessage(),
                $message->body
            ])));
            throw $ex;
        }
    }
}