<?php

namespace tests\UNit\Comsave\SafeSalesforceSaver\Services;

use Comsave\SafeSalesforceSaver\Services\SafeSalesforceSaver;
use PHPUnit\Framework\TestCase;

/**
 * Class SafeSalesforceSaverTest
 * @package tests\UNit\Comsave\SafeSalesforceSaver\Services
 * @coversDefaultClass \Comsave\SafeSalesforceSaver\Services\SafeSalesforceSaver
 */
class SafeSalesforceSaverTest extends TestCase
{
    /* @var SafeSalesforceSaver */
    private $SafeSalesforceSaver;

    public function setUp(): void
    {
        $this->SafeSalesforceSaver = new SafeSalesforceSaver();
    }

    /**
     * @covers ::ASyncSave()
     */
    public function testASyncSave()
    {

    }

    /**
     * @covers ::Save()
     */
    public function testSave()
    {

    }
}
