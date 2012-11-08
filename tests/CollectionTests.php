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
}