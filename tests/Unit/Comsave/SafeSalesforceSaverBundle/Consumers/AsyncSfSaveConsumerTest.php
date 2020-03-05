<?php

namespace Tests\Unit\Comsave\SafeSalesforceSaverBundle\Consumers;

use Comsave\SafeSalesforceSaverBundle\Consumers\AsyncSfSaveConsumer;
use LogicItLab\Salesforce\MapperBundle\MappedBulkSaver;
use LogicItLab\Salesforce\MapperBundle\Mapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AsyncSfSaveConsumerTest
 * @package tests\Unit\Comsave\SafeSalesforceSaver\Consumers
 * @coversDefaultClass \Comsave\SafeSalesforceSaver\Consumers\AsyncSfSaveConsumer
 */
class AsyncSfSaveConsumerTest extends TestCase
{
    /* @var AsyncSfSaveConsumer */
    private $asyncSfSaveConsumer;

    /* @var Mapper|MockObject */
    private $mapper;

    /** @var MappedBulkSaver|MockObject */
    private $mappedBulkSaver;

    public function setUp(): void
    {
        $this->mapper = $this->createMock(Mapper::class);
        $this->mappedBulkSaver = $this->createMock(MappedBulkSaver::class);
        $this->asyncSfSaveConsumer = new AsyncSfSaveConsumer($this->mapper, $this->mappedBulkSaver);
    }

    /**
     * @covers ::execute()
     */
    public function testExecute()
    {
        $message = [];
        $this->asyncSfSaveConsumer->execute(serialize($message));
    }
}
