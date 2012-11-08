<?php
/**
 * Monga is a swift MongoDB Abstraction for PHP 5.3+
 *
 * @package    Monga
 * @version    1.0
 * @author     Frank de Jonge
 * @license    MIT License
 * @copyright  2011 - 2012 Frank de Jonge
 * @link       http://github.com/FrenkyNet/Monga
 */

namespace Monga\Query;

class Find extends Where
{
	/**
	 *  @var  array  $orderBy  collection ordering
	 */
	protected $orderBy;

	/**
	 *  @var  int  $skip  query offset
	 */
	protected $skip;

	/**
	 *  @var  int  $limit  query limit
	 */
	protected $limit;

	/**
	 * @var  bool  $findOne  wether to find one, or more
	 */
	protected $findOne;

	/**
	 * @var  array  $select  fields include exclude array
	 */
	protected $select = array();

	/**
	 *  Orders a collection
	 *
	 *  @param  string field     field to order by
	 *  @param  string $direction asc/desc
	 *
	 *  @return object            current instance
	 */
	public function orderBy($field, $direction = 'asc')
	{
		$this->orderBy[$field] = $direction === 'asc' ? 1 : -1;

		return $this;
	}

	/**
	 *  Specifies fields to select
	 *
	 *  @param  string $field field to select
	 *
	 *  @return object        current instance
	 */
	public function select($field)
	{
		$fields = func_get_args();

		foreach ((array) $fields as $field)
		{
			$this->select[$field] = 1;
		}
	}

	/**
	 *  Specifies fields to exclude
	 *
	 *  @param  string $field fields to explude
	 *
	 *  @return object        current instance
	 */
	public function exclude($field)
	{
		$fields = func_get_args();

		foreach ($fields as $field)
		{
			$this->select[$field] = -1;
		}
	}

	/**
	 * Influence the select/exclude array in MongoDB fashion.
	 *
	 * @param    array  $fields  associative array to include/exclude fields.
	 */
	public function fields(array $fields)
	{
		$this->select = array_merge($this->select, $fields);

		return $this;
	}

	/**
	 *  Retrieve the select statement.
	 *
	 *  @return array|null array of fields to select or exlude
	 */
	public function getSelect()
	{
		return empty($this->select) ? null : $this->select;
	}

	/**
	 * Set the find type, one or many
	 *
	 * @param   bool   $one  true for one, false for many
	 * @return  object       current instance
	 */
	public function one($one = true)
	{
		$this->findOne = (bool) $one;

		return $this;
	}

	/**
	 *  Get the post-find actions.
	 *
	 *  @return  array  post-find actions
	 */
	public function getPostFindActions()
	{
		$actions = array();

		$this->orderBy and $actions[] = array('sort', $this->orderBy);
		$this->skip and $actions[] = array('skip', $this->skip);
		$this->limit and $actions[] = array('limit', $this->limit);

		return $actions;
	}

	/**
	 * Returns wether to find one, false for multiple
	 *
	 * @return  boolean  wether to find one
	 */
	public function getFindOne()
	{
		return (bool) $this->findOne;
	}
}