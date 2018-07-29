<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/07/29
 * Time: 2:02
 */

namespace INTERMediator\FileMakerServer\RESTAPI;

use PHPUnit\Framework\TestCase;

class FMDataAPIUnitTest extends TestCase
{
    private $fmdataapi;

    public function setUp()
    {
        $this->fmdataapi = new FMDataAPI("TestDB", "web", "password", "localhost", "443", "https", true);
    }

    public function test_anyway()
    {
        $this->assertNotNull($this->fmdataapi, 'FMDataAPI class must be instanticate.');
        $this->assertEquals($this->fmdataapi->errorCode(), 0, 'It must be no error before calling.');
        $this->assertEquals($this->fmdataapi->errorMessage(), "", 'It must be no error before calling.');
        $this->assertEquals($this->fmdataapi->httpStatus(), 0, 'It must be no status before calling.');
    }
}