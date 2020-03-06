<?php

namespace Comsave\SafeSalesforceSaverBundle\Exception;

/**
 * Class TimeoutException
 * @package Comsave\SafeSalesforceSaverBundle\Exception
 */
class TimeoutException extends SSSException
{
    protected $message;

    /**
     * @param string $model
     */
    public function __construct(string $model)
    {
        $this->message = sprintf('The time limit expired while trying to save to Salesforce. Serialized message of the failed call: %s', $model);
    }
}