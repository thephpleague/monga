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
use MongoDBRef;

class Cursor implements \Countable, \Iterator
{
	protected $cursor;

	protected $collection;

	public function __construct(MongoCursor $cursor, Collection $collection = null)
	{
		$this->cursor = $cursor;
		$collection and $this->collection = $collection;
	}

	/**
	 * Rettrieve the associated collection.
	 *
	 * @return  object  associated Collection instance
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 * COuntable implementation
	 *
	 * @return  int  number of documents
	 */
	public function count()
	{
		return $this->cursor->count();
	}

	/**
	 * Iterator implementation: current
	 *
	 * @return  array  current cursor item
	 */
	public function current()
	{
		return current($this->cursor);
	}

	/**
	 * Iterator implementation: key
	 *
	 * @return  mixed  current cursor key
	 */
	public function key()
	{
		return key($this->cursor);
	}

	/**
	 * Iterator implementation: next
	 *
	 * @return  void
	 */
	public function next()
	{
		next($this->cursor);
	}

	/**
	 * Iterator implementation: rewind
	 *
	 * @return  void
	 */
	public function rewind()
	{
		rewind($this->cursor);
	}

	/**
	 * Iterator implementation: valid
	 *
	 * @return  void
	 */
	public function valid()
	{
		return valid($this->cursor);
	}

	/**
	 * Returns the cursor as an array
	 *
	 * @return  array  the iterator as array
	 */
	public function toArray()
	{
		return iterator_to_array($this->cursor);
	}

	/**
	 * Return the cursor content as MongoDBREf objects.
	 *
	 * @return  array  array of mongo references
	 */
	public function toRefArray()
	{
		// Retrieve the actual objects.
		$documents = $this->asArray();

		// Get the collection idenfitief
		$collection = (string) $this->collection;

		foreach ($documents as &$document)
		{
			$document = MongoDBRef::create($collection, $document);
		}

		return $documents;
	}

	/**
	 * Original cursor method routing.
	 *
	 * @param   string  $method     method name
	 * @param   array   $arguments  method arguments
	 * @return  mixed   method result
	 */
	public function __call($method, $arguments)
	{
		if ( ! method_exists($this->cursor, $method))
		{
			throw new \BadMethodCallException('Call to undefined function '.get_called_class().'::'.$method.'.');
		}

		// Trigger the method.
		$function = array($this->cursor, $method);
		$result = call_user_func_array($function, $arguments);

		// When the cursor is returned, return the current instance.
		// It has no use returning the cursor because the cursor
		// contained in this instance will already be affected.
		// Returning it's will result in an out-of-sync cursor
		// in this instance.
		if ($result instanceof MongoCursor)
		{
			return $this;
		}

		return $result;
	}
}