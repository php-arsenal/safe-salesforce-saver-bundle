<?php

namespace PhpArsenal\SafeSalesforceSaverBundle\Services\SalesforceSaver;

use PhpArsenal\SafeSalesforceSaverBundle\Factory\ExceptionMessageFactory;
use PhpArsenal\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer;
use PhpArsenal\SafeSalesforceSaverBundle\Services\ModelSerializer;
use Psr\Log\LoggerInterface;

class AsyncSalesforceSaver
{
    /** @var AsyncSfSaverProducer */
    private $aSyncSaverProducer;

    /** @var ModelSerializer */
    private $modelSerializer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        AsyncSfSaverProducer $aSyncSaver,
        ModelSerializer $modelSerializer,
        LoggerInterface $logger
    ) {
        $this->aSyncSaverProducer = $aSyncSaver;
        $this->modelSerializer = $modelSerializer;
        $this->logger = $logger;
    }

    public function save($models): void
    {
        $serializedModels = $this->modelSerializer->serialize($models);

        $this->logger->debug(ExceptionMessageFactory::build($this, [
            'Scheduling for saving',
            $serializedModels
        ]));

        $this->aSyncSaverProducer->publish($serializedModels);

        $this->logger->debug(ExceptionMessageFactory::build($this, [
            'Scheduled for saving successfully',
            $serializedModels
        ]));
    }
}