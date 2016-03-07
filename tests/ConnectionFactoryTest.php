<?php

use Mockery as m;
use PhD\ConnectionFactory;

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
