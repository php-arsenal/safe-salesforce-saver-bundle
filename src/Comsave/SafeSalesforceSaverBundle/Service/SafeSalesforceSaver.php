<?php

namespace Comsave\SafeSalesforceSaver\Service;

use Comsave\SafeSalesforceSaver\Producer\AsyncSfSaverProducer;
use Comsave\SafeSalesforceSaver\Producer\RpcSfSaverClient;
use Traversable;

/**
 * Class SafeSalesforceSaver
 * @package Comsave\SafeSalesforceSaver\Service
 */
class SafeSalesforceSaver
{
    /** @var AsyncSfSaverProducer */
    private $aSyncSaver;

    /** @var RpcSfSaverClient */
    private $rpcSaver;

    /**
     * @param AsyncSfSaverProducer $aSyncSaver
     * @param RpcSfSaverClient $rpcSaver
     * @codeCoverageIgnore
     */
    public function __construct(AsyncSfSaverProducer $aSyncSaver, RpcSfSaverClient $rpcSaver)
    {
        $this->aSyncSaver = $aSyncSaver;
        $this->rpcSaver = $rpcSaver;
    }

    /**
     * Use this function to save your model(s) to Salesforce without waiting for a response.
     * @param $models
     */
    public function ASyncSave($models): void
    {
        if (is_array($models)) {
            $modelsArray = $models;
        } elseif ($models instanceof Traversable) {
            $modelsArray = [];
            foreach ($models as $m) {
                $modelsArray[] = $m;
            }
        } else {
            $modelsArray = array($models);
        }

        $this->aSyncSaver->publish(serialize($modelsArray));
    }

    /**
     * Use this function to save your model to Salesforce and wait for the response.
     * @param $models
     * @return string
     * @throws \Exception
     */
    public function Save($models): string
    {
        if (is_array($models)) {
            $modelsArray = $models;
        } elseif ($models instanceof Traversable) {
            $modelsArray = [];
            foreach ($models as $m) {
                $modelsArray[] = $m;
            }
        } else {
            $modelsArray = array($models);
        }

        return $this->rpcSaver->call($modelsArray);
    }
}