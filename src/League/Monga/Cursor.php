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

use MongoCursor;
use MongoDBRef;

class Cursor implements \Countable, \IteratorAggregate
{
    /**
     * @var  object  $result  MongoCursor
     */
    protected $result;

    /**
     * @var  object  $collection  Monga\Collection
     */
    protected $collection;

    /**
     * Constructor, sets the result and collection
     *
     * @param object $result     MongoCursor
     * @param object $collection Monga\Collection
     */
    public function __construct(MongoCursor $result, Collection $collection = null)
    {
        $this->result = $result;
        $collection && $this->collection = $collection;
    }

    /**
     * Retrieve the associated collection.
     *
     * @return object associated Collection instance
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Retrieve the MongoCursor instance
     *
     * @return object MongoCursor
     */
    public function getCursor()
    {
        return $this->result;
    }

    /**
     * Implementing IteratorAggregate
     *
     * @return object Mongoresult
     */
    public function getIterator()
    {
        return $this->result;
    }

    /**
     * Countable implementation
     *
     * @return int number of documents
     */
    public function count()
    {
        return $this->result->count(true);
    }

    /**
     * Returns the result as an array
     *
     * @return array the iterator as array
     */
    public function toArray()
    {
        return iterator_to_array($this->getIterator());
    }

    /**
     * Return the result content as MongoDBREf objects.
     *
     * @return array array of mongo references
     */
    public function toRefArray()
    {
        // Retrieve the actual objects.
        $documents = $this->toArray();

        // Get the collection idenfitier
        $collection = (string) $this->collection->getCollection();

        foreach ($documents as &$document) {
            $document = MongoDBRef::create($collection, $document);
        }

        return $documents;
    }

    /**
     * Original cursor method routing.
     *
     * @param string $method    method name
     * @param array  $arguments method arguments
     *
     * @return mixed method result
     */
    public function __call($method, $arguments)
    {
        if (! method_exists($this->result, $method)) {
            throw new \BadMethodCallException('Call to undefined function '.get_called_class().'::'.$method.'.');
        }

        // Trigger the method.
        $function = array($this->result, $method);
        $result = call_user_func_array($function, $arguments);

        // When the cursor is returned, return the current instance.
        // It has no use returning the cursor because the cursor
        // contained in this instance will already be affected.
        // Returning it's will cursor in an out-of-sync cursor
        // in this instance.
        if ($result instanceof MongoCursor) {
            return $this;
        }

        return $result;
    }
}
