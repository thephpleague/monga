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

class Projection extends Computer
{
	public function select($field)
	{
		$this->fields[$field] = 1;

		return $this;
	}

	public function exclude($field)
	{
		$this->fields[$field] = -1;

		return $this;
	}

	public function alias($field, $alias)
	{
		$this->fields[$alias] = $this->prepareField($field);

		return $this;
	}

	/**
	 * Retrieve the projection
	 *
	 * @return  array  projection array
	 */
	public function getProjection()
	{
		return $this->fields;
	}
}