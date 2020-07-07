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
     * @covers ::unserializeModels()
     * @covers ::setCreatedModelIds()
     */
    public function testSaveSingleModel(): void
    {
        $model = new \stdClass();
        $model->id = 1;
        $models = $model;

        $serializedModels = 'a:1:{i:0;O:8:"stdClass":1:{s:2:"id";N;}}';

        $this->modelSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($models)
            ->willReturn($serializedModels);

        $serializedSavedModels = 'a:1:{i:0;O:8:"stdClass":1:{s:2:"id";i:1;}}';

        $this->rpcClientMock->expects($this->once())
            ->method('call')
            ->with($serializedModels)
            ->willReturn('a:1:{i:0;O:8:"stdClass":1:{s:2:"id";i:1;}}');

        $this->rpcClientMock->expects($this->once())
            ->method('call')
            ->with($serializedModels)
            ->willReturn($serializedSavedModels);

        $savedModel = new \stdClass();
        $savedModel->id = 1;
        $savedModels = [$model];

        $this->modelSerializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedSavedModels)
            ->willReturn($savedModels);

        $this->syncSalesforceSaver->save($models);
        $this->assertEquals($model->id, 1);
    }

    /**
     * @covers ::save()
     * @covers ::unserializeModels()
     * @covers ::setCreatedModelIds()
     */
    public function testSaveMultipleModels(): void
    {
        $model = new \stdClass();
        $model->id = 1;
        $model2 = new \stdClass();
        $model2->id = 2;
        $models = [$model, $model2];

        $serializedModels = 'a:2:{i:0;O:8:"stdClass":1:{s:2:"id";N;}i:1;O:8:"stdClass":1:{s:2:"id";N;}}';

        $this->modelSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($models)
            ->willReturn($serializedModels);

        $serializedSavedModels = 'a:2:{i:0;O:8:"stdClass":1:{s:2:"id";i:1;}i:1;O:8:"stdClass":1:{s:2:"id";i:2;}}';

        $this->rpcClientMock->expects($this->once())
            ->method('call')
            ->with($serializedModels)
            ->willReturn($serializedSavedModels);

        $savedModel = new \stdClass();
        $savedModel->id = 1;
        $savedModel2 = new \stdClass();
        $savedModel2->id = 2;
        $savedModels = [$model, $model2];

        $this->modelSerializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedSavedModels)
            ->willReturn($savedModels);

        $this->syncSalesforceSaver->save($models);
        $this->assertEquals($models[0]->id, 1);
        $this->assertEquals($models[1]->id, 2);
    }

    /**
     * @covers ::save()
     */
    public function testSaveThrowsExceptionWhenFailedToSave(): void
    {
        $model = new \stdClass();
        $model->id = null;
        $model2 = new \stdClass();
        $model2->id = null;
        $models = [$model, $model2];

        $serializedModels = 'a:2:{i:0;O:8:"stdClass":1:{s:2:"id";N;}i:1;O:8:"stdClass":1:{s:2:"id";N;}}';

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