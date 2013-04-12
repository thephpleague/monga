<?php

use League\Monga\Query\Aggregation as Agr;
use Mockery as m;

class QueryAggregateTests extends PHPUnit_Framework_TestCase
{
	public function testProject()
	{
		$a = new Agr();
		$a->project(array(
			'field' => 1,
			'other' => -1,
		));

		$expected = array(
			array('$project' => array(
				'field' => 1,
				'other' => -1,
			)),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testProjectClosure()
	{
		$a = new Agr();
		$a->project(function($p){
			$p->select('field');
			$p->exclude('other');
			$p->alias('actual', 'alias');
		});

		$expected = array(
			array('$project' => array(
				'field' => 1,
				'other' => -1,
				'alias' => '$actual',
			)),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testGroup()
	{
		$a = new Agr();
		$a->group(array(
			'num' => array('$sum' => '$num'),
		));

		$expected = array(
			array('$group' => array(
				'num' => array('$sum' => '$num'),
			)),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testGroupClosure()
	{
		$a = new Agr();
		$a->group(function($g){
			$g->sum('num');
		});

		$expected = array(
			array('$group' => array(
				'num' => array('$sum' => 1),
			)),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testUnwind()
	{
		$a = new Agr();
		$a->unwind('tags');
		$expected = array(
			array('$unwind' => '$tags'),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testSkip()
	{
		$a = new Agr();
		$a->skip(1);
		$expected = array(
			array('$skip' => 1),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testLimit()
	{
		$a = new Agr();
		$a->limit(1);
		$expected = array(
			array('$limit' => 1),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testPipe()
	{
		$a = new Agr();
		$a->pipe(array('$limit' => 1));
		$expected = array(
			array('$limit' => 1),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testMatch()
	{
		$a = new Agr();
		$a->match(array('field' => 'value'));
		$expected = array(
			array(
				'$match' => array(
					'field' => 'value',
				),
			),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testMatchClosure()
	{
		$a = new Agr();
		$a->match(function($w){
			$w->where('field', 'value');
		});
		$expected = array(
			array(
				'$match' => array(
					'field' => 'value',
				),
			),
		);

		$this->assertEquals($expected, $a->getPipeline());
	}

	public function testComputor()
	{
		$a = new Agr();
		$a->group(function($g){
			$g->addToSet('tags')
				->first('first', 'favs')
				->last('last', 'favs')
				->max('max', 'scores')
				->min('min', 'scores')
				->push('names', 'name')
				->by('rank');
		});

		$expected = array(
			array('$group' => array(
				'tags' => array('$addToSet' => '$tags'),
				'first' => array('$first' => '$favs'),
				'last' => array('$last' => '$favs'),
				'max' => array('$max' => '$scores'),
				'min' => array('$min' => '$scores'),
				'names' => array('$push' => '$name'),
				'_id' => '$rank',
			)),
		);
	}

	public function testSetPipeline()
	{
		$a = new Agr();
		$expected = array('pipeline');
		$a->setPipeline($expected);
		$this->assertEquals($expected, $a->getPipeline());
	}
}