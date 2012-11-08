<?php

use Monga\Collection;
use Monga\Database;

class CollectionTests extends PHPUnit_Framework_TestCase
{
	protected $database;

	protected $connection;

	protected $collection;

	public function setUp()
	{
		if ( ! $this->connection)
		{
			$this->connection = Monga::connection();
		}

		$this->database = null;
		$this->database = $this->connection->database('__unit_testing__');
		$this->collection = $this->database->collection('__unit_testing__');
	}

	public function tearDown()
	{
		$this->database->collection('__unit_testing__')->getCollection()->drop();
		$this->connection->dropDatabase('__unit_testing__');
	}

	/**
	 * @expectedException Exception
	 */
	public function testInvalidConstructor()
	{
		$collection = new Collection(false);
	}

	public function testGetCollection()
	{
		$collection = $this->collection->getCollection();

		$this->assertInstanceOf('MongoCollection', $collection);
	}

	public function testSetCollection()
	{
		$original = $this->collection->getCollection();
		$originalHash = spl_object_hash($original);
		$new = $this->database->collection('__different__')->getCollection();
		$newHash = spl_object_hash($new);
		$this->collection->setCollection($new);
		$reflection = new ReflectionObject($this->collection);
		$property = $reflection->getProperty('collection');
		$property->setAccessible(true);
		$this->assertInstanceOf('MongoCollection', $property->getValue($this->collection));
		$this->assertEquals($newHash, spl_object_hash($property->getValue($this->collection)));
		$this->assertNotEquals($originalHash, spl_object_hash($property->getValue($this->collection)));
		$this->collection->setCollection($original);
	}

	public function testCount()
	{
		$result = $this->collection->count();
		$this->assertEquals(0, $result);
		$result = $this->collection->getCollection()->insert(array('this' => 'value'));
		$this->assertEquals(1, $result);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCountException()
	{
		$this->collection->count(false);
	}

	public function testCountWhere()
	{
		$where = new Monga\Query\Where();
		$where->where('name', 'Frank');
		$result = $this->collection->count($where);
		$this->assertEquals(0, $result);
		$result = $this->collection->getCollection()->insert(array('name' => 'Frank'));
		$this->assertEquals(1, $result);
	}

	public function testCountClosure()
	{
		$where = function($query){
			$query->where('name', 'Frank');
		};
		$result = $this->collection->count($where);
		$this->assertEquals(0, $result);
		$result = $this->collection->getCollection()->insert(array('name' => 'Frank'));
		$this->assertEquals(1, $result);
	}
}