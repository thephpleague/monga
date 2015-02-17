<?php

class QueryFindTests extends PHPUnit_Framework_TestCase
{
    protected $find;

    public function setUp()
    {
        $this->find = new League\Monga\Query\Find();
    }

    public function getProperty($property)
    {
        $reflection = new ReflectionObject($this->find);
        $property = $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($this->find);
    }

    public function testOrderBy()
    {
        $this->find->orderBy('one', 1);
        $this->find->orderBy('two', 'asc');
        $this->find->orderBy('three', 'desc');

        $this->assertEquals([
            'one' => 1,
            'two' => 1,
            'three' => -1,
        ],
        $this->getProperty('orderBy'));
    }

    public function testSelect()
    {
        $this->find->select('one', 'two');

        $this->assertEquals([
            'one' => 1,
            'two' => 1,
        ],
        $this->getProperty('fields'));
    }

    public function testExclude()
    {
        $this->find->exclude('one', 'two');

        $this->assertEquals([
            'one' => -1,
            'two' => -1,
        ],
        $this->getProperty('fields'));
    }

    public function testFields()
    {
        $this->find->fields([
            'one' => 1,
            'two' => false,
        ]);

        $this->assertEquals([
            'one' => 1,
            'two' => -1,
        ],
        $this->getProperty('fields'));
    }

    public function testGetFields()
    {
        $this->assertEmpty($this->getProperty('fields'));
        $this->find->fields([
            'one' => 1,
        ]);

        $this->assertEquals(['one' => 1], $this->getProperty('fields'));
    }

    public function testOne()
    {
        $this->assertFalse($this->getProperty('findOne'));
        $this->find->one();
        $this->assertTrue($this->getProperty('findOne'));
    }

    public function testMultiple()
    {
        $this->assertFalse($this->getProperty('findOne'));
        $this->find->multiple();
        $this->assertFalse($this->getProperty('findOne'));
        $this->find->multiple(false);
        $this->assertTrue($this->getProperty('findOne'));
    }

    public function testGetFindOne()
    {
        $this->assertFalse($this->find->getFindOne());
        $this->find->one();
        $this->assertTrue($this->find->getFindOne());
    }

    public function testGetPostFindActions()
    {
        $expected = [
            ['sort', ['one' => 1]],
            ['skip', 5],
            ['limit', 15],
        ];

        $this->find->skip(5)->limit(15)->orderBy('one', 'asc');

        $this->assertEquals($expected, $this->find->getPostFindActions());
    }
}
