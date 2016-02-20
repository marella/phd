<?php

use Mockery as m;
use PhD\DB;
use PhD\DatabaseManager;

class DBTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        DB::clearFacadeRoot();
    }

    public function tearDown()
    {
        m::close();
    }

    public function testGetSetAndClear()
    {
        $this->assertNull(DB::getFacadeRoot());

        $db = new DatabaseManagerStub();
        DB::setFacadeRoot($db);
        $instance = DB::getFacadeRoot();
        $this->assertNotNull($instance);
        $this->assertEquals($db, $instance);

        DB::clearFacadeRoot();
        $this->assertNull(DB::getFacadeRoot());
    }

    public function testSetThrowsError()
    {
        if (class_exists('TypeError')) {
            $this->setExpectedException('TypeError');
        } else {
            $this->setExpectedException('PHPUnit_Framework_Error');
        }

        DB::setFacadeRoot(null);
    }

    public function testInitCallsGetAndSetOnce()
    {
        $this->assertNull(DB::getFacadeRoot());

        $DB = m::mock('PhD\DB'.'[getFacadeRoot, setFacadeRoot]');
        $DB->shouldReceive('getFacadeRoot')->once()->andReturn(false);
        $DB->shouldReceive('setFacadeRoot')->once()->with(m::type('PhD\DatabaseManager'));
        $DB::init([]);
    }

    public function testInitCallsGetOnly()
    {
        $this->assertNull(DB::getFacadeRoot());

        $DB = m::mock('PhD\DB'.'[getFacadeRoot, setFacadeRoot]');
        $DB->shouldReceive('getFacadeRoot')->once()->andReturn(true);
        $DB->shouldNotReceive('setFacadeRoot');
        $DB::init([]);
    }

    public function testCallStatic()
    {
        $this->assertNull(DB::getFacadeRoot());

        $db = $this->getMock('DatabaseManagerStub', ['foo', 'bar']);
        $db->expects($this->once())->method('foo')->with('a')->will($this->returnValue('b'));
        $db->expects($this->once())->method('bar')->will($this->returnValue('c'));
        DB::setFacadeRoot($db);
        $this->assertEquals('b', DB::foo('a'));
        $this->assertEquals('c', DB::bar());
    }
}

class DatabaseManagerStub extends DatabaseManager
{
    public function __construct()
    {
    }
}
