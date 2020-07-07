<?php

namespace Tests\Unit\Comsave\SafeSalesforceSaverBundle\Services;

use Comsave\SafeSalesforceSaverBundle\Exception\SaveException;
use Comsave\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer;
use Comsave\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
use Comsave\SafeSalesforceSaverBundle\Services\SafeSalesforceSaver;
use Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver\AsyncSalesforceSaver;
use Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver\SyncSalesforceSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SafeSalesforceSaverTest
 * @package tests\Unit\Comsave\SafeSalesforceSaverBundle\Services
 * @coversDefaultClass \Comsave\SafeSalesforceSaverBundle\Services\SafeSalesforceSaver
 */
class SafeSalesforceSaverTest extends TestCase
{
    /* @var SafeSalesforceSaver */
    private $safeSalesforceSaver;

    /** @var AsyncSalesforceSaver|MockObject */
    private $asyncSalesforceSaverMock;

    /** @var SyncSalesforceSaver|MockObject */
    private $syncSalesforceSaverMock;

    public function setUp(): void
    {
        $this->asyncSalesforceSaverMock = $this->createMock(AsyncSalesforceSaver::class);
        $this->syncSalesforceSaverMock = $this->createMock(SyncSalesforceSaver::class);
        $this->safeSalesforceSaver = new SafeSalesforceSaver(
            $this->asyncSalesforceSaverMock,
            $this->syncSalesforceSaverMock
        );
    }

    /**
     * @covers ::aSyncSave()
     */
    public function testAsyncSaveSingle(): void
    {
        $models = new \stdClass();

        $this->asyncSalesforceSaverMock->expects($this->once())
            ->method('save')
            ->with($models);

        $this->syncSalesforceSaverMock->expects($this->never())
            ->method('save');

        $this->safeSalesforceSaver->aSyncSave($models);
    }

    /**
     * @covers ::aSyncSave()
     */
    public function testAsyncSaveMultiple(): void
    {
        $models = [new \stdClass(), new \stdClass()];

        $this->asyncSalesforceSaverMock->expects($this->once())
            ->method('save')
            ->with($models);

        $this->syncSalesforceSaverMock->expects($this->never())
            ->method('save');

        $this->safeSalesforceSaver->aSyncSave($models);
    }

    /**
     * @covers ::save()
     */
    public function testSyncSaveSingle(): void
    {
        $models = new \stdClass();

        $this->asyncSalesforceSaverMock->expects($this->never())
            ->method('save');

        $this->syncSalesforceSaverMock->expects($this->once())
            ->method('save')
            ->with($models);

        $this->safeSalesforceSaver->save($models);
    }

    /**
     * @covers ::save()
     */
    public function testSyncSaveMultiple(): void
    {
        $models = [new \stdClass(), new \stdClass()];

        $this->asyncSalesforceSaverMock->expects($this->never())
            ->method('save');

        $this->syncSalesforceSaverMock->expects($this->once())
            ->method('save')
            ->with($models);

        $this->safeSalesforceSaver->save($models);
    }
}
