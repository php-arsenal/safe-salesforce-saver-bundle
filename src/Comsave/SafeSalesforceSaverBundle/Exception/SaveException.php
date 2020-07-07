<?php

namespace Comsave\SafeSalesforceSaverBundle\Exception;

class SaveException extends SafeSalesforceSaverException
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $error)
    {
        parent::__construct(sprintf('There was an error while saving to Salesforce:%s', $error));
    }
}