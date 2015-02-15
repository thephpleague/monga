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

use MongoCode;
use MongoDB;
use MongoDBRef;

class Database
{
    /**
     * @var  object  $database MongoDB instance
     */
    protected $database;

    /**
     * @var  object  $connection  Connection instance
     */
    protected $connection;

    /**
     * Constructor, sets database and Connection.
     *
     * @param object $database   MongoDB connection object
     * @param object $connection Monga\Connection object, optional
     */
    public function __construct(MongoDB $database, Connection $connection = null)
    {
        $connection && $this->connection = $connection;

        $this->setDatabase($database);
    }

    /**
     * Retrieve all collection names
     *
     * @return array array of collection names
     */
    public function listCollections()
    {
        return $this->database->getCollectionNames();
    }

    /**
     * Check whether the database contains a given connection
     *
     * @param string $collection collection name
     *
     * @return boolean whether the collection exists in the database
     */
    public function hasCollection($collection)
    {
        return in_array($collection, $this->listCollections());
    }

    /**
     * Returns all collection.
     *
     * @param boolean $wrap whether to wrap the collection instances in Collection classes.
     *
     * @return array connections array
     */
    public function allCollections($wrap = false)
    {
        $collections = $this->database->listCollections();

        if (! $wrap) {
            return $collections;
        }

        return array_map(
            function ($collection) {
                return new Collection($collection);
            },
            $collections
        );
    }

    /**
     * Retrieve the connection.
     *
     * @return object Connection instance
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Connection injector
     *
     * @param Connection $connection connection object
     * @param object     $this
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Database injector
     *
     * @param mixed $database MongoDB instance, Database instance, string identifier.
     *
     * @return object $this
     */
    public function setDatabase($database)
    {
        if (! $database instanceof MongoDB) {
            $database = $this->connection->getConnection()->{$database};
        }

        $this->database = $database;

        return $this;
    }

    /**
     * Return the MongoDB database instance
     *
     * @return object MongoDB database instance
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Retrieve a collection
     *
     * @param string  $collection collection name
     * @param boolean $wrap       whether to wrap it in a Collection class.
     *
     * @return mixed Collection instance or MongoCollection instance.
     */
    public function collection($collection, $wrap = true)
    {
        $collection = $this->database->selectCollection($collection);
        $wrap && $collection = new Collection($collection);

        return $collection;
    }

    /**
     * Retrieve a GridFS object
     *
     * @param string  $prefix collection name
     * @param boolean $wrap   whether to wrap it in a Collection class.
     *
     * @return mixed Collection instance or MongoCollection instance.
     */
    public function filesystem($prefix = 'fs', $wrap = true)
    {
        $collection = $this->database->getGridFS($prefix);
        $wrap && $collection = new Filesystem($collection);

        return $collection;
    }

    /**
     * Drops the current database.
     *
     * @return boolean success boolean
     */
    public function drop()
    {
        $result = $this->database->drop();

        return $result === true || (bool) $result['ok'];
    }

    /**
     * Retrieve one or more references from the database.
     *
     * @param object|array $reference one or more references
     *
     * @return array one or more documents
     */
    public function getRef($reference)
    {
        $array = ! isset($reference['$ref']);
        $reference = $array ? $reference : array($reference);
        $database = $this->database;

        $result = array_map(
            function ($ref) use ($database) {
                return MongoDBRef::get($database, $ref);
            },
            $reference
        );

        return $array ? $result : reset($result);
    }

    /**
     * Execute javascript on the database
     *
     * @param mixed $code      MongoCode or javascript string
     * @param array $arguments function arguments
     *
     * @return mixed result
     */
    public function executeCode($code, array $arguments = array())
    {
        if (! ($code instanceof MongoCode)) {
            $code = new MongoCode($code);
        }

        return $this->database->execute($code, $arguments);
    }

    /**
     * Execute a command
     *
     * @param array $command command array
     * @param array $options command options
     * @param array result
     */
    public function command(array $command, array $options = array())
    {
        return $this->database->command($command, $options);
    }
}
