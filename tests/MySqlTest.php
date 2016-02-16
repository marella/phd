<?php

use PhD\ConnectionFactory;
use PhD\DatabaseManager;

class MySqlTest extends PHPUnit_Extensions_Database_TestCase
{
    protected $db;

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $config = $this->getConfig();
        $config = $config['connections'][$config['default']];
        $database = $config['database'];
        $pdo = new PDO("mysql:host={$config['host']};dbname={$database}", $config['username'], $config['password']);

        return $this->createDefaultDBConnection($pdo, $database);
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return $this->getYamlDataSet('seeds/test_users');
    }

    public function setUp()
    {
        parent::setUp();
        $this->db = $this->getDb();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->db->disconnect();
        unset($this->db);
    }

    public function testConnection()
    {
        $defaultConnection = $this->db->getDefaultConnection();
        $this->assertInstanceOf('PDO', $this->db->getPdo());
        $this->assertInstanceOf('PDO', $this->db->getReadPdo());
        $this->assertInstanceOf('PDO', $this->db->connection()->getPdo());
        $this->assertInstanceOf('PDO', $this->db->connection()->getReadPdo());
        $this->assertInstanceOf('PDO', $this->db->connection($defaultConnection)->getPdo());
        $this->assertInstanceOf('PDO', $this->db->connection($defaultConnection)->getReadPdo());
        $this->db->disconnect();
        $this->assertNull($this->db->getPdo());
        $this->assertNull($this->db->getReadPdo());
        $this->assertNull($this->db->connection()->getPdo());
        $this->assertNull($this->db->connection()->getReadPdo());
        $this->assertNull($this->db->connection($defaultConnection)->getPdo());
        $this->assertNull($this->db->connection($defaultConnection)->getReadPdo());
    }

    public function testSetup()
    {
        $queryTable = $this->getConnection()->createQueryTable('test_users', 'SELECT * FROM test_users');
        $expectedTable = $this->getDataSet()->getTable('test_users');
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testSelectOne()
    {
        $actual = $this->db->selectOne('SELECT * FROM test_users');
        $expected = $this->getDataSet()->getTable('test_users')->getRow(0);
        $this->assertEquals($expected, $actual);
    }

    public function testSelect()
    {
        $actual = $this->db->select('SELECT * FROM test_users');
        $expected = $this->getDataSet()->getTable('test_users');
        $this->assertEquals($expected->getRowCount(), count($actual));
        foreach ($actual as $i => $actualRow) {
            $expectedRow = $expected->getRow($i);
            $this->assertEquals($expectedRow, $actualRow);
        }
    }

    public function testInsert()
    {
        $conn = $this->getConnection();
        $originalRowCount = $conn->createQueryTable('test_users', 'SELECT * FROM test_users')->getRowCount();
        $result = $this->db->insert('INSERT INTO test_users (name) VALUES (?)', ['foo']);
        $this->assertTrue($result);
        $expectedRowCount = $originalRowCount + 1;
        $actualRowCount = $conn->createQueryTable('test_users', 'SELECT * FROM test_users')->getRowCount();
        $this->assertEquals($expectedRowCount, $actualRowCount);
        $actual = $conn->createQueryTable('test_users', 'SELECT * FROM test_users')->getValue($actualRowCount - 1, 'name');
        $this->assertEquals('foo', $actual);
    }

    public function testDelete()
    {
        $conn = $this->getConnection();
        $originalRowCount = $conn->createQueryTable('test_users', 'SELECT * FROM test_users')->getRowCount();
        $affected = $this->db->delete('DELETE FROM test_users LIMIT 1');
        $this->assertEquals(1, $affected);
        $expectedRowCount = $originalRowCount - 1;
        $actualRowCount = $conn->createQueryTable('test_users', 'SELECT * FROM test_users')->getRowCount();
        $this->assertEquals($expectedRowCount, $actualRowCount);
    }

    public function testUpdate()
    {
        $conn = $this->getConnection();
        $originalRowCount = $conn->createQueryTable('test_users', 'SELECT * FROM test_users')->getRowCount();
        $affected = $this->db->update('UPDATE test_users SET name = :name LIMIT 1', ['name' => 'foo']);
        $this->assertEquals(1, $affected);
        $expectedRowCount = $originalRowCount;
        $actualRowCount = $conn->createQueryTable('test_users', 'SELECT * FROM test_users')->getRowCount();
        $this->assertEquals($expectedRowCount, $actualRowCount);
        $actual = $conn->createQueryTable('test_users', 'SELECT * FROM test_users')->getValue(0, 'name');
        $this->assertEquals('foo', $actual);
    }

    protected function getConfig()
    {
        return require __DIR__.'/config.php';
    }

    protected function getDb()
    {
        $config = $this->getConfig();
        $factory = new ConnectionFactory();
        $db = new DatabaseManager($config, $factory);

        return $db;
    }

    protected function getYamlDataSet($filename)
    {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            __DIR__."/data/$filename.yml"
        );
    }
}
