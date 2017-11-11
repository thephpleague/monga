<?php

use PHPUnit\Framework\TestCase;

class QueryUpdateTests extends TestCase
{
    protected $update;

    public function setUp()
    {
        $this->update = new League\Monga\Query\Update();
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

        $this->assertEquals([
            'field' => ['$set', 'value'],
        ],
        $this->getProperty('update'));
    }

    public function testRemove()
    {
        $this->update->remove('one', 'two');

        $this->assertEquals([
            'one' => ['$unset', 1],
            'two' => ['$unset', 1],
        ],
        $this->getProperty('update'));
    }

    public function testRename()
    {
        $this->update->rename('one', 'two');

        $this->assertEquals([
            'one' => ['$rename', 'two'],
        ],
        $this->getProperty('update'));
    }

    public function testPush()
    {
        $this->update->push('field', 'value');
        $this->update->push('field2', 'value', true);

        $this->assertEquals([
            'field' => ['$push', 'value'],
            'field2' => ['$pushAll', 'value'],
        ],
        $this->getProperty('update'));
    }

    public function testPushAll()
    {
        $this->update->pushAll('field', ['value', 'other']);

        $this->assertEquals([
            'field' => ['$pushAll', ['value', 'other']],
        ],
        $this->getProperty('update'));
    }

    public function testPull()
    {
        $this->update->pull('field', 'value');
        $this->update->pull('field2', 10, '$gt', true);

        $this->assertEquals([
            'field' => ['$pull', 'value'],
            'field2' => ['$pullAll', ['$gt' => 10]],
        ],
        $this->getProperty('update'));
    }

    public function testPullAll()
    {
        $this->update->pullAll('field', ['value', 'other']);

        $this->assertEquals([
            'field' => ['$pullAll', ['value', 'other']],
        ],
        $this->getProperty('update'));
    }

    public function testAddToSet()
    {
        $this->update->addToSet('field', ['value', 'other']);

        $this->assertEquals([
            'field' => ['$addToSet', ['value', 'other']],
        ],
        $this->getProperty('update'));
    }

    public function testPop()
    {
        $this->update->pop('field');

        $this->assertEquals([
            'field' => ['$pop', 1],
        ],
        $this->getProperty('update'));
    }

    public function testUnshift()
    {
        $this->update->unshift('field');

        $this->assertEquals([
            'field' => ['$pop', -1],
        ],
        $this->getProperty('update'));
    }

    public function testIncrement()
    {
        $this->update->increment('field', 2);

        $this->assertEquals([
            'field' => ['$inc', 2],
        ],
        $this->getProperty('update'));
    }

    public function testGetUpdate()
    {
        $expected = [
            '$set' => ['field' => 'value'],
            '$inc' => ['downloads' => 1],
            '$atomic' => 1,
        ];

        $result = $this->update->increment('downloads', 1)->set('field', 'value')->atomic()->getUpdate();

        $this->assertEquals($expected, $result);
    }

    public function testGetOptions()
    {
        $this->update->single();
        $this->assertEquals([
            'w' => 0,
            'fsync' => false,
            'connectTimeoutMS' => MongoCursor::$timeout,
            'upsert' => false,
            'multiple' => false,
        ], $this->update->getOptions());
    }
}
