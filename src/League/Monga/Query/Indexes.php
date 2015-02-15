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

namespace League\Monga\Query;

use MongoCollection;

class Indexes
{
    /**
     * Cnstructor, sets collection
     *
     * @param object MongoCollection
     */
    public function __construct(MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Return the collection
     *
     * @return object MongoCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Inject a collection
     *
     * @param  object MongoCollection
     *
     * @return object $this
     */
    public function setCollection(MongoCollection $collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Create an index.
     *
     * @param array $index   string fieldname or multi-key-index array
     * @param array $options index options
     *
     * @return object $this
     */
    public function create(array $index, $options = array())
    {
        // Convert expressive syntax
        $index = $this->prepareIndex($index);

        // Ensure the index
        $this->collection->ensureIndex($index, $options);

        return $this;
    }

    /**
     * Geospatial shortcut.
     *
     * @param string $field   field to use
     * @param array  $options index options
     *
     * @return object $this
     */
    public function geo($field, $options = array())
    {
        return $this->create(array($field => '2d'), $options);
    }

    /**
     * Prepate an index, allowing more expressive syntax.
     *
     * @param object $index index
     *
     * @return object $index prepared index
     */
    protected function prepareIndex($index)
    {
        foreach ($index as &$value) {
            // Convert ascending
            $value = $value === 'asc' ? 1 : $value;

            // Convert descending
            $value = $value === 'desc' ? -1 : $value;

            // Convert geo to 2d
            $value = $value === 'geo' ? '2d' : $value;
        }

        return $index;
    }

    /**
     * Drop one or more indexes
     *
     * @param mixed $index string index name or index
     *
     * @return object $this
     */
    public function drop($index)
    {
        $indexes = func_get_args();

        foreach ($indexes as $index) {
            $index = is_array($index) ? $this->prepareIndex($index) : $index;

            $this->collection->deleteIndex($index);
        }

        return $this;
    }

    /**
     * Drop all the indexes for the current collection.
     *
     * @return object $this
     */
    public function dropAll()
    {
        $this->collection->deleteIndexes();

        return $this;
    }
}
