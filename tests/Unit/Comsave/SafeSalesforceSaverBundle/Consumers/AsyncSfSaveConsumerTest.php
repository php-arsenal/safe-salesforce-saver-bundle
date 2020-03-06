<?php

namespace Tests\Unit\Comsave\SafeSalesforceSaverBundle\Consumers;

use Comsave\SafeSalesforceSaverBundle\Consumers\AsyncSfSaveConsumer;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use LogicItLab\Salesforce\MapperBundle\Mapper;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AsyncSfSaveConsumerTest
 * @package tests\Unit\Comsave\SafeSalesforceSaverBundle\Consumers
 * @coversDefaultClass \Comsave\SafeSalesforceSaverBundle\Consumers\AsyncSfSaveConsumer
 */
class AsyncSfSaveConsumerTest extends TestCase
{
    /* @var AsyncSfSaveConsumer */
    private $asyncSfSaveConsumer;

    /* @var Mapper|MockObject */
    private $mapperMock;

    /** @var MappedBulkSaver|MockObject */
    private $mappedBulkSaverMock;

    public function setUp(): void
    {
        $this->mapperMock = $this->createMock(Mapper::class);
        $this->mappedBulkSaverMock = $this->createMock(MappedBulkSaver::class);
        $this->asyncSfSaveConsumer = new AsyncSfSaveConsumer($this->mapperMock, $this->mappedBulkSaverMock);
    }

    /**
     * @covers ::execute()
     */
    public function testExecute()
    {
        $body = new \stdClass();
        $message = new AMQPMessage(serialize($body));

        $this->mapperMock->expects($this->once())
            ->method('save')
            ->with($body);

        $this->asyncSfSaveConsumer->execute($message);
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
            ->withConsecutive($object, $object2);

        $this->mappedBulkSaverMock->expects($this->once())
            ->method('flush');

        $this->asyncSfSaveConsumer->execute($message);
    }
}
