<?php

namespace tests\Unit\Comsave\SafeSalesforceSaver\Consumers;

use Comsave\SafeSalesforceSaver\Consumers\AsyncSfSaveConsumer;
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

    public function setUp(): void
    {
        $this->mapper = $this->createMock(Mapper::class);
        $this->asyncSfSaveConsumer = new AsyncSfSaveConsumer($this->mapper);
    }

    /**
     * @covers ::execute()
     */
    public function testExecute()
    {
        $this->asyncSfSaveConsumer->execute();
    }
}
