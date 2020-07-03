<?php

namespace Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver;

use Comsave\SafeSalesforceSaverBundle\Factory\ExceptionMessageFactory;
use Comsave\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer;
use Comsave\SafeSalesforceSaverBundle\Services\ModelSerializer;
use Psr\Log\LoggerInterface;

class AsyncSalesforceSaver
{
    /** @var AsyncSfSaverProducer */
    private $aSyncSaver;

    /** @var ModelSerializer */
    private $modelSerializer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * AsyncSalesforceSaver constructor.
     * @param AsyncSfSaverProducer $aSyncSaver
     * @param ModelSerializer $modelSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        AsyncSfSaverProducer $aSyncSaver,
        ModelSerializer $modelSerializer,
        LoggerInterface $logger
    ) {
        $this->aSyncSaver = $aSyncSaver;
        $this->modelSerializer = $modelSerializer;
        $this->logger = $logger;
    }

    public function save($models): void
    {
        $serializedModels = $this->modelSerializer->serialize($models);

        $this->logger->info(ExceptionMessageFactory::build($this, implode('. ', [
            'Scheduling for saving',
            $serializedModels
        ])));

        $this->aSyncSaver->publish($serializedModels);

        $this->logger->info(ExceptionMessageFactory::build($this, implode('. ', [
            'Scheduled for saving successfully.',
            $serializedModels
        ])));
    }
}