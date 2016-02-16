<?php

use PhD\ConnectionFactory;
use Mockery as m;

class ConnectionFactoryTest extends PHPUnit_Framework_TestCase
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
        $factory = new ConnectionFactory();
        $factory->createConnector(['foo']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIsThrownOnUnsupportedDriver()
    {
        $factory = new ConnectionFactory();
        $factory->createConnector(['driver' => 'foo']);
    }
}
