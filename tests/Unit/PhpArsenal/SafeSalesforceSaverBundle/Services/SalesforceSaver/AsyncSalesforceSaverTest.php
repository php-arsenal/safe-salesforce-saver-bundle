<?php

namespace Tests\Unit\PhpArsenal\SafeSalesforceSaverBundle\Services\SalesforceSaver;

use PhpArsenal\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer;
use PhpArsenal\SafeSalesforceSaverBundle\Services\ModelSerializer;
use PhpArsenal\SafeSalesforceSaverBundle\Services\SalesforceSaver\AsyncSalesforceSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class AsyncSalesforceSaverTest
 * @package Tests\Unit\PhpArsenal\SafeSalesforceSaverBundle\Services\SalesforceSaver
 * @coversDefaultClass \PhpArsenal\SafeSalesforceSaverBundle\Services\SalesforceSaver\AsyncSalesforceSaver
 */
class AsyncSalesforceSaverTest extends TestCase
{
    /** @var AsyncSalesforceSaver */
    private $asyncSalesforceSaver;

    /** @var AsyncSfSaverProducer|MockObject */
    private $aSyncSaverProducerMock;

    /** @var ModelSerializer|MockObject */
    private $modelSerializerMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    public function setUp(): void
    {
        $this->aSyncSaverProducerMock = $this->createMock(AsyncSfSaverProducer::class);
        $this->modelSerializerMock = $this->createMock(ModelSerializer::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->asyncSalesforceSaver = new AsyncSalesforceSaver(
            $this->aSyncSaverProducerMock,
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

        $this->aSyncSaverProducerMock->expects($this->once())
            ->method('publish')
            ->with($serializedModels);

        $this->asyncSalesforceSaver->save($models);
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

        $this->aSyncSaverProducerMock->expects($this->once())
            ->method('publish')
            ->with($serializedModels);

        $this->asyncSalesforceSaver->save($models);
    }
}