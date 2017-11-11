<?php

use League\Monga;
use League\Monga\Collection;
use League\Monga\Database;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CollectionTests extends TestCase
{
    protected $database;
    protected $connection;
    protected $collection;

    public function setUp()
    {
        if (! $this->connection) {
            $this->connection = Monga::connection();
        }

        $this->database = $this->connection->database('__unit_testing__');
        $this->collection = $this->database->collection('__unit_testing__');
    }

    public function tearDown()
    {
        $this->database->collection('__unit_testing__')->getCollection()->drop();
        // $this->connection->dropDatabase('__unit_testing__');
        $this->database = null;
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

    public function testSetMaxRetries()
    {
        $this->collection->setMaxRetries(5);
        $reflection = new ReflectionObject($this->collection);
        $property = $reflection->getProperty('maxRetries');
        $property->setAccessible(true);
        $this->assertEquals(5, $property->getValue($this->collection));
    }

    public function testCount()
    {
        $result = $this->collection->count();
        $this->assertEquals(0, $result);
        $this->collection->getCollection()->insert(['this' => 'value']);
        $result = $this->collection->count();
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
        $where = new League\Monga\Query\Where();
        $where->where('name', 'Frank');
        $result = $this->collection->count($where);
        $this->assertEquals(0, $result);
        $this->collection->getCollection()->insert(['name' => 'Frank']);
        $result = $this->collection->count($where);
        $this->assertEquals(1, $result);
    }

    public function testCountClosure()
    {
        $where = function ($query) {
            $query->where('name', 'Frank');
        };
        $result = $this->collection->count($where);
        $this->assertEquals(0, $result);
        $this->collection->getCollection()->insert(['name' => 'Frank']);
        $result = $this->collection->count($where);
        $this->assertEquals(1, $result);
    }

    public function testDrop()
    {
        $result = $this->collection->drop();
        $this->assertFalse($result);
        $this->collection->insert(['name' => 'Frank']);
        $result = $this->collection->drop();
        $this->assertTrue($result);
    }

    public function testTruncate()
    {
        $result = $this->collection->truncate();
        $this->assertTrue($result);
    }

    public function testRemove()
    {
        $result = $this->collection->remove([]);
        $this->assertTrue($result);
    }

    public function testRemoveWhere()
    {
        $this->collection->getCollection()->insert(['name' => 'Frank']);
        $this->assertEquals(1, $this->collection->count());
        $result = $this->collection->remove(['name' => 'Bert']);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->collection->count());
        $result = $this->collection->remove(['name' => 'Frank']);
        $this->assertTrue($result);
        $this->assertEquals(0, $this->collection->count());
    }

    public function testRemoveWhereClosure()
    {
        $closure = function ($query) {
            $query->where('name', 'Frank');
        };
        $closure2 = function ($query) {
            $query->where('name', 'Bert');
        };
        $this->collection->getCollection()->insert(['name' => 'Frank']);
        $this->assertEquals(1, $this->collection->count());
        $result = $this->collection->remove($closure2);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->collection->count());
        $result = $this->collection->remove($closure);
        $this->assertTrue($result);
        $this->assertEquals(0, $this->collection->count());
    }

    public function testRemoveWithQuery()
    {
        $where = new League\Monga\Query\Remove();

        $where->where('name', 'Frank');

        $this->collection->getCollection()->insert(['name' => 'Frank']);
        $this->assertEquals(1, $this->collection->count());
        $result = $this->collection->remove($where);
        $this->assertTrue($result);
        $this->assertEquals(0, $this->collection->count());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidRemove()
    {
        $this->collection->remove(false);
    }

    public function testListIndexes()
    {
        $this->assertInternalType('array', $this->collection->listIndexes());
    }

    public function testDistinct()
    {
        $collection = m::mock('MongoCollection');
        $collection->shouldReceive('distinct')
            ->with('surname', ['age' => 25])
            ->once()
            ->andReturn(['randomstring']);

        $expected = ['randomstring'];
        $c = new Collection($collection);
        $result = $c->distinct('surname', ['age' => 25]);
        $this->assertEquals($expected, $result);
    }

    public function testDistinctClosure()
    {
        $collection = m::mock('MongoCollection');
        $collection->shouldReceive('distinct')
            ->with('surname', ['age' => 25])
            ->once()
            ->andReturn(['randomstring']);

        $expected = ['randomstring'];
        $c = new Collection($collection);
        $result = $c->distinct('surname', function ($w) {
            $w->where('age', 25);
        });
        $this->assertEquals($expected, $result);
    }

    public function testAggregation()
    {
        $collection = m::mock('MongoCollection');
        $collection->shouldReceive('aggregate')
            ->with(['randomstring'])
            ->once()
            ->andReturn(['randomstring']);

        $expected = ['randomstring'];
        $c = new Collection($collection);
        $result = $c->aggregate(['randomstring']);
        $this->assertEquals($expected, $result);
    }

    public function testAggregationClosure()
    {
        $collection = m::mock('MongoCollection');
        $collection->shouldReceive('aggregate')
            ->with([
                ['$limit' => 1],
            ])
            ->once()
            ->andReturn(['randomstring']);

        $expected = ['randomstring'];
        $c = new Collection($collection);
        $result = $c->aggregate(function ($a) {
            $a->limit(1);
        });
        $this->assertEquals($expected, $result);
    }

    public function testIndexes()
    {
        $result = false;
        $callback = function () use (&$result) {
            $result = true;
        };
        $this->collection->indexes($callback);

        $this->assertTrue($result);
    }

    public function testFind()
    {
        $result = $this->collection->find();
        $this->assertInstanceOf('League\Monga\Cursor', $result);
    }

    public function testFindWithQuery()
    {
        $query = new League\Monga\Query\Find();
        $result = $this->collection->find($query);

        $this->assertInstanceOf('League\Monga\Cursor', $result);
    }

    public function testFindOneEmpty()
    {
        $result = $this->collection->findOne();
        $this->assertNull($result);
    }

    public function testFindOneNotEmpty()
    {
        $this->collection->insert(['some' => 'value']);
        $result = $this->collection->findOne();
        $this->assertInternalType('array', $result);
        $this->assertEquals('value', $result['some']);
    }

    public function testFindOneWithPostFindAction()
    {
        $result = $this->collection->findOne(function ($query) {
            $query->where('some', 'value')
                ->orderBy('some', 'asc')
                ->skip(0)
                ->limit(1);
        });

        $this->assertNull($result);
    }

    public function testFindOneWithPostFindActionWithResult()
    {
        $this->collection->insert(['some' => 'value']);

        $result = $this->collection->findOne(function ($query) {
            $query->where('some', 'value')
                ->orderBy('some', 'asc')
                ->skip(0)
                ->limit(1);
        });

        $this->assertInternalType('array', $result);
        $this->assertEquals('value', $result['some']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidFind()
    {
        $this->collection->find(false);
    }

    public function testInsertOne()
    {
        $result = $this->collection->insert(['new' => 'entry']);

        $this->assertInstanceOf('MongoId', $result);
    }

    public function testInsertMultiple()
    {
        $result = $this->collection->insert([
            ['number' => 'one'],
            ['number' => 'two'],
        ]);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf('MongoId', $result);
    }

    public function testInvalidInsert()
    {
        $collection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['insert'])
            ->getMock();
        $collection->expects($this->once())
            ->method('insert')
            ->with($this->equalTo(['invalid']))
            ->will($this->returnValue(false));

        $this->collection->setCollection($collection);
        $result = $this->collection->insert(['invalid']);
        $this->assertFalse($result);
    }

    public function testInsertMultipleInvalid()
    {
        $input = [
            [false], [false],
        ];
        $collection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['batchInsert'])
            ->getMock();
        $collection->expects($this->once())
            ->method('batchInsert')
            ->with($this->equalTo($input))
            ->will($this->returnValue(false));

        $this->collection->setCollection($collection);
        $result = $this->collection->insert([
            [false], [false],
        ]);

        $this->assertFalse($result);
    }

    public function testSave()
    {
        $item = ['name' => 'Frank'];
        $result = $this->collection->save($item);
        $this->assertTrue($result);
    }

    public function testUpdate()
    {
        $result = $this->collection->update(['name' => 'changed']);
        $this->assertTrue($result);
    }

    public function testUpdateClosure()
    {
        $result = $this->collection->update(function ($query) {
            $query->set('name', 'changed')
                ->increment('viewcount', 2);
        });

        $this->assertTrue($result);
    }

    public function testUpdateWithQuery()
    {
        $query = new League\Monga\Query\Update();

        $query->set('name', 'changed');

        $result = $this->collection->update($query);
        $this->assertTrue($result);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidUpdate()
    {
        $result = $this->collection->update(false);
    }
}
