<?php

namespace PhpArsenal\SafeSalesforceSaverBundle\Services\SalesforceSaver;

use PhpArsenal\SafeSalesforceSaverBundle\Factory\ExceptionMessageFactory;
use PhpArsenal\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
use PhpArsenal\SafeSalesforceSaverBundle\Services\ModelSerializer;
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
     * @codeCoverageIgnore
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

        $this->logger->debug(ExceptionMessageFactory::build($this, [
            'Saving',
            $serializedModels
        ]));

        try {
            $savedSerializedModels = $this->rpcClient->call($serializedModels);
        } catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, [
                'Failed to save to Salesforce',
                $ex->getMessage(),
                $serializedModels
            ]));
            throw $ex;
        }

        $unserializedSavedModels = $this->unserializeModels($savedSerializedModels);
        $this->setCreatedModelIds($models, $unserializedSavedModels);

        $this->logger->debug(ExceptionMessageFactory::build($this, [
            'Saved',
            $savedSerializedModels
        ]));
    }

    private function unserializeModels(string $serializedModels): array
    {
        try {
            return $this->modelSerializer->unserialize($serializedModels);
        }
        catch (\Throwable $ex) {
            $this->logger->error(ExceptionMessageFactory::build($this, [
                'Failed to unserialize message',
                $ex->getMessage(),
                $serializedModels
            ]));
            throw $ex;
        }
    }

    /**
     * @param $models
     * @return object|object[]
     * @throws \PhpArsenal\SafeSalesforceSaverBundle\Exception\TimeoutException
     * @throws \PhpArsenal\SafeSalesforceSaverBundle\Exception\UnidentifiedMessageException
     * @throws \Throwable
     */
    public function setCreatedModelIds($models, $unserializedSavedModels): void
    {
        $createdModels = is_array($unserializedSavedModels) && isset($unserializedSavedModels['created']) ? $unserializedSavedModels['created'] : [];
        $createdModels = array_values($createdModels);
        $models = is_array($models) ? array_values($models) : [$models];

        foreach($createdModels as $i => $createdModel) {
            if (!isset($models[$i])) {
                continue;
            }

            $modelRef = new \ReflectionClass($models[$i]);
            $idProp = $modelRef->getProperty('id');
            $idProp->setAccessible(true);

            if(method_exists($createdModel, 'getId')
                && $modelRef->hasProperty('id')
                && !$idProp->getValue($models[$i])) {
                $idProp->setValue($models[$i], $createdModel->getId());
            }
        }
    }
}