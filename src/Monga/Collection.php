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

use MongoCode;
use MongoCollection;
use Closure;

class Collection
{
	/**
	 * @var  object  MongoCollection instance
	 */
	protected $collection;

	/**
	 * Constructor, sets the MongoCollection instance.
	 *
	 * @param  object  $collection  MongoCollection
	 */
	public function __construct(MongoCollection $collection)
	{
		$this->collection = $collection;
	}

	/**
	 * Get the raw collection object
	 *
	 * @return  object  MongoCollection
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 * MongoCollection injector
	 *
	 * @param   object  $collection  MongoCollection
	 * @return  object  $this
	 */
	public function setCollection(MongoCollection $collection)
	{
		$this->collection = $collection;

		return $this;
	}

	/**
	 * Drops the current collection.
	 *
	 * @return  boolean  success boolean
	 */
	public function drop()
	{
		$result = $this->collection->drop();

		return $result === true or (bool) $result['ok'];
	}

	/**
	 * Counts the given collection with an optional filter query
	 *
	 * @param   array|closre  $query  count filter
	 * @return  int                   number of documents
	 */
	public function count($query = array())
	{
		if ($query instanceof Closure)
		{
			$query = new Query\Where($this, $query);
		}

		if ($query instanceof Query\Where)
		{
			$query = $query->getWhere();
		}

		if ( ! is_array($query))
		{
			throw new \InvalidArgumentException('The count query should be an array.');
		}

		return $this->collection->count($query);
	}

	/**
	 * Truncates the table.
	 *
	 * @return  bool  success boolean
	 */
	public function truncate()
	{
		$result = $this->collection->remove(array());

		return $result === true or (bool) $result['ok'];
	}

	/**
	 * Removes documents from the current collection
	 *
	 * @param   array|Closure  $criteria  remove filter
	 * @param   array          $options   remove options
	 * @return  mixed          false on failure, number of deleted items on success
	 */
	public function remove($criteria, $options = array())
	{
		if ($criteria instanceof Closure)
		{
			// Create new Remove query
			$query = new Query\Remove();

			// Set the given options
			$query->setOptions($options);

			// Execute the callback
			$criteria($query);

			// Retrieve the where filter
			$criteria = $query->getWhere();

			// Retrieve the options, these might
			// have been altered in the closure.
			$options = $query->getOptions();
		}

		if ( ! is_array($criteria))
		{
			throw new \InvalidArgumentException('Remove criteria must be an array.');
		}

		$result = $this->collection->remove($criteria, $options);

		return $result === true or (bool) $result['ok'];
	}

	/**
	 * Finds documents.
	 *
	 * @param   mixed    $query    configuration closure, raw mongo conditions array
	 * @param   array    $fields   associative array for field exclusion/inclusion
	 * @param   boolean  $findOne  wether to find one or multiple
	 * @return  mixed              result Cursor for multiple, document array for one.
	 */
	public function find($query = array(), $fields = array(), $findOne = false)
	{
		$postFind = false;

		if ($query instanceof Closure)
		{
			$find = new Query\Find();

			// set the fields to select
			$find->fields($fields);
			$find->one($findOne);
			$query($find);

			$findOne = $find->getFindOne();
			$fields = $find->getFields();
			$query = $find->getWhere();
			$postFind = $find->getPostFindActions();
		}

		if ( ! is_array($query) or ! is_array($fields))
		{
			throw new \InvalidArgumentException('Find params $query and $fields must be arrays.');
		}

		// Prepare the find arguments
		$arguments = array();
		empty($query) or $arguments[] = $query;
		empty($fields) or $arguments[] = $fields;

		// Wrap the find function so it is callable
		$function = array(
			$this->getCollection(),
			($findOne and ! $postFind) ? 'findOne' : 'find',
		);

		$result = call_user_func_array($function, $arguments);

		// Trigger any post find actions.
		if ($postFind)
		{
			foreach ($postFind as $arguments)
			{
				$method = array_shift($arguments);

				$result = call_user_func_array(array($result, $method), $arguments);
			}
		}

		// When there were post-find actions, we used normal find
		// so we can sort, skip, and limit. Now we'll need to retrieve
		// the first result.
		if ($findOne and $postFind)
		{
			$result = $result->getNext();
		}

		return $findOne ? $result : new Cursor($result, $this);
	}

	/**
	 * Finds a single documents.
	 *
	 * @param   mixed       $query    configuration closure, raw mongo conditions array
	 * @param   array       $fields   associative array for field exclusion/inclusion
	 * @return  array|null            document array when found, null when not found
	 */
	public function findOne($query = array(), $fields = array())
	{
		return $this->find($query, $fields, true);
	}

	/**
	 * Inserts one or multiple documents.
	 *
	 * @param   array    $data     documents or array of documents
	 * @param   array    $options  insert options
	 * @return  boolean             success boolean
	 */
	public function insert(array $data, $options = array())
	{
		// Check wether we're dealing with a batch insert.
		if (isset($data[0]) and is_array($data[0]))
		{
			// Insert using batchInsert
			$result = $this->collection->batchInsert($data, $options);

			if ( ! $result or ! ($result === true or (bool) $result['ok']))
			{
				return false;
			}

			$result = array();

			foreach($data as $r)
			{
				// Collect all the id's for the return value
				$result[] = $r['_id'];
			}

			// Return all inserted id's.
			return $result;
		}

		$result = $this->collection->insert($data, $options);

		if ($result === true or (bool) $result['ok'])
		{
			return $data['_id'];
		}

		return false;
	}

	/**
	 * Updates a collection
	 *
	 * @param   mixed    $values   update array or callback
	 * @param   mixed    $query    update filter
	 * @param   array    $options  update options
	 * @return  boolean            query success
	 */
	public function update($values = array(), $query = null, $options = array())
	{
		if ($values instanceof CLosure)
		{
			$query = new Query\Update();
			$query->setOptions($options);
			$values($query);

			$options = $query->getOptions();
			$values = $query->getUpdate();
			$query = $query->getWhere();
		}

		if ( ! is_array($values) or ! is_array($options))
		{
			throw new \InvalidArgumentException('Update params $update and $options must be arrays.');
		}

		isset($query) or $query = array();

		var_dump($query);
		var_dump($values);
		var_dump($options);

		$result = $this->collection->update($query, $values, $options);

		var_dump($result);

		return $result === true or !! $result['ok'];
	}

	/**
	 * Saves a documents.
	 *
	 * @param   array    $document  document
	 * @param   array    $options   save options
	 * @return  boolean             success boolean
	 */
	public function save(&$document, $options = array())
	{
		$result = $this->collection->save($document, $options);

		return $result === true or (bool) $result['ok'];
	}
}