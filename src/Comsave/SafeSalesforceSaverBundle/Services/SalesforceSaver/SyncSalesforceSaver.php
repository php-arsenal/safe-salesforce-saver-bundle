<?php

namespace Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver;

use Comsave\SafeSalesforceSaverBundle\Factory\ExceptionMessageFactory;
use Comsave\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
use Comsave\SafeSalesforceSaverBundle\Services\ModelSerializer;
use Psr\Log\LoggerInterface;

class SyncSalesforceSaver
{
    /** @var RpcSfSaverClient */
    private $rpcClient;

    /** @var ModelSerializer */
    private $modelSerializer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * SyncSalesforceSaver constructor.
     * @param RpcSfSaverClient $rpcClient
     * @param ModelSerializer $modelSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(RpcSfSaverClient $rpcClient, ModelSerializer $modelSerializer, LoggerInterface $logger)
    {
        $this->rpcClient = $rpcClient;
        $this->modelSerializer = $modelSerializer;
        $this->logger = $logger;
    }

    public function save($models): void
    {
        $serializedModels = $this->modelSerializer->serialize($models);

        $this->logger->info(ExceptionMessageFactory::build($this, implode('. ', [
            'Saving',
            $serializedModels
        ])));

        try {
            $savedSerializedModels = $this->rpcClient->call($serializedModels);
        } catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, implode('. ', [
                'Failed to save to Salesforce',
                $ex->getMessage(),
                $serializedModels
            ])));
            throw $ex;
        }

        try {
            $unserializedSavedModels = $this->modelSerializer->unserialize($savedSerializedModels);
        }
        catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, implode('. ', [
                'Failed to unserialize message',
                $ex->getMessage(),
                $savedSerializedModels
            ])));
            throw $ex;
        }

        $this->setModelIds($models, $unserializedSavedModels);

        $this->logger->info(ExceptionMessageFactory::build($this, implode('. ', [
            'Saved',
            $this->modelSerializer->serialize($models)
        ])));
    }

    /**
     * @param $models
     * @return object|object[]
     * @throws \Comsave\SafeSalesforceSaverBundle\Exception\TimeoutException
     * @throws \Comsave\SafeSalesforceSaverBundle\Exception\UnidentifiedMessageException
     * @throws \Throwable
     */
    public function setModelIds($models, $unserializedSavedModels): void
    {
        $createdModels = $unserializedSavedModels['created'] ?? [];
        $createdModels = array_values($createdModels);
        $models = is_array($models) ?: [$models];

        foreach($models as $i => $model) {
            if (isset($createdModels[$i])
                && method_exists($createdModels[$i], 'getId')
                && !$models[$i]->getId()
                && method_exists($models[$i], 'setId')) {

                $models[$i]->setId($createdModels[$i]->getId());
            }
        }
    }
}