<?php

use Mockery as m;

class PhpDbConnectionFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIfDriverIsntSetExceptionIsThrown()
    {
        $factory = new PhpDb\ConnectionFactory();
        $factory->createConnector(['foo']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIsThrownOnUnsupportedDriver()
    {
        $factory = new PhpDb\ConnectionFactory();
        $factory->createConnector(['driver' => 'foo']);
    }
}
