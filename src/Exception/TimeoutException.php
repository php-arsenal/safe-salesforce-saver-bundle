<?php

namespace PhpArsenal\SafeSalesforceSaverBundle\Exception;

class TimeoutException extends SafeSalesforceSaverException
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $model)
    {
        parent::__construct(sprintf('The time limit expired while trying to save to Salesforce. Serialized message of the failed call: %s', $model));
    }
}