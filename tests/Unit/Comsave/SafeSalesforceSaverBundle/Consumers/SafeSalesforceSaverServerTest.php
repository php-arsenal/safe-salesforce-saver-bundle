<?php

namespace Tests\Unit\Comsave\SafeSalesforceSaverBundle\Consumers;

use Comsave\SafeSalesforceSaverBundle\Consumers\SafeSalesforceSaverServer;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use LogicItLab\Salesforce\MapperBundle\Mapper;
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
class SafeSalesforceSaverServerTest extends TestCase
{
    /* @var SafeSalesforceSaverServer */
    private $SafeSalesforceSaverServer;

    /* @var Mapper|MockObject */
    private $mapperMock;

    /** @var MappedBulkSaver|MockObject */
    private $mappedBulkSaverMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    public function setUp(): void
    {
        $this->mapperMock = $this->createMock(Mapper::class);
        $this->mappedBulkSaverMock = $this->createMock(MappedBulkSaver::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->SafeSalesforceSaverServer = new SafeSalesforceSaverServer($this->mapperMock, $this->mappedBulkSaverMock, $this->loggerMock);
    }

    /**
     * @covers ::execute()
     */
    public function testExecute()
    {
        $body = new \stdClass();
        $message = new AMQPMessage(serialize([$body]));

        $this->mapperMock->expects($this->once())
            ->method('save')
            ->with($body);

        $returnValue = $this->SafeSalesforceSaverServer->execute($message);

        $this->assertEquals($returnValue, $body);
    }

    /**
     * @covers ::execute()
     */
    public function testExecuteMultiple()
    {
        $object = new \stdClass();
        $object2 = new \stdClass();
        $message = new AMQPMessage(serialize([$object, $object2]));

        $this->mappedBulkSaverMock->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive([$object], [$object2]);

        $saveResultMock1 = $this->createMock(SaveResult::class);
        $saveResultMock2 = $this->createMock(SaveResult::class);

        $this->mappedBulkSaverMock->expects($this->once())
            ->method('flush')
            ->willReturn([$saveResultMock1, $saveResultMock2]);

        $returnValue = $this->SafeSalesforceSaverServer->execute($message);

        $this->assertEquals($returnValue, [$saveResultMock1, $saveResultMock2]);
    }

    /**
     * @covers ::execute()
     */
    public function testExecuteExceptionGetsLogged()
    {
        $body = new \stdClass();
        $serializedMessage = serialize([$body]);
        $message = new AMQPMessage($serializedMessage);
        $exception = new SaveException('This is a test exception.');

        $this->mapperMock->expects($this->once())
            ->method('save')
            ->with($body)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('SafeSalesforceSaver - message: This is a test exception. - body: ' . $serializedMessage);

        $this->SafeSalesforceSaverServer->execute($message);
    }
}
