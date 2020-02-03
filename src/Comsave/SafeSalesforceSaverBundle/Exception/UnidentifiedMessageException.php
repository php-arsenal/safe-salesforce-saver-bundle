<?php

namespace Comsave\SafeSalesforceSaver\Exception;

/**
 * Class UnidentifiedMessageException
 * @package Comsave\SafeSalesforceSaver\Exception
 */
class UnidentifiedMessageException extends SSSException
{
    protected $message = 'No valid response was received from the server.';

    /**
     * @param string $requestId
     * @param string $model
     * @codeCoverageIgnore
     */
    public function __construct(string $requestId, string $model)
    {
        $this->message .= sprintf(' The requestId was \'%s\'. The serialized model that was being saved was: %s', $requestId, $model);
    }
}