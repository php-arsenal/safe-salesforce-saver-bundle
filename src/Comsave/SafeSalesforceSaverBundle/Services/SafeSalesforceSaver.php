<?php

namespace Comsave\SafeSalesforceSaverBundle\Services;

use Comsave\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer;
use Comsave\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
use Phpforce\SoapClient\Result\SaveResult;
use Traversable;

/**
 * Class SafeSalesforceSaver
 * @package Comsave\SafeSalesforceSaverBundle\Services
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
     * @param $models mixed You can either pass a single object or an array of objects.
     */
    public function aSyncSave($models): void
    {
        $this->aSyncSaver->publish(serialize($this->turnModelsIntoArray($models)));
    }

    /**
     * Use this function to save your model(s) to Salesforce and wait for the response.
     * @param $models mixed You can either pass a single object or an array of objects.
     * @return string If you passed a single object this function will return a serialized version of your object. If you passed multiple objects this function will return an array of all the inserted IDs
     * @throws \Exception
     */
    public function save($models): string
    {
        $result = unserialize($this->rpcSaver->call(serialize($this->turnModelsIntoArray($models))));

        if (is_countable($models) && count($models) != 1) {
            foreach($models as $key => $model) {
                $model->setId($result[$key]->getId());
            }
        }

        return serialize($models);
    }

    /**
     * @param $models
     * @return array
     */
    private function turnModelsIntoArray($models): array
    {
        if (is_array($models)) {
            $modelsArray = $models;
        } elseif ($models instanceof Traversable) {
            $modelsArray = [];
            foreach ($models as $m) {
                $modelsArray[] = $m;
            }
        } else {
            $modelsArray = [$models];
        }

        return $modelsArray;
    }
}