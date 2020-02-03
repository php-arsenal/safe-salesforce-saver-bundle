<?php

namespace Comsave\SafeSalesforceSaver\Producer;

use Comsave\SafeSalesforceSaver\Exception\TimeoutException;
use Comsave\SafeSalesforceSaver\Exception\UnidentifiedMessageException;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class RpcSalesforceSaverServer
 * @package Comsave\SafeSalesforceSaver\Producer
 */
class RpcSfSaverProducer extends Producer
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
     * @param $model
     * @return mixed
     * @throws TimeoutException
     * @throws UnidentifiedMessageException
     */
    public function call($model)
    {
        $requestId = 'sss_' . crc32(microtime());

        $this->rpcClient->addRequest(serialize($model), 'safe_salesforce_saver_server', $requestId, null, 50);

        try {
            $reply = $this->rpcClient->getReplies();
        } catch (AMQPTimeoutException $e) {
            throw new TimeoutException(serialize($model));
        }

        if (!isset($reply[$requestId])) {
            throw new UnidentifiedMessageException($requestId, serialize($model));
        }

        return $reply[$requestId];
    }
}