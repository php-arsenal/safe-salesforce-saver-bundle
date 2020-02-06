<?php

namespace Comsave\SafeSalesforceSaver\Producer;

use Comsave\SafeSalesforceSaver\Exception\TimeoutException;
use Comsave\SafeSalesforceSaver\Exception\UnidentifiedMessageException;
use OldSound\RabbitMqBundle\RabbitMq\RpcClient;
use PhpAmqpLib\Exception\AMQPTimeoutException;

/**
 * Class RpcSalesforceSaverServer
 * @package Comsave\SafeSalesforceSaver\Producer
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
    public function call($models)
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