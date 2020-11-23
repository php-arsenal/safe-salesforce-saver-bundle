<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use Comsave\SafeSalesforceSaverBundle\Factory\ExceptionMessageFactory;
use Comsave\SafeSalesforceSaverBundle\Services\ModelSerializer;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class SalesforceSaverConsumer implements ConsumerInterface
{
    /** @var MappedBulkSaver */
    private $mappedBulkSaver;

    /** @var ModelSerializer */
    private $modelSerializer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @codeCoverageIgnore
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
     * @return array|null
     * @throws \Throwable
     */
    public function execute(AMQPMessage $message)
    {
        $result = [];
        $this->logger->debug(ExceptionMessageFactory::build($this, [
            'Consuming',
            $message->body
        ]));

        $models = $this->unserializeModels($message);

        try {
            foreach ($models as $model) {
                $this->mappedBulkSaver->save($model);
            }

            $result = $this->mappedBulkSaver->flush();
        } catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, [
                'Failed to save to Salesforce',
                $ex->getMessage(),
                $message->body
            ]));

            if($this->shouldRequeue($ex)) {
                throw $ex;
            }
        }

        $this->logger->debug(ExceptionMessageFactory::build($this, [
            'Consumed',
            $message->body
        ]));

        return $result;
    }

    private function unserializeModels(AMQPMessage $message): array
    {
        try {
            return $this->modelSerializer->unserialize($message->body);
        }
        catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, [
                'Failed to unserialize message',
                $ex->getMessage(),
                $message->body
            ]));
            throw $ex;
        }
    }

    public function shouldRequeue(\Throwable $exception): bool
    {
        return (strpos($exception->getMessage(), 'org is locked') !== false
            || strpos($exception->getMessage(), 'Error Fetching http headers') !== false
            || strpos($exception->getMessage(), 'unable to obtain exclusive access') !== false);
    }
}