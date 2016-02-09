<?php

use Mockery as m;

class PhpDbConnectionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testSelectOneCallsSelectAndReturnsSingleResult()
    {
        $connection = $this->getMockConnection(['select']);
        $connection->expects($this->once())->method('select')->with('foo', ['bar' => 'baz'])->will($this->returnValue(['foo']));
        $this->assertEquals('foo', $connection->selectOne('foo', ['bar' => 'baz']));
    }

    public function testSelectProperlyCallsPDO()
    {
        $pdo = $this->getMock('DatabaseConnectionTestMockPDO', ['prepare']);
        $writePdo = $this->getMock('DatabaseConnectionTestMockPDO', ['prepare']);
        $writePdo->expects($this->never())->method('prepare');
        $statement = $this->getMock('PDOStatement', ['execute', 'fetchAll']);
        $statement->expects($this->once())->method('execute')->with($this->equalTo(['foo' => 'bar']));
        $statement->expects($this->once())->method('fetchAll')->will($this->returnValue(['boom']));
        $pdo->expects($this->once())->method('prepare')->with('foo')->will($this->returnValue($statement));
        $mock = $this->getMockConnection(['prepareBindings'], $writePdo);
        $mock->setReadPdo($pdo);
        $mock->expects($this->once())->method('prepareBindings')->with($this->equalTo(['foo' => 'bar']))->will($this->returnValue(['foo' => 'bar']));
        $results = $mock->select('foo', ['foo' => 'bar']);
        $this->assertEquals(['boom'], $results);
        $log = $mock->getQueryLog();
        $this->assertEquals('foo', $log[0]['query']);
        $this->assertEquals(['foo' => 'bar'], $log[0]['bindings']);
        $this->assertTrue(is_numeric($log[0]['time']));
    }

    public function testInsertCallsTheStatementMethod()
    {
        $connection = $this->getMockConnection(['statement']);
        $connection->expects($this->once())->method('statement')->with($this->equalTo('foo'), $this->equalTo(['bar']))->will($this->returnValue('baz'));
        $results = $connection->insert('foo', ['bar']);
        $this->assertEquals('baz', $results);
    }

    public function testUpdateCallsTheAffectingStatementMethod()
    {
        $connection = $this->getMockConnection(['affectingStatement']);
        $connection->expects($this->once())->method('affectingStatement')->with($this->equalTo('foo'), $this->equalTo(['bar']))->will($this->returnValue('baz'));
        $results = $connection->update('foo', ['bar']);
        $this->assertEquals('baz', $results);
    }

    public function testDeleteCallsTheAffectingStatementMethod()
    {
        $connection = $this->getMockConnection(['affectingStatement']);
        $connection->expects($this->once())->method('affectingStatement')->with($this->equalTo('foo'), $this->equalTo(['bar']))->will($this->returnValue('baz'));
        $results = $connection->delete('foo', ['bar']);
        $this->assertEquals('baz', $results);
    }

    public function testStatementProperlyCallsPDO()
    {
        $pdo = $this->getMock('DatabaseConnectionTestMockPDO', ['prepare']);
        $statement = $this->getMock('PDOStatement', ['execute']);
        $statement->expects($this->once())->method('execute')->with($this->equalTo(['bar']))->will($this->returnValue('foo'));
        $pdo->expects($this->once())->method('prepare')->with($this->equalTo('foo'))->will($this->returnValue($statement));
        $mock = $this->getMockConnection(['prepareBindings'], $pdo);
        $mock->expects($this->once())->method('prepareBindings')->with($this->equalTo(['bar']))->will($this->returnValue(['bar']));
        $results = $mock->statement('foo', ['bar']);
        $this->assertEquals('foo', $results);
        $log = $mock->getQueryLog();
        $this->assertEquals('foo', $log[0]['query']);
        $this->assertEquals(['bar'], $log[0]['bindings']);
        $this->assertTrue(is_numeric($log[0]['time']));
    }

    public function testAffectingStatementProperlyCallsPDO()
    {
        $pdo = $this->getMock('DatabaseConnectionTestMockPDO', ['prepare']);
        $statement = $this->getMock('PDOStatement', ['execute', 'rowCount']);
        $statement->expects($this->once())->method('execute')->with($this->equalTo(['foo' => 'bar']));
        $statement->expects($this->once())->method('rowCount')->will($this->returnValue(['boom']));
        $pdo->expects($this->once())->method('prepare')->with('foo')->will($this->returnValue($statement));
        $mock = $this->getMockConnection(['prepareBindings'], $pdo);
        $mock->expects($this->once())->method('prepareBindings')->with($this->equalTo(['foo' => 'bar']))->will($this->returnValue(['foo' => 'bar']));
        $results = $mock->update('foo', ['foo' => 'bar']);
        $this->assertEquals(['boom'], $results);
        $log = $mock->getQueryLog();
        $this->assertEquals('foo', $log[0]['query']);
        $this->assertEquals(['foo' => 'bar'], $log[0]['bindings']);
        $this->assertTrue(is_numeric($log[0]['time']));
    }

    public function testTransactionMethodRunsSuccessfully()
    {
        $pdo = $this->getMock('DatabaseConnectionTestMockPDO', ['beginTransaction', 'commit']);
        $mock = $this->getMockConnection([], $pdo);
        $pdo->expects($this->once())->method('beginTransaction');
        $pdo->expects($this->once())->method('commit');
        $result = $mock->transaction(function ($db) { return $db; });
        $this->assertEquals($mock, $result);
    }

    public function testTransactionMethodRollsbackAndThrows()
    {
        $pdo = $this->getMock('DatabaseConnectionTestMockPDO', ['beginTransaction', 'commit', 'rollBack']);
        $mock = $this->getMockConnection([], $pdo);
        $pdo->expects($this->once())->method('beginTransaction');
        $pdo->expects($this->once())->method('rollBack');
        $pdo->expects($this->never())->method('commit');
        try {
            $mock->transaction(function () { throw new Exception('foo'); });
        } catch (Exception $e) {
            $this->assertEquals('foo', $e->getMessage());
        }
    }

    /**
     * @expectedException RuntimeException
     */
    public function testTransactionMethodDisallowPDOChanging()
    {
        $pdo = $this->getMock('DatabaseConnectionTestMockPDO', ['beginTransaction', 'commit', 'rollBack']);
        $pdo->expects($this->once())->method('beginTransaction');
        $pdo->expects($this->once())->method('rollBack');
        $pdo->expects($this->never())->method('commit');

        $mock = $this->getMockConnection([], $pdo);

        $mock->setReconnector(function ($connection) {
            $connection->setPDO(null);
        });

        $mock->transaction(function ($connection) { $connection->reconnect(); });
    }

    public function testPrepareBindings()
    {
        $date = m::mock('DateTime');
        $conn = $this->getMockConnection();
        $dateFormat = $conn->getDateFormat();
        $this->assertEquals('Y-m-d H:i:s', $dateFormat);
        $date->shouldReceive('format')->once()->with($dateFormat)->andReturn('bar');
        $bindings = ['test' => $date];
        $result = $conn->prepareBindings($bindings);
        $this->assertEquals(['test' => 'bar'], $result);
    }

    public function testPretendOnlyLogsQueries()
    {
        $connection = $this->getMockConnection();
        $queries = $connection->pretend(function ($connection) {
            $connection->select('foo bar', ['baz']);
        });
        $this->assertEquals('foo bar', $queries[0]['query']);
        $this->assertEquals(['baz'], $queries[0]['bindings']);
    }

    protected function getMockConnection($methods = [], $pdo = null)
    {
        $pdo = $pdo ?: new DatabaseConnectionTestMockPDO();
        $defaults = ['getDefaultQueryGrammar', 'getDefaultPostProcessor', 'getDefaultSchemaGrammar'];
        $connection = $this->getMock('PhpDb\Connections\Connection', array_merge($defaults, $methods), [$pdo]);
        $connection->enableQueryLog();

        return $connection;
    }
}

class DatabaseConnectionTestMockPDO extends PDO
{
    public function __construct()
    {
    }
}
