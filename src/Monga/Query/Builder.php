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

use Closure;
use Monga\Collection;
use MongoCursor;

abstract class Builder
{
	protected $collection;

	protected $safe = false;

	public function __construct($collection = null, $commands = null)
	{
		if ($collection instanceof Closure)
		{
			$commands = $collection;
			$collection = null;
		}

		$collection and $this->setCollection($collection);
	}

	public function safe($safe = true)
	{
		$this->safe = $safe;

		return this;
	}

	public function timeout($timeout)
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function fsync($fsync)
	{
		$this->fsync = $fsync;

		return $this;
	}

	public function setCollection(Collection $collection)
	{
		$this->collection = $collection;

		return $this;
	}

	public function getCollection()
	{
		return $this->collection;
	}

	public function execute($collection = null)
	{
		$collection and $this->setCollection($collection);

		if ( ! $this->collection)
		{
			throw new Exception('A query must contain a Collection object to be executed.');
		}

		$actions = $this->compile();
		$result = $this->collection->getCollection();

		foreach ($actions as $arguments)
		{
			// Retrieve the method name, this
			// will leave us the method params.
			$method = array_shift($arguments);

			//
			$result = call_user_func_array(array($result, $method), $arguments);
		}

		return $result;
	}

	public function getOptions()
	{
		return array(
			'safe' => $this->save,
			'fsync' => $this->fsync,
			'timeout' => $this->timeout ?: MongoCursor::$timeout,
		);
	}

	public function setOptions($options)
	{
		foreach ($options as $option => $value)
		{
			if (property_exists($this, $option))
			{
				$this->{$option} = $value;
			}
		}

		return $this;
	}
}