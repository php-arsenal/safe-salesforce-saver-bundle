<?php

namespace Comsave\SafeSalesforceSaver\Service;

use Comsave\SafeSalesforceSaver\Producer\AsyncSfSaverProducer;
use Comsave\SafeSalesforceSaver\Producer\RpcSfSaverProducer;

/**
 * Class SafeSalesforceSaver
 * @package Comsave\SafeSalesforceSaver\Service
 */
class SafeSalesforceSaver {

    /** @var AsyncSfSaverProducer */
    private $aSyncSaver;

    /** @var RpcSfSaverProducer */
    private $rpcSaver;

    /**
     * @param AsyncSfSaverProducer $aSyncSaver
     * @param RpcSfSaverProducer $rpcSaver
     * @codeCoverageIgnore
     */
    public function __construct(AsyncSfSaverProducer $aSyncSaver, RpcSfSaverProducer $rpcSaver)
    {
        $this->aSyncSaver = $aSyncSaver;
        $this->rpcSaver = $rpcSaver;
    }

    /**
     * Use this function to save your model to Salesforce without waiting for a response.
     * @param $model
     */
    public function ASyncSave($model): void
    {
        $this->aSyncSaver->publish(serialize($model));
    }

    /**
     * Use this function to save your model to Salesforce and wait for the response.
     * @param $model
     * @return string
     * @throws \Exception
     */
    public function Save($model): string
    {
        return $this->rpcSaver->call($model);
    }
}