<?php

namespace PhpArsenal\SafeSalesforceSaverBundle\Producer;

use PhpArsenal\SafeSalesforceSaverBundle\Exception\TimeoutException;
use PhpArsenal\SafeSalesforceSaverBundle\Exception\UnidentifiedMessageException;
use OldSound\RabbitMqBundle\RabbitMq\RpcClient;
use PhpAmqpLib\Exception\AMQPTimeoutException;

class RpcSfSaverClient
{
    public const REQUEST_EXPIRATION = 50;

    /** @var RpcClient */
    private $rpcClient;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(RpcClient $rpcClient)
    {
        $this->rpcClient = $rpcClient;
    }

    public function call(string $serializedModels): string
    {
        $requestId = $this->addRequest($serializedModels);

        try {
            $reply = $this->rpcClient->getReplies();
        } catch (AMQPTimeoutException $e) {
            throw new TimeoutException($serializedModels);
        }

        if (!isset($reply[$requestId])) {
            throw new UnidentifiedMessageException($requestId, $serializedModels);
        }

        return $reply[$requestId];
    }

    private function addRequest(string $serializedModels): string
    {
        $requestId = $this->generateRequestId($serializedModels);

        $this->rpcClient->addRequest(
            $serializedModels,
            'safe_salesforce_saver_server',
            $requestId,
            null,
            static::REQUEST_EXPIRATION
        );

        return $requestId;
    }

    private function generateRequestId(string $serializedModels): string
    {
        return sprintf('sss_%s', crc32($serializedModels));
    }
}