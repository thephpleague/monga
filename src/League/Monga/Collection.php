<?php
/**
 * Monga is a swift MongoDB Abstraction for PHP 5.4+
 *
 * @package    Monga
 * @version    1.0
 * @author     Frank de Jonge
 * @license    MIT License
 * @copyright  2011 - 2015 Frank de Jonge
 * @link       http://github.com/thephpleague/monga
 */

namespace League\Monga;

use Closure;
use MongoCollection;

class Collection
{
    /**
     * @var  object  MongoCollection instance
     */
    protected $collection;

    /**
     * @var  int  Amount of times to retry CRUD operations for "not master"
     */
    protected $maxRetries = 1;

    /**
     * Constructor, sets the MongoCollection instance.
     *
     * @param object $collection MongoCollection
     */
    public function __construct(MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get the raw collection object
     *
     * @return object MongoCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * MongoCollection injector
     *
     * @param object $collection MongoCollection
     *
     * @return object $this
     */
    public function setCollection(MongoCollection $collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Set the max amount of times to retry CRUD operations in the case of
     * "not master" exceptions.
     *
     * @param int $amount The amount of times
     *
     * @return object $this
     */
    public function setMaxRetries($amount)
    {
        $this->maxRetries = $amount;

        return $this;
    }

    /**
     * Drops the current collection.
     *
     * @return boolean success boolean
     */
    public function drop()
    {
        $result = $this->collection->drop();

        return $result === true || (bool) $result['ok'];
    }

    /**
     * Counts the given collection with an optional filter query
     *
     * @param array|closure $query count filter
     *
     * @return int number of documents
     */
    public function count($query = array())
    {
        if ($query instanceof Closure) {
            $callback = $query;
            $query = new Query\Where();
            $callback($query);
        }

        if ($query instanceof Query\Where) {
            $query = $query->getWhere();
        }

        if (! is_array($query)) {
            throw new \InvalidArgumentException('The count query should be an array.');
        }

        return $this->collection->count($query);
    }

    /**
     * Returns the distinct values for a given key.
     *
     * @param string $field field to use
     * @param mixed  $query match query
     *
     * @return array array of distinct values
     */
    public function distinct($key, $query = array())
    {
        if ($query instanceof Closure) {
            // Store the callback
            $callback = $query;

            // Create a new Where filter.
            $query = new Query\Where();

            // trigger callback
            $callback($query);
        }

        if ($query instanceof Query\Where) {
            // Get the filter.
            $query = $query->getWhere();
        }

        return $this->collection->distinct($key, $query);
    }

    /**
     * Aggregate a collection
     *
     * @param mixed $aggregation aggregaction pipeline of callback Closure
     *
     * @return array aggregation result
     */
    public function aggregate($aggregation = array())
    {
        if ($aggregation instanceof Closure) {
            // Store the callback
            $callback = $aggregation;

            // Create a new pipeline
            $aggregation = new Query\Aggregation();

            // Fire the callback
            $callback($aggregation);
        }

        if ($aggregation instanceof Query\Aggregation) {
            // Retrieve the pipeline
            $aggregation = $aggregation->getPipeline();
        }

        // Execute the aggregation.
        return $this->collection->aggregate($aggregation);
    }

    /**
     * Truncates the table.
     *
     * @return bool success boolean
     */
    public function truncate()
    {
        $result = $this->collection->remove(array());

        return $result === true || (bool) $result['ok'];
    }

    /**
     * Manipulate collection indexes
     *
     * @param Closure $callback callback
     *
     * @return object $this
     */
    public function indexes(Closure $callback)
    {
        $indexes = new Query\Indexes($this->collection);
        $callback($indexes);

        return $this;
    }

    /**
     * Retrieve the collection indexes
     *
     * @return array collection indexes
     */
    public function listIndexes()
    {
        return $this->collection->getIndexInfo();
    }

    /**
     * Removes documents from the current collection
     *
     * @param array|Closure $criteria remove filter
     * @param array         $options  remove options
     *
     * @return mixed false on failure, number of deleted items on success
     */
    public function remove($criteria, $options = array())
    {
        if ($criteria instanceof Closure) {
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

        if (! is_array($criteria)) {
            throw new \InvalidArgumentException('Remove criteria must be an array.');
        }

        $maxRetries = $this->maxRetries;
        $tries = 0;

        do {
            try {
                $result = $this->collection->remove($criteria, $options);
                break;
            } catch (MongoCursorException $e) {
                // Retry "save" in case of rediscovery latency issues
                // in replica set failover. Error codes 10107, 13435, and 10058
                // are MongoCursorException's "not master" errors.
                if (in_array($e->getCode(), array(10107, 13435, 10058))) {
                    if ($tries === $maxRetries) {
                        throw $e;
                    } else {
                        $tries++;
                        continue;
                    }
                } else {
                    throw $e;
                }
            }
        } while ($tries <= $maxRetries);

        return $result === true || (bool) $result['ok'];
    }

    /**
     * Finds documents.
     *
     * @param mixed   $query   configuration closure, raw mongo conditions array
     * @param array   $fields  associative array for field exclusion/inclusion
     * @param boolean $findOne whether to find one or multiple
     *
     * @return mixed result Cursor for multiple, document array for one.
     */
    public function find($query = array(), $fields = array(), $findOne = false)
    {
        $postFind = false;

        if ($query instanceof Closure) {
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

        if (! is_array($query) || ! is_array($fields)) {
            throw new \InvalidArgumentException('Find params $query and $fields must be arrays.');
        }

        // Prepare the find arguments
        $arguments = array();
        empty($query) || $arguments[] = $query;
        empty($fields) || $arguments[] = $fields;

        // Wrap the find function so it is callable
        $function = array(
            $this->getCollection(),
            ($findOne && ! $postFind) ? 'findOne' : 'find',
        );

        $result = call_user_func_array($function, $arguments);

        // Trigger any post find actions.
        if ($postFind) {
            foreach ($postFind as $arguments) {
                $method = array_shift($arguments);

                $result = call_user_func_array(array($result, $method), $arguments);
            }
        }

        // When there were post-find actions, we used normal find
        // so we can sort, skip, and limit. Now we'll need to retrieve
        // the first result.
        if ($findOne && $postFind) {
            $result = $result->getNext();
        }

        return $findOne ? $result : new Cursor($result, $this);
    }

    /**
     * Finds a single documents.
     *
     * @param mixed $query  configuration closure, raw mongo conditions array
     * @param array $fields associative array for field exclusion/inclusion
     *
     * @return array|null document array when found, null when not found
     */
    public function findOne($query = array(), $fields = array())
    {
        return $this->find($query, $fields, true);
    }

    /**
     * Inserts one or multiple documents.
     *
     * @param array $data    documents or array of documents
     * @param array $options insert options
     *
     * @return boolean success boolean
     */
    public function insert(array $data, $options = array())
    {
        $maxRetries = $this->maxRetries;
        $tries = 0;

        // Check whether we're dealing with a batch insert.
        if (isset($data[0]) && is_array($data[0])) {
            // Insert using batchInsert
            do {
                try {
                    $result = $this->collection->batchInsert($data, $options);
                    break;
                } catch (MongoCursorException $e) {
                    // Retry "save" in case of rediscovery latency issues
                    // in replica set failover. Error codes 10107, 13435, and 10058
                    // are MongoCursorException's "not master" errors.
                    if (in_array($e->getCode(), array(10107, 13435, 10058))) {
                        if ($tries === $maxRetries) {
                            throw $e;
                        } else {
                            $tries++;
                            continue;
                        }
                    } else {
                        throw $e;
                    }
                }
            } while ($tries <= $maxRetries);

            if (! $result || ! ($result === true || (bool) $result['ok'])) {
                return false;
            }

            $result = array();

            foreach ($data as $r) {
                // Collect all the id's for the return value
                $result[] = $r['_id'];
            }

            // Return all inserted id's.
            return $result;
        }

        do {
            try {
                $result = $this->collection->insert($data, $options);
                break;
            } catch (MongoCursorException $e) {
                // Retry "save" in case of rediscovery latency issues
                // in replica set failover. Error codes 10107, 13435, and 10058
                // are MongoCursorException's "not master" errors.
                if (in_array($e->getCode(), array(10107, 13435, 10058))) {
                    if ($tries === $maxRetries) {
                        throw $e;
                    } else {
                        $tries++;
                        continue;
                    }
                } else {
                    throw $e;
                }
            }
        } while ($tries <= $maxRetries);

        if ($result === true || (bool) $result['ok']) {
            return $data['_id'];
        }

        return false;
    }

    /**
     * Updates a collection
     *
     * @param mixed $values  update array or callback
     * @param mixed $query   update filter
     * @param array $options update options
     *
     * @return boolean query success
     */
    public function update($values = array(), $query = null, $options = array())
    {
        if ($values instanceof Closure) {
            $query = new Query\Update();
            $query->setOptions($options);
            $values($query);

            $options = $query->getOptions();
            $values = $query->getUpdate();
            $query = $query->getWhere();
        }

        if (! is_array($values) || ! is_array($options)) {
            throw new \InvalidArgumentException('Update params $update and $options must be arrays.');
        }

        isset($query) || $query = array();

        $maxRetries = $this->maxRetries;
        $tries = 0;

        do {
            try {
                $result = $this->collection->update($query, $values, $options);
                break;
            } catch (MongoCursorException $e) {
                // Retry "save" in case of rediscovery latency issues
                // in replica set failover. Error codes 10107, 13435, and 10058
                // are MongoCursorException's "not master" errors.
                if (in_array($e->getCode(), array(10107, 13435, 10058))) {
                    if ($tries === $maxRetries) {
                        throw $e;
                    } else {
                        $tries++;
                        continue;
                    }
                } else {
                    throw $e;
                }
            }
        } while ($tries <= $maxRetries);

        return $result === true || (bool) $result['ok'];
    }

    /**
     * Saves a documents.
     *
     * @param array $document document
     * @param array $options  save options
     *
     * @return boolean success boolean
     */
    public function save(&$document, $options = array())
    {
        $maxRetries = $this->maxRetries;
        $tries = 0;

        do {
            try {
                $result = $this->collection->save($document, $options);
                break;
            } catch (MongoCursorException $e) {
                // Retry "save" in case of rediscovery latency issues
                // in replica set failover. Error codes 10107, 13435, and 10058
                // are MongoCursorException's "not master" errors.
                if (in_array($e->getCode(), array(10107, 13435, 10058))) {
                    if ($tries === $maxRetries) {
                        throw $e;
                    } else {
                        $tries++;
                        continue;
                    }
                } else {
                    throw $e;
                }
            }
        } while ($tries <= $maxRetries);

        return $result === true || (bool) $result['ok'];
    }
}
