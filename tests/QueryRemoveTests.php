<?php

class QueryRemoveTests extends PHPUnit_Framework_TestCase
{
	protected $remove;

	public function setUp()
	{
		$this->remove = new League\Monga\Query\Remove();
	}

	public function getProperty($property)
	{
		$reflection = new ReflectionObject($this->remove);
		$property = $property = $reflection->getProperty($property);
		$property->setAccessible(true);
		return $property->getValue($this->remove);
	}

	public function testSingle()
	{
		$this->remove->single();
		$this->assertTrue($this->getProperty('justOne'));
		$this->remove->single(false);
		$this->assertFalse($this->getProperty('justOne'));
	}

	public function testMultiple()
	{
		$this->remove->multiple();
		$this->assertFalse($this->getProperty('justOne'));
		$this->remove->multiple(false);
		$this->assertTrue($this->getProperty('justOne'));
	}

	public function testGetOptions()
	{
		$this->remove->single();
		$this->assertEquals(array(
			'safe' => false,
			'fsync' => false,
			'timeout' => MongoCursor::$timeout,
			'justOne' => true
		), $this->remove->getOptions());
	}
}