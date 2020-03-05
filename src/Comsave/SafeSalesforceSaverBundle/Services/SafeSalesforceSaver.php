<?php

namespace Comsave\SafeSalesforceSaver\Services;

use Comsave\SafeSalesforceSaver\Producer\AsyncSfSaverProducer;
use Comsave\SafeSalesforceSaver\Producer\RpcSfSaverClient;
use Traversable;

/**
 * Class SafeSalesforceSaver
 * @package Comsave\SafeSalesforceSaver\Services
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
    public function aSyncSave($models): void
    {
        $this->aSyncSaver->publish(serialize($this->turnModelsIntoArray($models)));
    }

    /**
     * Use this function to save your model to Salesforce and wait for the response.
     * @param $models
     * @return string
     * @throws \Exception
     */
    public function save($models): string
    {
        return $this->rpcSaver->call(serialize($this->turnModelsIntoArray($models)));
    }

    /**
     * @param $models
     * @return array
     */
    private function turnModelsIntoArray($models)
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

        return $modelsArray;
    }
}