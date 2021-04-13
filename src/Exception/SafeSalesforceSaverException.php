<?php

namespace PhpArsenal\SafeSalesforceSaverBundle\Exception;

class SafeSalesforceSaverException extends \Exception
{
    protected $model;

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }
}