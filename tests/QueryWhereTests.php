<?php


class QueryWhereTests extends PHPUnit_Framework_TestCase
{
	protected $query;

	public function setUp()
	{
		$this->query = new Monga\Query\Where();
	}

	public function getProperty($property)
	{
		$reflection = new ReflectionObject($this->query);
		$property = $property = $reflection->getProperty($property);
		$property->setAccessible(true);
		return $property->getValue($this->query);
	}

	public function testSetWhere()
	{
		$this->assertNull($this->getProperty('where'));
		$this->query->setWhere(array('name' => 'John'));

		$expected = array(
			'$or' => array(
				array(
					'$and' => array(
						array('name' => 'John'),
					)
				)
			)
		);

		$this->assertEquals($expected, $this->getProperty('where'));
		$this->query->setWhere(array());
		$this->assertNull($this->getProperty('where'));
	}

	public function testWhere()
	{
		$this->query->where('name', 'John');

		$expected = array(
			'$or' => array(
				array(
					'$and' => array(
						array('name' => 'John'),
					)
				)
			)
		);

		$this->assertEquals($expected, $this->getProperty('where'));
	}
}