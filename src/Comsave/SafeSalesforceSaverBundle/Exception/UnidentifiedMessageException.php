<?php

namespace Comsave\SafeSalesforceSaverBundle\Exception;

/**
 * Class UnidentifiedMessageException
 * @package Comsave\SafeSalesforceSaver\Exception
 */
class UnidentifiedMessageException extends SSSException
{
    protected $message;

    /**
     * @param string $requestId
     * @param string $model
     * @codeCoverageIgnore
     */
    public function __construct(string $requestId, string $model)
    {
        $this->message .= sprintf('No valid response was received from the rpc server. The requestId was \'%s\'. Serialized message of the failed call: %s', $requestId, $model);
    }
}