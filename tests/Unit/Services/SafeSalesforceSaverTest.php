<?php

namespace Tests\Unit\Services;

use PhpArsenal\SafeSalesforceSaverBundle\Exception\SaveException;
use PhpArsenal\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer;
use PhpArsenal\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
use PhpArsenal\SafeSalesforceSaverBundle\Services\SafeSalesforceSaver;
use PhpArsenal\SafeSalesforceSaverBundle\Services\SalesforceSaver\AsyncSalesforceSaver;
use PhpArsenal\SafeSalesforceSaverBundle\Services\SalesforceSaver\SyncSalesforceSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SafeSalesforceSaverTest
 * @package tests\Unit\PhpArsenal\SafeSalesforceSaverBundle\Services
 * @coversDefaultClass \PhpArsenal\SafeSalesforceSaverBundle\Services\SafeSalesforceSaver
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
