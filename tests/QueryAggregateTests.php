<?php

use League\Monga\Query\Aggregation as Agr;
use PHPUnit\Framework\TestCase;

class QueryAggregateTests extends TestCase
{
    public function testProject()
    {
        $a = new Agr();
        $a->project([
            'field' => 1,
            'other' => -1,
        ]);

        $expected = [
            ['$project' => [
                'field' => 1,
                'other' => -1,
            ]],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testProjectClosure()
    {
        $a = new Agr();
        $a->project(function ($p) {
            $p->select('field');
            $p->exclude('other');
            $p->alias('actual', 'alias');
        });

        $expected = [
            ['$project' => [
                'field' => 1,
                'other' => -1,
                'alias' => '$actual',
            ]],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testGroup()
    {
        $a = new Agr();
        $a->group([
            'num' => ['$sum' => '$num'],
        ]);

        $expected = [
            ['$group' => [
                'num' => ['$sum' => '$num'],
            ]],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testGroupClosure()
    {
        $a = new Agr();
        $a->group(function ($g) {
            $g->sum('num');
        });

        $expected = [
            ['$group' => [
                'num' => ['$sum' => 1],
            ]],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testUnwind()
    {
        $a = new Agr();
        $a->unwind('tags');
        $expected = [
            ['$unwind' => '$tags'],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testSkip()
    {
        $a = new Agr();
        $a->skip(1);
        $expected = [
            ['$skip' => 1],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testLimit()
    {
        $a = new Agr();
        $a->limit(1);
        $expected = [
            ['$limit' => 1],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testPipe()
    {
        $a = new Agr();
        $a->pipe(['$limit' => 1]);
        $expected = [
            ['$limit' => 1],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testMatch()
    {
        $a = new Agr();
        $a->match(['field' => 'value']);
        $expected = [
            [
                '$match' => [
                    'field' => 'value',
                ],
            ],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testMatchClosure()
    {
        $a = new Agr();
        $a->match(function ($w) {
            $w->where('field', 'value');
        });
        $expected = [
            [
                '$match' => [
                    'field' => 'value',
                ],
            ],
        ];

        $this->assertEquals($expected, $a->getPipeline());
    }

    public function testComputor()
    {
        $a = new Agr();
        $a->group(function ($g) {
            $g->addToSet('tags')
                ->first('first', 'favs')
                ->last('last', 'favs')
                ->max('max', 'scores')
                ->min('min', 'scores')
                ->push('names', 'name')
                ->by('rank');
        });

        $expected = [
            ['$group' => [
                'tags' => ['$addToSet' => '$tags'],
                'first' => ['$first' => '$favs'],
                'last' => ['$last' => '$favs'],
                'max' => ['$max' => '$scores'],
                'min' => ['$min' => '$scores'],
                'names' => ['$push' => '$name'],
                '_id' => '$rank',
            ]],
        ];
    }

    public function testSetPipeline()
    {
        $a = new Agr();
        $expected = ['pipeline'];
        $a->setPipeline($expected);
        $this->assertEquals($expected, $a->getPipeline());
    }
}
