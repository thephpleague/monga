<?php

use League\Monga\Connection;
use League\Monga\Database;
use League\Monga;

class DatabaseTests extends PHPUnit_Framework_TestCase
{
	protected $database;

	protected $connection;

	public function setUp()
	{
		if ( ! $this->connection)
		{
			$this->connection = Monga::connection();
		}

		$this->database = null;
		$this->database = $this->connection->database('__unit_testing__');
	}

	public function tearDown()
	{
		$this->connection->dropDatabase('__unit_testing__');
	}

	public function testBasicNewDatabase()
	{
		$mongo = new Mongo();
		$database = new Database($mongo->{"__unit_testing__"});
		$reflection = new ReflectionObject($database);
		$propertyDatabase = $reflection->getProperty('database');
		$propertyDatabase->setAccessible(true);
		$propertyConnection = $reflection->getProperty('connection');
		$propertyConnection->setAccessible(true);
		$this->assertInstanceOf('MongoDB', $propertyDatabase->getValue($database));
		$this->assertNull($propertyConnection->getValue($database));
	}

	public function testNewDatabaseWithConnection()
	{
		$mongo = new Mongo();
		$connection = Monga::connection();
		$connectionHash = spl_object_hash($connection);
		$database = new Database($mongo->{"__unit_testing__"}, $connection);
		$reflection = new ReflectionObject($database);
		$propertyDatabase = $reflection->getProperty('database');
		$propertyDatabase->setAccessible(true);
		$propertyConnection = $reflection->getProperty('connection');
		$propertyConnection->setAccessible(true);
		$this->assertInstanceOf('MongoDB', $propertyDatabase->getValue($database));
		$this->assertInstanceOf('League\Monga\Connection', $propertyConnection->getValue($database));
		$this->assertEquals($connectionHash, spl_object_hash($propertyConnection->getValue($database)));
	}

	public function testGetDatabase()
	{
		$database = $this->database->getDatabase();
		$this->assertInstanceOf('MongoDB', $database);
	}

	public function testSetDatabase()
	{
		$original = $this->database->getDatabase();
		$originalHash = spl_object_hash($original);
		$new = $this->connection->database('_other_unknown_', false);
		$newHash = spl_object_hash($new);
		$this->database->setDatabase($new);
		$reflection = new ReflectionObject($this->database);
		$property = $reflection->getProperty('database');
		$property->setAccessible(true);
		$this->assertInstanceOf('MongoDB', $property->getValue($this->database));
		$this->assertEquals($newHash, spl_object_hash($property->getValue($this->database)));
		$this->assertNotEquals($originalHash, spl_object_hash($property->getValue($this->database)));
		$this->database->setDatabase('from_string');
		$this->assertInstanceOf('MongoDB', $property->getValue($this->database));
		$this->assertNotEquals($originalHash, spl_object_hash($property->getValue($this->database)));
		$this->assertNotEquals($newHash, spl_object_hash($property->getValue($this->database)));
		$this->database->setDatabase($original);
	}

	public function testGetConnection()
	{
		$database = $this->database->getConnection();
		$this->assertInstanceOf('League\Monga\Connection', $database);
	}

	public function testSetConnection()
	{
		$original = $this->database->getConnection();
		$originalHash = spl_object_hash($original);
		$new = new Connection();
		$newHash = spl_object_hash($new);
		$this->database->setConnection($new);
		$reflection = new ReflectionObject($this->database);
		$property = $reflection->getProperty('connection');
		$property->setAccessible(true);
		$this->assertInstanceOf('League\Monga\Connection', $property->getValue($this->database));
		$this->assertEquals($newHash, spl_object_hash($property->getValue($this->database)));
		$this->assertNotEquals($originalHash, spl_object_hash($property->getValue($this->database)));
		$this->database->setConnection($original);
	}

	public function testListCollections()
	{
		$collections = $this->database->listCollections();
		$this->assertInternalType('array', $collections);
		$this->assertEmpty($collections);
	}

	public function testListCollectionsWithCollections()
	{
		$database = $this->database->getDatabase();
		$database->demo1->insert(array('some' => 'data'));
		$database->demo2->insert(array('some' => 'data'));
		$database->demo3->insert(array('some' => 'data'));

		$collections = $this->database->listCollections();
		$this->assertCount(3, $collections);
		$this->assertEquals(array('demo1', 'demo2', 'demo3'), $collections);
	}

	public function testHasCollection()
	{
		$this->database->getDatabase()->demo->insert(array('some' => 'data'));

		$this->assertTrue($this->database->hasCollection('demo'));
		$this->assertFalse($this->database->hasCollection('__unknown_collection__'));
	}

	public function testAllCollections()
	{
		$all = $this->database->allCollections();
		$this->assertEmpty($all);

		$database = $this->database->getDatabase();
		$database->demo1->insert(array('some' => 'data'));
		$database->demo2->insert(array('some' => 'data'));
		$database->demo3->insert(array('some' => 'data'));

		$all = $this->database->allCollections(true);
		$this->assertContainsOnlyInstancesOf('League\Monga\Collection', $all);

		$raw = $this->database->allCollections();
		$this->assertContainsOnlyInstancesOf('MongoCollection', $raw);
	}

	public function testGetCollection()
	{
		$collection = $this->database->collection('demo');
		$this->assertInstanceOf('League\Monga\Collection', $collection);
	}

	public function testGetRawCollection()
	{
		$collection = $this->database->collection('demo', false);
		$this->assertInstanceOf('MongoCollection', $collection);
	}

	public function testGetFilesystem()
	{
		$fs = $this->database->filesystem('demo');
		$this->assertInstanceOf('League\Monga\Filesystem', $fs);
	}

	public function testGetRawFilesystem()
	{
		$fs = $this->database->filesystem('demo', false);
		$this->assertInstanceOf('MongoGridFS', $fs);
	}

	public function testGetRef()
	{
		$item = array('something' => 'something');

		$collection = $this->database->getDatabase()->{'demo'};
		$collection->insert($item);
		$ref = MongoDBRef::create('demo', $item['_id']);

		$fetched = $this->database->getRef($ref);
		$this->assertEquals($item, $fetched);
	}

	public function testExecuteCode()
	{
		$result = $this->database->executeCode('function(){ return 1;}');

		$this->assertInternalType('array', $result);
		$this->assertEquals(1, $result['retval']);
	}

	public function testExecuteParams()
	{
		$name = 'Frank';
		$result = $this->database->executeCode('function(name){ return name;}', array($name));
		$this->assertInternalType('array', $result);
		$this->assertEquals($name, $result['retval']);
	}

	public function testExecuteMongoCode()
	{
		$result = $this->database->executeCode(new MongoCode('function(){ return 1;}'));
		$this->assertInternalType('array', $result);
		$this->assertEquals(1, $result['retval']);
	}

	public function testCommand()
	{
		$this->database->collection('demo')->insert(array('this' => 'that'));
		$result = $this->database->command(array('count' => 'demo'));
		$this->assertInternalType('array', $result);
		$this->assertEquals(1, $result['ok']);
		$this->assertEquals(1, $result['n']);
	}

	public function testDrop()
	{
		$this->assertTrue($this->database->drop());
	}
}
