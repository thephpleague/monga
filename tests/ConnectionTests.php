<?php

use League\Monga\Connection;

class ConnectionTests extends PHPUnit_Framework_TestCase
{
    protected $connection;

    public function setUp()
    {
        if ( ! $this->connection) {
            $this->connection = new Connection(null, array(
                'connect' => true,
            ));
        }
    }

    public function testInjection()
    {
        $mongo = $this->connection->getConnection();
        $mongoHash = spl_object_hash($mongo);
        $connection = new Connection($mongo);
        $reflection = new ReflectionObject($connection);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);
        $this->assertInstanceOf('MongoClient', $connectionProperty->getValue($connection));
        $this->assertEquals($mongoHash, spl_object_hash($connectionProperty->getValue($connection)));
    }

    public function testDisconnect()
    {
        $this->connection->disconnect();
        $this->assertFalse($this->connection->isConnected());
    }


    public function testReconnectonnect()
    {
        $this->connection->disconnect();
        $this->assertTrue($this->connection->disconnect());
        $this->assertFalse($this->connection->isConnected());
        $this->connection->connect();
        $this->assertTrue($this->connection->connect());
        $this->assertTrue($this->connection->isConnected());
    }


    public function testHasDatabase()
    {
        $this->assertFalse($this->connection->hasDatabase('__unknown__database__'));
        $this->assertTrue($this->connection->hasDatabase('admin'));
    }

    public function testDatabaseDefaultServer()
    {
        $connection = new Connection(null);
        $host = (string) $connection->getConnection();
        $this->assertEquals('localhost:27017', $host);
    }

    public function testDatabaseConfig()
    {
        $connection = new Connection(array('connect' => true));
        $host = (string) $connection->getConnection();
        $this->assertEquals('localhost:27017', $host);
    }

    public function testListDatabases()
    {
        $list = $this->connection->listDatabases();

        $this->assertInternalType('array', $list);
        $this->assertContains('admin', $list);

        $list = $this->connection->listDatabases(true);
        $this->assertInternalType('array', $list);
    }

    public function testGetMongoObject()
    {
        $mongo = $this->connection->getConnection();

        $this->assertInstanceOf('MongoClient', $mongo);
    }


    public function testDropUnknownDatabase()
    {
        $result = $this->connection->dropDatabase('_unknown_');

        $this->assertTrue($result);
    }


    public function testDropKnownDatabase()
    {
        $mongo = $this->connection->getConnection();
        $mongo->demo->users->insert(array('test' => true));

        $result = $this->connection->dropDatabase('demo');

        $this->assertTrue($result);
    }


    public function testGetDatabase()
    {
        $database = $this->connection->database('my_db');
        $this->assertInstanceOf('League\Monga\Database', $database);
    }


    public function testGetMongoDatabase()
    {
        $database = $this->connection->database('my_db', false);
        $this->assertInstanceOf('MongoDB', $database);
    }


    public function testGetConnection()
    {
        $mongo = $this->connection->getConnection();
        $this->assertInstanceOf('MongoClient', $mongo);
    }


    public function testReplaceConnection()
    {
        $original = $this->connection->getConnection();
        $new = new MongoClient();
        $original_hash = spl_object_hash($original);
        $new_hash = spl_object_hash($new);
        $this->connection->setConnection($new);
        $get_hash = spl_object_hash($this->connection->getConnection());
        $this->assertEquals($get_hash, $new_hash);
        $this->assertNotEquals($get_hash, $original_hash);
        $this->connection->setConnection($original);
    }
}
