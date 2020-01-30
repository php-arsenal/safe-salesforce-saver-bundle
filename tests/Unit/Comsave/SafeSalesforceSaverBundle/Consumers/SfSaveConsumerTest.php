<?php

namespace tests\Unit\Comsave\SafeSalesforceSaver\Consumers;

use Comsave\SafeSalesforceSaver\Consumers\SfSaveConsumer;
use LogicItLab\Salesforce\MapperBundle\Mapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SfSaveConsumerTest
 * @package tests\Unit\Comsave\SafeSalesforceSaver\Consumers
 * @coversDefaultClass \Comsave\SafeSalesforceSaver\Consumers\SfSaveConsumer
 */
class SfSaveConsumerTest extends TestCase
{
    /* @var SfSaveConsumer */
    private $sfSaveConsumer;

    /* @var Mapper|MockObject */
    private $mapper;

    public function setUp(): void
    {
        $this->mapper = $this->createMock(Mapper::class);
        $this->sfSaveConsumer = new SfSaveConsumer($this->mapper);
    }

    /**
     * @covers ::execute()
     */
    public function testExecute()
    {
        $this->sfSaveConsumer->execute();
    }
}
