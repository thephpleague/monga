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

namespace Monga;

use MongoCursor;

class Cursor
{
	protected $cursor;

	public function __construct($cursor)
	{
		$this->cursor = $cursor;
	}

	public function toArray()
	{
		return iterator_to_array($this->cursor);
	}

	public function toJSON()
	{
		return json_encode($this->toArray());
	}

	public function __call($method, $arguments)
	{
		if (method_exists($this->cursor, $method))
		{
			$result = call_user_func_array(array($this->cursor, $method), $arguments);

			if ($return instanceof MongoCursor)
			{
				return $this;
			}

			return $result;
		}

		throw new \BadMethodCallException('Trying to call undefined method MongoCursor::'.$method);
	}
}