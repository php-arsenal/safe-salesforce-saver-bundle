<?php

namespace Comsave\SafeSalesforceSaverBundle\Producer;

use Comsave\SafeSalesforceSaverBundle\Exception\TimeoutException;
use Comsave\SafeSalesforceSaverBundle\Exception\UnidentifiedMessageException;
use OldSound\RabbitMqBundle\RabbitMq\RpcClient;
use PhpAmqpLib\Exception\AMQPTimeoutException;

/**
 * Class RpcSfSaverClient
 * @package Comsave\SafeSalesforceSaverBundle\Producer
 */
class RpcSfSaverClient
{
    public const REQUEST_EXPIRATION = 50;

    /** @var RpcClient */
    private $rpcClient;

    /**
     * @param RpcClient $rpcClient
     * @codeCoverageIgnore
     */
    public function __construct(RpcClient $rpcClient)
    {
        $this->rpcClient = $rpcClient;
    }

    /**
     * @param $models
     * @return mixed
     * @throws TimeoutException
     * @throws UnidentifiedMessageException
     */
    public function call($models): string
    {
        $requestId = $this->generateRequestId($models);

        $this->rpcClient->addRequest(
            $models,
            'safe_salesforce_saver_server',
            $requestId,
            null,
            static::REQUEST_EXPIRATION
        );

        try {
            $reply = $this->rpcClient->getReplies();
        } catch (AMQPTimeoutException $e) {
            throw new TimeoutException($models);
        }

        if (!isset($reply[$requestId])) {
            throw new UnidentifiedMessageException($requestId, $models);
        }

        return $reply[$requestId];
    }

    private function generateRequestId($models): string
    {
        return sprintf('sss_%s', crc32($models));
    }
}