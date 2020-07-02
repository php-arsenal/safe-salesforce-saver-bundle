<?php

namespace Tests\Unit\Comsave\SafeSalesforceSaverBundle\Producer;

use Comsave\SafeSalesforceSaverBundle\Exception\TimeoutException;
use Comsave\SafeSalesforceSaverBundle\Exception\UnidentifiedMessageException;
use Comsave\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
use OldSound\RabbitMqBundle\RabbitMq\RpcClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RpcSfSaverClientTest
 * @package tests\Unit\Comsave\SafeSalesforceSaverBundle\Producer
 * @coversDefaultClass \Comsave\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient
 */
class RpcSfSaverClientTest extends TestCase
{
    /* @var RpcSfSaverClient */
    private $rpcSfSaverClient;

    /** @var RpcClient|MockObject */
    private $rpcClientMock;

    public function setUp(): void
    {
        $this->rpcClientMock = $this->createMock(RpcClient::class);
        $this->rpcSfSaverClient = new RpcSfSaverClient($this->rpcClientMock);
    }

    /**
     * @covers ::call()
     */
    public function testCall()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $models = serialize([$object1, $object2]);

        $replyMock = ['sss_3762955769' => 123];

        $this->rpcClientMock->expects($this->once())
            ->method('addRequest');
        $this->rpcClientMock->expects($this->once())
            ->method('getReplies')
            ->willReturn($replyMock);

        $this->rpcSfSaverClient->call($models);
    }

    /**
     * @covers ::call()
     */
    public function testCallThrowsExceptionWhenRequestIdIsNotFound()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $models = serialize([$object1, $object2]);

        $replyMock = ['invalidRequestId' => 123];

        $this->rpcClientMock->expects($this->once())
            ->method('addRequest');
        $this->rpcClientMock->expects($this->once())
            ->method('getReplies')
            ->willReturn($replyMock);

        $this->expectException(UnidentifiedMessageException::class);

        $this->rpcSfSaverClient->call($models);
    }

    /**
     * @covers ::call()
     */
    public function testCallThrowsExceptionWhenRequestTimesOut()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $models = serialize([$object1, $object2]);

        $replyMock = ['invalidRequestId' => 123];

        $this->rpcClientMock->expects($this->once())
            ->method('addRequest');
        $this->rpcClientMock->expects($this->once())
            ->method('getReplies')
            ->willThrowException(new TimeoutException($models));

        $this->expectException(TimeoutException::class);

        $this->rpcSfSaverClient->call($models);
    }
}
