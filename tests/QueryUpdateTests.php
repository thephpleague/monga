<?php

class QueryUpdateTests extends PHPUnit_Framework_TestCase
{
	protected $update;

	public function setUp()
	{
		$this->update = new Monga\Query\Update();
	}

	public function getProperty($property)
	{
		$reflection = new ReflectionObject($this->update);
		$property = $property = $reflection->getProperty($property);
		$property->setAccessible(true);
		return $property->getValue($this->update);
	}

	public function testSingle()
	{
		$this->assertTrue($this->getProperty('multiple'));
		$this->update->single();
		$this->assertFalse($this->getProperty('multiple'));
		$this->update->single(false);
		$this->assertTrue($this->getProperty('multiple'));
	}

	public function testMultiple()
	{
		$this->assertTrue($this->getProperty('multiple'));
		$this->update->multiple(false);
		$this->assertFalse($this->getProperty('multiple'));
		$this->update->multiple();
		$this->assertTrue($this->getProperty('multiple'));
	}

	public function testUpsert()
	{
		$this->update->upsert(false);
		$this->assertFalse($this->getProperty('upsert'));
		$this->update->upsert();
		$this->assertTrue($this->getProperty('upsert'));
	}

	public function testSet()
	{
		$this->update->set('field', 'value');

		$this->assertEquals(array(
			'field' => array('$set', 'value'),
		),
		$this->getProperty('update'));
	}

	public function testRemove()
	{
		$this->update->remove('one', 'two');

		$this->assertEquals(array(
			'one' => array('$unset', 1),
			'two' => array('$unset', 1),
		),
		$this->getProperty('update'));
	}

	public function testRename()
	{
		$this->update->rename('one', 'two');

		$this->assertEquals(array(
			'one' => array('$rename', 'two'),
		),
		$this->getProperty('update'));
	}

	public function testPush()
	{
		$this->update->push('field', 'value');
		$this->update->push('field2', 'value', true);

		$this->assertEquals(array(
			'field' => array('$push', 'value'),
			'field2' => array('$pushAll', 'value'),
		),
		$this->getProperty('update'));
	}

	public function testPushAll()
	{
		$this->update->pushAll('field', array('value', 'other'));

		$this->assertEquals(array(
			'field' => array('$pushAll', array('value', 'other')),
		),
		$this->getProperty('update'));
	}

	public function testPull()
	{
		$this->update->pull('field', 'value');
		$this->update->pull('field2', 10, '$gt', true);

		$this->assertEquals(array(
			'field' => array('$pull', 'value'),
			'field2' => array('$pullAll', array('$gt' => 10)),
		),
		$this->getProperty('update'));
	}

	public function testPullAll()
	{
		$this->update->pullAll('field', array('value', 'other'));

		$this->assertEquals(array(
			'field' => array('$pullAll', array('value', 'other')),
		),
		$this->getProperty('update'));
	}

	public function testAddToSet()
	{
		$this->update->addToSet('field', array('value', 'other'));

		$this->assertEquals(array(
			'field' => array('$addToSet', array('value', 'other')),
		),
		$this->getProperty('update'));
	}

	public function testPop()
	{
		$this->update->pop('field');

		$this->assertEquals(array(
			'field' => array('$pop', 1),
		),
		$this->getProperty('update'));
	}

	public function testUnshift()
	{
		$this->update->unshift('field');

		$this->assertEquals(array(
			'field' => array('$pop', -1),
		),
		$this->getProperty('update'));
	}

	public function testIncrement()
	{
		$this->update->increment('field', 2);

		$this->assertEquals(array(
			'field' => array('$inc', 2),
		),
		$this->getProperty('update'));
	}

	public function testGetUpdate()
	{
		$expected = array(
			'$set' => array('field' => 'value'),
			'$inc' => array('downloads' => 1),
		);

		$result = $this->update->increment('downloads', 1)->set('field', 'value')->getUpdate();

		$this->assertEquals($expected, $result);
	}

	public function testGetOptions()
	{
		$this->update->single();
		$this->assertEquals(array(
			'safe' => false,
			'fsync' => false,
			'timeout' => MongoCursor::$timeout,
			'upsert' => false,
			'multiple' => false,
		), $this->update->getOptions());
	}
}