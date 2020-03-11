<?php

namespace Comsave\SafeSalesforceSaverBundle\Services;

use Comsave\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer;
use Comsave\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
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
     * Use this function to save your model(s) to Salesforce and get the IDs set on your object(s).
     * @param $models mixed You can either pass a single object or an array of objects.
     * @throws \Exception
     */
    public function save($models): void
    {
        $result = unserialize($this->rpcSaver->call(serialize($this->turnModelsIntoArray($models))));

        if (is_countable($models) && count($models) != 1) {
            $iterator = 0;
            foreach ($models as $model) {
                if (!$model->getId()) {
                    $model->setId($result['created'][$iterator]->getId());
                    $iterator++;
                }
            }
        } else {
            if (is_iterable($models)) {
                $models = $models[0];
            }
            $models->setId($result->getId());
        }
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
