<?php

namespace Comsave\SafeSalesforceSaverBundle\Exception;

/**
 * Class SaveException
 * @package Comsave\SafeSalesforceSaverBundle\Exception
 */
class SaveException extends SSSException
{
    protected $message;

    /**
     * @param string $error
     */
    public function __construct(string $error)
    {
        $this->message = sprintf('There was an error while saving to Salesforce:%s', $error);
    }
}