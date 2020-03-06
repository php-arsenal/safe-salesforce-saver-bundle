<?php

namespace Comsave\SafeSalesforceSaverBundle\Producer;

use Comsave\SafeSalesforceSaverBundle\Exception\TimeoutException;
use Comsave\SafeSalesforceSaverBundle\Exception\UnidentifiedMessageException;
use OldSound\RabbitMqBundle\RabbitMq\RpcClient;
use PhpAmqpLib\Exception\AMQPTimeoutException;

/**
 * Class RpcSalesforceSaverServer
 * @package Comsave\SafeSalesforceSaverBundle\Producer
 */
class RpcSfSaverClient
{
    /**
     * @var RpcClient
     */
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
        $requestId = 'sss_' . crc32(microtime());
        $this->rpcClient->addRequest(serialize($models), 'safe_salesforce_saver_server', $requestId, null, 50);

        try {
            $reply = $this->rpcClient->getReplies();
        } catch (AMQPTimeoutException $e) {
            throw new TimeoutException(serialize($models));
        }

        if (!isset($reply[$requestId])) {
            throw new UnidentifiedMessageException($requestId, serialize($models));
        }

        return $reply[$requestId];
    }
}