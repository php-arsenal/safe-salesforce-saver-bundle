<?php

namespace Tests\Unit\Comsave\SafeSalesforceSaverBundle\Consumers;

use Comsave\SafeSalesforceSaverBundle\Consumers\SafeSalesforceSaverServer;
use Comsave\SafeSalesforceSaverBundle\Services\ModelSerializer;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use PhpAmqpLib\Message\AMQPMessage;
use Phpforce\SoapClient\Exception\SaveException;
use Phpforce\SoapClient\Result\SaveResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class SafeSalesforceSaverServerTest
 * @package tests\Unit\Comsave\SafeSalesforceSaverBundle\Consumers
 * @coversDefaultClass \Comsave\SafeSalesforceSaverBundle\Consumers\SafeSalesforceSaverServer
 */
class SalesforceSaverConsumer extends TestCase
{
    /* @var SafeSalesforceSaverServer */
    private $safeSalesforceSaverServer;

    /** @var MappedBulkSaver|MockObject */
    private $mappedBulkSaverMock;

    /** @var ModelSerializer|MockObject */
    private $modelSerializerMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    public function setUp(): void
    {
        $this->mappedBulkSaverMock = $this->createMock(MappedBulkSaver::class);
        $this->modelSerializerMock = $this->createMock(ModelSerializer::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->safeSalesforceSaverServer = new SafeSalesforceSaverServer(
            $this->mappedBulkSaverMock,
            $this->modelSerializerMock,
            $this->loggerMock
        );
    }

    /**
     * @covers ::execute()
     */
    public function testExecute(): void
    {
        $models = [new \stdClass()];
        $serializedModels = 'a:1:{i:0;O:8:"stdClass":0:{}}';
        $message = new AMQPMessage($serializedModels);

        $this->modelSerializerMock->expects($this->once())
            ->method('unserialize')
            ->with($message->body)
            ->willReturn($models);

        $saveResultMock = $this->createMock(SaveResult::class);
        $saveResultMocks = [$saveResultMock];

        $this->mappedBulkSaverMock->expects($this->once())
            ->method('save')
            ->with($models[0]);

        $this->mappedBulkSaverMock->expects($this->once())
            ->method('flush')
            ->willReturn($saveResultMocks);

        $this->assertEquals($saveResultMocks, $this->safeSalesforceSaverServer->execute($message));
    }

    /**
     * @covers ::execute()
     */
    public function testExecuteMultiple(): void
    {
        $models = [
            new \stdClass(),
            new \stdClass(),
        ];
        $serializedModels = 'a:2:{i:0;O:8:"stdClass":0:{}i:1;O:8:"stdClass":0:{}}';
        $message = new AMQPMessage($serializedModels);

        $this->modelSerializerMock->expects($this->once())
            ->method('unserialize')
            ->with($message->body)
            ->willReturn($models);

        $this->mappedBulkSaverMock->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$models[0]],
                [$models[1]]
            );

        $saveResultMock1 = $this->createMock(SaveResult::class);
        $saveResultMock2 = $this->createMock(SaveResult::class);
        $saveResultMocks = [$saveResultMock1, $saveResultMock2];

        $this->mappedBulkSaverMock->expects($this->once())
            ->method('flush')
            ->willReturn($saveResultMocks);

        $this->assertEquals($saveResultMocks, $this->safeSalesforceSaverServer->execute($message));
    }

    /**
     * @covers ::execute()
     */
    public function testExecuteExceptionGetsLogged(): void
    {
        $serializedModels = 'a:1:{i:0;O:8:"stdClass":0:{}}';
        $message = new AMQPMessage($serializedModels);

        $this->modelSerializerMock->expects($this->once())
            ->method('unserialize')
            ->with($message->body)
            ->willThrowException(new SaveException('This is a test exception'))
            ->willReturn([]);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('SafeSalesforceSaver. In `SafeSalesforceSaverServer` occured `Failed to unserialize message. This is a test exception. a:1:{i:0;O:8:"stdClass":0:{}}`.');

        $this->safeSalesforceSaverServer->execute($message);
    }
}
