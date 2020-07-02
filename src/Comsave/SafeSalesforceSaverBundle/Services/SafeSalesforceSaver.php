<?php

namespace Comsave\SafeSalesforceSaverBundle\Services;

use Comsave\SafeSalesforceSaverBundle\Exception\SaveException;
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
     * The IDs will only be set if your model has a public `setId` function.
     * @param $models mixed You can either pass a single object or an array of objects.
     * @throws \Exception
     */
    public function save($models): void
    {
        $rawResult = $this->rpcSaver->call(serialize($this->turnModelsIntoArray($models)));
        $result = false;

        try {
            $result = unserialize($rawResult);
        }
        catch(\Throwable $ex) {
            if ($result === false) {
                throw new SaveException('SafeSalesforceSaver - failed to unserialize: ' . $ex->getMessage());
            }
        }

        if ($result !== false && gettype($result) != 'string') {

            if (is_countable($models) && count($models) != 1) {
                $iterator = 0;
                foreach ($models as $model) {
                    if (method_exists($model, 'getId') && !$model->getId() && method_exists($model, 'setId')) {
                        $model->setId($result['created'][$iterator]->getId());
                        $iterator++;
                    }
                }
            } else {
                if (is_iterable($models)) {
                    $models = $models[0];
                }
                if (method_exists($models, 'setId')) {
                    $models->setId($result->getId());
                }
            }
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
