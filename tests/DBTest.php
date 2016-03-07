<?php

use Mockery as m;
use PhD\DatabaseManager;
use PhD\DB;

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

        $DB = m::mock(DB::class.'[getFacadeRoot, setFacadeRoot]');
        $DB->shouldReceive('getFacadeRoot')->once()->andReturn(false);
        $DB->shouldReceive('setFacadeRoot')->once()->with(m::type(DatabaseManager::class));
        $DB::init([]);
    }

    public function testInitCallsGetOnly()
    {
        $this->assertNull(DB::getFacadeRoot());

        $DB = m::mock(DB::class.'[getFacadeRoot, setFacadeRoot]');
        $DB->shouldReceive('getFacadeRoot')->once()->andReturn(true);
        $DB->shouldNotReceive('setFacadeRoot');
        $DB::init([]);
    }

    public function testCallStatic()
    {
        $this->assertNull(DB::getFacadeRoot());

        $db = $this->getMock('DatabaseManagerStub', ['a', 'b', 'c', 'd', 'e', 'f', 'g']);
        $db->expects($this->once())->method('a')->will($this->returnValue('ar'));
        $db->expects($this->once())->method('b')->with('b1')->will($this->returnValue('br'));
        $db->expects($this->once())->method('c')->with('c1', 'c2')->will($this->returnValue('cr'));
        $db->expects($this->once())->method('d')->with('d1', 'd2', 'd3')->will($this->returnValue('dr'));
        $db->expects($this->once())->method('e')->with('e1', 'e2', 'e3', 'e4')->will($this->returnValue('er'));
        $db->expects($this->once())->method('f')->with('f1', 'f2', 'f3', 'f4', 'f5')->will($this->returnValue('fr'));
        $db->expects($this->once())->method('g')->with('g1', 'g2', 'g3', 'g4', 'g5', 'g6')->will($this->returnValue('gr'));

        DB::setFacadeRoot($db);

        $this->assertEquals('ar', DB::a());
        $this->assertEquals('br', DB::b('b1'));
        $this->assertEquals('cr', DB::c('c1', 'c2'));
        $this->assertEquals('dr', DB::d('d1', 'd2', 'd3'));
        $this->assertEquals('er', DB::e('e1', 'e2', 'e3', 'e4'));
        $this->assertEquals('fr', DB::f('f1', 'f2', 'f3', 'f4', 'f5'));
        $this->assertEquals('gr', DB::g('g1', 'g2', 'g3', 'g4', 'g5', 'g6'));
    }

    public function testCallStaticThrowsException()
    {
        $this->assertNull(DB::getFacadeRoot());
        $this->setExpectedException('RuntimeException');
        DB::foo();
    }
}

class DatabaseManagerStub extends DatabaseManager
{
    public function __construct()
    {
    }
}
