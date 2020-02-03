<?php

namespace Comsave\SafeSalesforceSaver\Producer;

use Comsave\SafeSalesforceSaver\Exception\TimeoutException;
use Comsave\SafeSalesforceSaver\Exception\UnidentifiedMessageException;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RpcSalesforceSaverServer
 * @package Comsave\SafeSalesforceSaver\Producer
 */
class RpcSfSaverProducer extends Producer
{
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * @param ContainerInterface $container
     * @codeCoverageIgnore
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $model
     * @return mixed
     * @throws TimeoutException
     * @throws UnidentifiedMessageException
     */
    public function call($model)
    {
        $requestId = 'safe_salesforce_saver' . crc32(microtime());

        /** @var RpcClient $rpcClient */
        $rpcClient = $this->container->get('old_sound_rabbit_mq.parallel_rpc');

        $rpcClient->addRequest(serialize($model), 'order_create_server', $requestId, null, 50);

        try {
            $reply = $rpcClient->getReplies();
        } catch (AMQPTimeoutException $e) {
            throw new TimeoutException(serialize($model));
        }

        if (!isset($reply[$requestId])) {
            throw new UnidentifiedMessageException($requestId, serialize($model));
        }

        return $reply[$requestId];
    }
}