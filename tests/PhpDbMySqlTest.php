<?php

use Mockery as m;

class PhpDbMySqlTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    public function setUp()
    {
        $config = $this->getConfig();
        $factory = new \PhpDb\ConnectionFactory();
        $this->db = new \PhpDb\DatabaseManager($config, $factory);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testConnectionCanBeCreated()
    {
        $this->assertEquals('mysql', $this->db->getDefaultConnection());
        $this->assertInstanceOf('PDO', $this->db->connection()->getPdo());
        $this->assertInstanceOf('PDO', $this->db->connection()->getReadPdo());
        $this->db->connection()->disconnect();
        $this->assertNull($this->db->connection()->getPdo());
        $this->assertNull($this->db->connection()->getReadPdo());
    }

    protected function getConfig()
    {
        return require __DIR__.'/config.php';
    }
}
