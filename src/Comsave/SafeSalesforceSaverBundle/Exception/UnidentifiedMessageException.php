<?php

namespace Comsave\SafeSalesforceSaverBundle\Exception;

class UnidentifiedMessageException extends SafeSalesforceSaverException
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $requestId, string $model)
    {
        parent::__construct(sprintf('No valid response was received from the rpc server. The requestId was \'%s\'. Serialized message of the failed call: %s', $requestId, $model));
    }
}