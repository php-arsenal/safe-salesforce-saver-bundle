<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use Comsave\SafeSalesforceSaverBundle\Factory\ExceptionMessageFactory;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Class AsyncSfSaveConsumer
 * @package Comsave\SafeSalesforceSaverBundle\Consumer
 */
class AsyncSfSaveConsumer implements ConsumerInterface
{
    /** @var MappedBulkSaver */
    private $mappedBulkSaver;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param MappedBulkSaver $mappedBulkSaver
     * @param LoggerInterface $logger
     * @codeCoverageIgnore
     */
    public function __construct(MappedBulkSaver $mappedBulkSaver, LoggerInterface $logger)
    {
        $this->mappedBulkSaver = $mappedBulkSaver;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $message
     * @throws \Throwable
     */
    public function execute(AMQPMessage $message): void
    {
        $models = $this->unserializeModels($message);

        try {
            foreach ($models as $model) {
                $this->mappedBulkSaver->save($model);
            }

            $this->mappedBulkSaver->flush();
        } catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, implode('. ', [
                'Failed to save to Salesforce',
                $ex->getMessage(),
                $message->body
            ])));
            throw $ex;
        }
    }

    private function unserializeModels(AMQPMessage $message): array
    {
        try {
            return unserialize($message->body);
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