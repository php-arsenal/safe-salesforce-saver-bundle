<?php

namespace Tests\Unit\Services;

use PhpArsenal\SafeSalesforceSaverBundle\Services\ModelSerializer;
use PHPUnit\Framework\TestCase;

/**
 * Class ModelSerializerTest
 * @package Tests\Unit\Services
 * @coversDefaultClass \PhpArsenal\SafeSalesforceSaverBundle\Services\ModelSerializer
 */
class ModelSerializerTest extends TestCase
{
    /** @var ModelSerializer */
    private $modelSerializer;

    public function setUp(): void
    {
        $this->modelSerializer = new ModelSerializer();
    }

    /**
     * @covers ::serialize
     * @covers ::toArray
     */
    public function testSerializeSingleModel(): void
    {
        $this->assertEquals('a:1:{i:0;O:8:"stdClass":0:{}}', $this->modelSerializer->serialize(new \stdClass()));
    }

    /**
     * @covers ::serialize
     */
    public function testSerializeMultipleModels(): void
    {
        $this->assertEquals('a:2:{i:0;O:8:"stdClass":0:{}i:1;O:8:"stdClass":0:{}}', $this->modelSerializer->serialize([
            new \stdClass(),
            new \stdClass(),
        ]));
    }

    /**
     * @covers ::unserialize
     * @covers ::toArray
     */
    public function testUnserializeSingleModel(): void
    {
        $this->assertEquals([
            new \stdClass(),
        ], $this->modelSerializer->unserialize('a:1:{i:0;O:8:"stdClass":0:{}}'));
    }

    /**
     * @covers ::unserialize
     * @covers ::toArray
     */
    public function testUnserializeMultipleModels(): void
    {
        $this->assertEquals([
            new \stdClass(),
            new \stdClass(),
        ], $this->modelSerializer->unserialize('a:2:{i:0;O:8:"stdClass":0:{}i:1;O:8:"stdClass":0:{}}'));
    }
}