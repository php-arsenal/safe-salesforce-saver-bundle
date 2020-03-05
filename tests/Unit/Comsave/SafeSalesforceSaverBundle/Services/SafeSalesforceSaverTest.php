<?php

namespace Tests\Unit\Comsave\SafeSalesforceSaverBundle\Services;

use Comsave\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer;
use Comsave\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
use Comsave\SafeSalesforceSaverBundle\Services\SafeSalesforceSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SafeSalesforceSaverTest
 * @package tests\Unit\Comsave\SafeSalesforceSaver\Services
 * @coversDefaultClass \Comsave\SafeSalesforceSaver\Services\SafeSalesforceSaver
 */
class SafeSalesforceSaverTest extends TestCase
{
    /* @var SafeSalesforceSaver */
    private $safeSalesforceSaver;

    /** @var AsyncSfSaverProducer|MockObject */
    private $aSyncSaverMock;

    /** @var RpcSfSaverClient|MockObject */
    private $rpcSaverMock;

    public function setUp(): void
    {
        $this->aSyncSaverMock = $this->createMock(AsyncSfSaverProducer::class);
        $this->rpcSaverMock = $this->createMock(RpcSfSaverClient::class);
        $this->safeSalesforceSaver = new SafeSalesforceSaver($this->aSyncSaverMock, $this->rpcSaverMock);
    }

    /**
     * @covers ::aSyncSave()
     * @covers ::turnModelsIntoArray()
     */
    public function testASyncSaveOneObject()
    {
        $object = new \stdClass();

        $this->aSyncSaverMock->expects($this->once())
            ->method('publish')
            ->with(serialize([$object]));

        $this->safeSalesforceSaver->aSyncSave($object);
    }

    /**
     * @covers ::aSyncSave()
     * @covers ::turnModelsIntoArray()
     */
    public function testASyncSaveMultipleObjects()
    {
        $object = new \stdClass();
        $object2 = new \stdClass();

        $this->aSyncSaverMock->expects($this->once())
            ->method('publish')
            ->with(serialize([$object, $object2]));

        $this->safeSalesforceSaver->aSyncSave([$object, $object2]);
    }

    /**
     * @covers ::save()
     * @covers ::turnModelsIntoArray()
     */
    public function testSaveOneObject()
    {
        $object = new \stdClass();

        $this->rpcSaverMock->expects($this->once())
            ->method('call')
            ->with(serialize([$object]));

        $this->safeSalesforceSaver->save($object);
    }

    /**
     * @covers ::save()
     * @covers ::turnModelsIntoArray()
     */
    public function testSaveMultipleObjects()
    {
        $object = new \stdClass();
        $object2 = new \stdClass();

        $this->rpcSaverMock->expects($this->once())
            ->method('call')
            ->with(serialize([$object, $object2]));

        $this->safeSalesforceSaver->save([$object, $object2]);
    }
}
