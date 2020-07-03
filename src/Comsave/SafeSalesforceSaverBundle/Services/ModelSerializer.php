<?php

namespace Comsave\SafeSalesforceSaverBundle\Services;

class ModelSerializer
{
    /**
     * @param object|object[] $models
     * @return string
     */
    public function serialize($models): string
    {
        return serialize($this->toArray($models));
    }

    /**
     * @param string
     * @return object|object[]
     */
    public function unserialize(string $serializedModels)
    {
        return $this->toArray(unserialize($serializedModels));
    }

    /**
     * @param object|object[] $models
     * @return array
     */
    private function toArray($models): array
    {
        if (is_array($models)) {
            return $models;
        }

        if ($models instanceof \Traversable) {
            return array_map(function($model) {
                return $model;
            }, $models);
        }

        return [$models];
    }
}