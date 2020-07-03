<?php

namespace Tests\Unit\Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver;

use Comsave\SafeSalesforceSaverBundle\Exception\SaveException;
use Comsave\SafeSalesforceSaverBundle\Producer\RpcSfSaverClient;
use Comsave\SafeSalesforceSaverBundle\Services\ModelSerializer;
use Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver\SyncSalesforceSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class SyncSalesforceSaverTest
 * @package Tests\Unit\Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver
 * @coversDefaultClass \Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver\SyncSalesforceSaver
 */
class SyncSalesforceSaverTest extends TestCase
{
    /** @var SyncSalesforceSaver */
    private $syncSalesforceSaver;

    /** @var RpcSfSaverClient|MockObject */
    private $rpcClientMock;

    /** @var ModelSerializer|MockObject */
    private $modelSerializerMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    public function setUp(): void
    {
        $this->rpcClientMock = $this->createMock(RpcSfSaverClient::class);
        $this->modelSerializerMock = $this->createMock(ModelSerializer::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->syncSalesforceSaver = new SyncSalesforceSaver(
            $this->rpcClientMock,
            $this->modelSerializerMock,
            $this->loggerMock
        );
    }

    /**
     * @covers ::save()
     */
    public function testSaveSingleModel(): void
    {
        $models = new \stdClass();
        $serializedModels = 'a:1:{i:0;O:8:"stdClass":0:{}}';

        $this->modelSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($models)
            ->willReturn($serializedModels);

        $this->rpcClientMock->expects($this->once())
            ->method('call')
            ->with($serializedModels)
            ->willReturn(serialize('testString'));

        $this->syncSalesforceSaver->save($models);
    }

    /**
     * @covers ::save()
     */
    public function testSaveMultipleModels(): void
    {
        $models = [
            new \stdClass(),
            new \stdClass()
        ];
        $serializedModels = 'a:2:{i:0;O:8:"stdClass":0:{}i:1;O:8:"stdClass":0:{}}';

        $this->modelSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($models)
            ->willReturn($serializedModels);

        $this->rpcClientMock->expects($this->once())
            ->method('call')
            ->with($serializedModels)
            ->willReturn(serialize('testString2'));

        $this->syncSalesforceSaver->save($models);
    }

    /**
     * @covers ::save()
     */
    public function testSaveThrowsExceptionWhenFailedToSave(): void
    {
        $models = [
            new \stdClass(),
            new \stdClass(),
        ];
        $serializedModels = 'a:2:{i:0;O:8:"stdClass":0:{}i:1;O:8:"stdClass":0:{}}';

        $this->modelSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($models)
            ->willReturn($serializedModels);

        $this->rpcClientMock->expects($this->once())
            ->method('call')
            ->with($serializedModels)
            ->willThrowException(new SaveException('Failed to unserialize.'));

        $this->expectException(SaveException::class);

        $this->syncSalesforceSaver->save($models);
    }
}