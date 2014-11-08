<?php


class QueryWhereTests extends PHPUnit_Framework_TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new League\Monga\Query\Where();
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
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
        $this->query->setWhere(array());
        $this->assertNull($this->getProperty('where'));
    }

    public function testWhere()
    {
        $this->query->where('name', 'John');
        $this->query->where(array(
            'surname' => 'Doe',
            'age' => 25,
        ));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            'name' => 'John',
                            'surname' => 'Doe',
                            'age' => 25,
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhere()
    {
        $this->query->andWhere('name', 'John');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => 'John'),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhere()
    {
        $this->query->where('name', 'John')
            ->orWhere('name', 'Steve')
            ->orWhere(array('name' => 'Jack', 'surname' => 'Johnes'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => 'John'),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => 'Steve'),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => 'Jack'),
                    ),
                ),
                array(
                    '$and' => array(
                        array('surname' => 'Johnes'),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereNot()
    {
        $this->query->whereNot('name', 'John');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$ne' => 'John')),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereNot()
    {
        $this->query->andWhereNot('name', 'John');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$ne' => 'John')),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereNot()
    {
        $this->query->whereNot('name', 'John')
            ->orWhereNot('name', 'Steve');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$ne' => 'John')),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$ne' => 'Steve')),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereLike()
    {
        $this->query->whereLike('field', '%value')
            ->whereLike('field', 'value%')
            ->whereLike('other', 'value', 'imxs');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$and' => array(
                                array(
                                    'field' => new MongoRegex('/value$/imxsu'),
                                ),
                                array(
                                    'field' => new MongoRegex('/^value/imxsu'),
                                    'other' => new MongoRegex('/^value$/imxs'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereLike()
    {
        $this->query->whereLike('field', '%value')
            ->andWhereLike('field', 'value%')
            ->andWhereLike('other', 'value', 'imxs');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$and' => array(
                                array(
                                    'field' => new MongoRegex('/value$/imxsu'),
                                ),
                                array(
                                    'field' => new MongoRegex('/^value/imxsu'),
                                    'other' => new MongoRegex('/^value$/imxs'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereLike()
    {
        $this->query->whereLike('field', '%value')
            ->andWhereLike('field', 'value%')
            ->andWhereLike('other', 'value', 'imxs')
            ->orWhereLike('field', 'value');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$and' => array(
                                array(
                                    'field' => new MongoRegex('/value$/imxsu'),
                                ),
                                array(
                                    'field' => new MongoRegex('/^value/imxsu'),
                                    'other' => new MongoRegex('/^value$/imxs'),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    '$and' => array(
                        array(
                            'field' => new MongoRegex('/^value$/imxsu'),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereRegex()
    {
        $this->query->whereRegex('field', '/^value/imxsu');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('field' => new MongoRegex('/^value/imxsu')),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereRegex()
    {
        $this->query->whereRegex('field', '/^value/imxsu')
            ->orWhereRegex('field', '/value$/imxsu');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('field' => new MongoRegex('/^value/imxsu')),
                    ),
                ),
                array(
                    '$and' => array(
                        array('field' => new MongoRegex('/value$/imxsu')),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereRegex()
    {
        $this->query->whereRegex('field', '/^value/imxsu')
            ->andWhereRegex('other', '/value$/imxsu');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            'field' => new MongoRegex('/^value/imxsu'),
                            'other' => new MongoRegex('/value$/imxsu'),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereExists()
    {
        $this->query->whereExists('name');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$exists' => true)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereExists()
    {
        $this->query->andWhereExists('name');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$exists' => true)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereExists()
    {
        $this->query->whereExists('name')
            ->orWhereExists('name');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$exists' => true)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$exists' => true)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereNotExists()
    {
        $this->query->whereNotExists('name');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$exists' => false)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereNotExists()
    {
        $this->query->andWhereNotExists('name');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$exists' => false)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereNotExists()
    {
        $this->query->whereNotExists('name')
            ->orWhereNotExists('name');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$exists' => false)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$exists' => false)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereIn()
    {
        $this->query->whereIn('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$in' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereIn()
    {
        $this->query->andWhereIn('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$in' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereIn()
    {
        $this->query->whereIn('name', array('key' => 1, 2, '3'))
            ->orWhereIn('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$in' => array(1, 2, '3'))),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$in' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereAll()
    {
        $this->query->whereAll('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$all' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereAll()
    {
        $this->query->andWhereAll('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$all' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereAll()
    {
        $this->query->whereAll('name', array('key' => 1, 2, '3'))
            ->orWhereAll('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$all' => array(1, 2, '3'))),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$all' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereNotIn()
    {
        $this->query->whereNotIn('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$nin' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereNotIn()
    {
        $this->query->andWhereNotIn('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$nin' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereNotIn()
    {
        $this->query->whereNotIn('name', array('key' => 1, 2, '3'))
            ->orWhereNotIn('name', array('key' => 1, 2, '3'));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$nin' => array(1, 2, '3'))),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$nin' => array(1, 2, '3'))),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereSize()
    {
        $this->query->whereSize('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$size' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereSize()
    {
        $this->query->andWhereSize('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$size' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereSize()
    {
        $this->query->whereSize('name', 10)
            ->orWhereSize('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$size' => 10)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$size' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereType()
    {
        $this->query->whereType('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$type' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWhereType()
    {
        $this->query->whereType('name', 'invalid');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$type' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereType()
    {
        $this->query->andWhereType('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$type' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereType()
    {
        $this->query->whereType('name', 10)
            ->orWhereType('name', 'string');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$type' => 10)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$type' => 2)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereLt()
    {
        $this->query->whereLt('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereLt()
    {
        $this->query->andWhereLt('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereLt()
    {
        $this->query->whereLt('name', 10)
            ->orWhereLt('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lt' => 10)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$lt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereLte()
    {
        $this->query->whereLte('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lte' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereLte()
    {
        $this->query->andWhereLte('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lte' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereLte()
    {
        $this->query->whereLte('name', 10)
            ->orWhereLte('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lte' => 10)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$lte' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereGt()
    {
        $this->query->whereGt('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$gt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereGt()
    {
        $this->query->andWhereGt('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$gt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereGt()
    {
        $this->query->whereGt('name', 10)
            ->orWhereGt('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$gt' => 10)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$gt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereGte()
    {
        $this->query->whereGte('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$gte' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereGte()
    {
        $this->query->andWhereGte('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$gte' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereGte()
    {
        $this->query->whereGte('name', 10)
            ->orWhereGte('name', 10);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$gte' => 10)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$gte' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereNear()
    {
        $this->query->whereNear('location', 10, 10, array('$maxDistance' => 5));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('location' => array('$near' => array(10, 10), '$maxDistance' => 5)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereNear()
    {
        $this->query->andWhereNear('location', 10, 10, array('$maxDistance' => 5));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('location' => array('$near' => array(10, 10), '$maxDistance' => 5)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereNear()
    {
        $this->query->whereNear('location', 10, 10, array('$maxDistance' => 5))
            ->orWhereNear('location', 10, 10, array('$maxDistance' => 5));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('location' => array('$near' => array(10, 10), '$maxDistance' => 5)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('location' => array('$near' => array(10, 10), '$maxDistance' => 5)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereWithin()
    {
        $this->query->whereWithin('location', array('$box' => array(array(0, 0), array(10, 10))), array('$maxDistance' => 5));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('location' => array('$within' => array('$box' => array(array(0,0),array(10,10))), '$maxDistance' => 5)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereWithin()
    {
        $this->query->andWhereWithin('location', array('$box' => array(array(0, 0), array(10, 10))), array('$maxDistance' => 5));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('location' => array('$within' => array('$box' => array(array(0,0),array(10,10))), '$maxDistance' => 5)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereWithin()
    {
        $this->query->whereWithin('location', array('$box' => array(array(0, 0), array(10, 10))), array('$maxDistance' => 5))
            ->orWhereWithin('location', array('$box' => array(array(0, 0), array(10, 10))), array('$maxDistance' => 5));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('location' => array('$within' => array('$box' => array(array(0,0),array(10,10))), '$maxDistance' => 5)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('location' => array('$within' => array('$box' => array(array(0,0),array(10,10))), '$maxDistance' => 5)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereBetween()
    {
        $this->query->whereBetween('name', 10, 15);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lt' => 15, '$gt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereBetween()
    {
        $this->query->andWhereBetween('name', 10, 15);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lt' => 15, '$gt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereBetween()
    {
        $this->query->whereBetween('name', 10, 15)
            ->orWhereBetween('name', 10, 15);

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('name' => array('$lt' => 15, '$gt' => 10)),
                    ),
                ),
                array(
                    '$and' => array(
                        array('name' => array('$lt' => 15, '$gt' => 10)),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testWhereId()
    {
        $this->query->whereId('50a2cdf711fa67c551000001');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('_id' => new MongoId('50a2cdf711fa67c551000001')),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testAndWhereId()
    {
        $this->query->andWhereId('50a2cdf711fa67c551000001');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('_id' => new MongoId('50a2cdf711fa67c551000001')),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrWhereId()
    {
        $this->query->orWhereId('50a2cdf711fa67c551000001')
            ->orWhereId(new MongoId('50a2cdf711fa67c551000001'), 'id');

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('_id' => new MongoId('50a2cdf711fa67c551000001')),
                    ),
                ),
                array(
                    '$and' => array(
                        array('id' => new MongoId('50a2cdf711fa67c551000001')),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testNorWhere()
    {
        $this->query->norWhere(function ($query) {
            $query->where('field', 'value');
        });

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$nor' => array(
                                array('field' => 'value'),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testEmptyNorWhere()
    {
        $this->query->norWhere(function () {});
        $this->assertNull($this->getProperty('where'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidNorWhere()
    {
        $this->query->norWhere(false);
    }

    public function testAndNorWhere()
    {
        $this->query->andNorWhere(function ($query) {
            $query->where('field', 'value');
        });

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$nor' => array(
                                array('field' => 'value'),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrNorWhere()
    {
        $this->query->norWhere(function ($query) {
            $query->where('field', 'value');
        })
        ->orNorWhere(function ($query) {
            $query->where('field', 'value');
        });

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$nor' => array(
                                array('field' => 'value'),
                            ),
                        ),
                    ),
                ),
                array(
                    '$and' => array(
                        array(
                            '$nor' => array(
                                array('field' => 'value'),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testNotWhere()
    {
        $this->query->notWhere(function ($query) {
            $query->where('field', 'value');
        });

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$not' => array(
                                array('field' => 'value'),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testEmptyNotWhere()
    {
        $this->query->notWhere(function () {});
        $this->assertNull($this->getProperty('where'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidNotWhere()
    {
        $this->query->notWhere(false);
    }

    public function testAndNotWhere()
    {
        $this->query->andNotWhere(function ($query) {
            $query->where('field', 'value');
        });

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$not' => array(
                                array('field' => 'value'),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testOrNotWhere()
    {
        $this->query->notWhere(function ($query) {
            $query->where('field', 'value');
        })
        ->orNotWhere(function ($query) {
            $query->where('field', 'value');
        });

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$not' => array(
                                array('field' => 'value'),
                            ),
                        ),
                    ),
                ),
                array(
                    '$and' => array(
                        array(
                            '$not' => array(
                                array('field' => 'value'),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }

    public function testGetWhere()
    {
        $this->assertEquals(array(), $this->query->getWhere());
        $this->query->where('this', 'that');
        $this->assertEquals(array('this' => 'that'), $this->query->getWhere());
        $this->query->orWhere(function ($query) {
            $query->where('one', 1)
                ->orWhere('two', 2);
        });
        $this->query->where(function () {});
        $this->assertEquals(array(
            '$or' => array(
                array('this' => 'that'),
                array(
                    '$or' => array(
                        array('one' => 1),
                        array('two' => 2),
                    ),
                ),
            ),
        ), $this->query->getWhere());

        $this->query = new League\Monga\Query\Where();

        $this->query->where(function ($query) {
            $query->where('one', 1)
                ->orWhere('two', 2);
        });

        $this->assertEquals(array(
            '$or' => array(
                array('one' => 1),
                array('two' => 2),
            ),
        ), $this->query->getWhere());

        $this->query = new League\Monga\Query\Where();

        $this->query->setWhere(array(
            '$or' => array(
                array('$and' => array(
                    array('$or' => array(
                        array('this' => 'that'),
                    )),
                )),
            ),
        ));

        $this->assertEquals(array('this' => 'that'), $this->query->getWhere());

        $this->query->setWhere(array());

        $this->query->norWhere(function ($query) {
            $query->where('one', 1)
                ->where('two', 2);
        })->norWhere(function ($query) {
            $query->where('three', 1)
                ->where('four', 2);
        });

        $this->assertEquals(array(
            '$nor' => array(
                array('one' => 1, 'two' => 2),
                array('three' => 1, 'four' => 2),
        ), ), $this->query->getWhere());

        $this->query->setWhere(array());
        $this->query->where('name', 'john')
            ->where('name', 'jim')
            ->where(function ($query) {
                $query->where('this', 'one')
                    ->where('is', 'tricky');
            });

        $this->assertEquals(array(
            '$and' => array(
                array(
                    '$and' => array(
                        array('name' => 'john'),
                        array('name' => 'jim'),
                    ),
                ),
                array(
                    'this' => 'one',
                    'is' => 'tricky',
                ),
            ),
        ), $this->query->getWhere());

        $this->query->setWhere(array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$and' => array(
                                array(
                                    'name' => 'Frank',
                                    'surname' => 'de Jonge',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ));

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array(
                            '$and' => array(
                                array(
                                    'name' => 'Frank',
                                    'surname' => 'de Jonge',
                                ),
                                array(
                                    'name' => 'Billy',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->query->where('name', 'Billy');
    }

    public function testOrWhereClosure()
    {
        $this->query->orWhere(function ($query) {
            $query->where('something', 'broken');
        });

        $expected = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('something' => 'broken'),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $this->getProperty('where'));
    }
}
