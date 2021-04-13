<?php

namespace Tests\Unit\Consumers;

use PhpArsenal\SafeSalesforceSaverBundle\Consumers\SalesforceSaverConsumer;
use PhpArsenal\SafeSalesforceSaverBundle\Services\ModelSerializer;
use PhpAmqpLib\Message\AMQPMessage;
use PhpArsenal\SalesforceMapperBundle\MappedBulkSaver;
use PhpArsenal\SoapClient\Result\SaveResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class SafeSalesforceSaverServerTest
 * @package tests\Unit\PhpArsenal\SafeSalesforceSaverBundle\Consumers
 * @coversDefaultClass \PhpArsenal\SafeSalesforceSaverBundle\Consumers\SalesforceSaverConsumer
 */
class SalesforceSaverConsumerTest extends TestCase
{
    /* @var SalesforceSaverConsumer */
    private $salesforceSaverConsumer;

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
        $this->salesforceSaverConsumer = new SalesforceSaverConsumer(
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

        $this->assertEquals($saveResultMocks, $this->salesforceSaverConsumer->execute($message));
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

        $this->assertEquals($saveResultMocks, $this->salesforceSaverConsumer->execute($message));
    }

    /**
     * @covers ::execute()
     */
    public function testExecuteExceptionGetsLogged(): void
    {
        $serializedModels = 'a:1:{i:0;O:8:"stdClass":0:{}}';
        $message = new AMQPMessage($serializedModels);

        $exception = new \Exception('This is a test exception');

        $this->modelSerializerMock->expects($this->once())
            ->method('unserialize')
            ->with($message->body)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('SafeSalesforceSaver. In `SalesforceSaverConsumer` occured `Failed to unserialize message. This is a test exception. a:1:{i:0;O:8:"stdClass":0:{}}`.');

        $this->expectExceptionMessage($exception->getMessage());

        $this->salesforceSaverConsumer->execute($message);
    }

    public function exceptionProvider()
    {
        return [
            ['Mandatory field \'name__c\' is missing.', false],
            ['Save failed. org is locked.', true],
            ['unable to obtain exclusive access to object 12345.', true],
            ['Http Error 1234. Error Fetching http headers.', true],
        ];
    }

    /**
     * @dataProvider exceptionProvider()
     * @covers ::shouldRequeue()
     */
    public function testShouldRequeue($message, $expected)
    {
        $exception = new \Exception($message);
        $this->assertEquals($expected, $this->salesforceSaverConsumer->shouldRequeue($exception));
    }
}
