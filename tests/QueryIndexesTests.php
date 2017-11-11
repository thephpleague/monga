<?php

use League\Monga\Query\Indexes as i;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class QueryIndexesTests extends TestCase
{
    protected $indexes;

    public function tearDown()
    {
        m::close();
    }

    public function testGetCollection()
    {
        $collection = m::mock('MongoCollection');
        $indexes = new i($collection);
        $this->assertInstanceOf('MongoCollection', $indexes->getCollection());
    }

    public function testSetCollection()
    {
        $collection = m::mock('MongoCollection');
        $collection2 = m::mock('MongoCollection');
        $indexes = new i($collection);

        $hash = spl_object_hash($collection);
        $hash2 = spl_object_hash($collection2);

        $this->assertEquals($hash, spl_object_hash($indexes->getCollection()));
        $indexes->setCollection($collection2);
        $this->assertEquals($hash2, spl_object_hash($indexes->getCollection()));
    }

    public function testCreate()
    {
        $index = ['field' => 1];
        $mock = m::mock('MongoCollection');
        $mock->shouldReceive('ensureIndex')
            ->with($index, []);

        $i = new i($mock);
        $i->create($index);
    }

    public function testGeo()
    {
        $index = ['field' => '2d'];
        $mock = m::mock('MongoCollection');
        $mock->shouldReceive('ensureIndex')
            ->with($index, []);

        $i = new i($mock);
        $i->geo('field');
    }

    public function testPrepareIndex()
    {
        $i = new i(m::mock('MongoCollection'));

        $expected = [
            'asc' => 1,
            'desc' => -1,
            'geo' => '2d',
            'field' => 1,
        ];

        $reflection = new ReflectionObject($i);
        $method = $reflection->getMethod('prepareIndex');
        $method->setAccessible(true);
        $result = $method->invoke($i, [
            'asc' => 'asc',
            'desc' => 'desc',
            'geo' => 'geo',
            'field' => 1,
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testDrop()
    {
        $mock = m::mock('MongoCollection');
        $mock->shouldReceive('deleteIndex');
        $i = new i($mock);

        $i->drop('index_name');
    }

    public function testDropAll()
    {
        $mock = m::mock('MongoCollection');

        $mock->shouldReceive('deleteIndexes')
            ->withNoArgs();

        $i = new i($mock);
        $i->dropAll();
    }
}
