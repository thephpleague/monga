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

use MongoClient;

class Connection
{
    /**
     * @var  Mongo  $connection  MongoDB Connection instance
     */
    protected $connection;

    /**
     * @var boolean $connected If there is a current connection
     */
    protected $connected = false;

    /**
     * Establishes a MongoDB connection
     *
     * @param string $server  mongo dns
     * @param array  $options connection options
     */
    public function __construct($server = null, array $options = array())
    {
        if ($server instanceof MongoClient) {
            $this->connection = $server;
        } else {
            if (is_array($server)) {
                $options = $server;
                $server = null;
            }

            // Mimic the default mongo connect settings.
            if (! isset($options['connect'])) {
                $options['connect'] = true;
            }

            $this->connection = new MongoClient($server ?: 'mongodb://localhost:27017', $options);
        }
    }

    /**
     * Connection injector
     *
     * @param object $connection MongoClient instance
     *
     * @return object $this
     */
    public function setConnection(MongoClient $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Retrieve the MongoConnection.
     *
     * @return object Mongo instance
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Connect to the database.
     *
     * @return boolean connection result
     */
    public function connect()
    {
        if ($this->connection->connect()) {
            $this->connected = true;

            return true;
        }

        return false;
    }

    /**
     * Disconnect from a mongo database.
     *
     * @return boolean disconnect result
     */
    public function disconnect()
    {
        if ($this->connection->close()) {
            $this->connected = false;

            return true;
        }

        return false;
    }

    /**
     * Returns whether the connection is connection.
     *
     * @return bool whether there is a connection
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Drops a database.
     *
     * @param string $database database name
     *
     * @return boolean whether the database was dropped successfully
     */
    public function dropDatabase($database)
    {
        $result = $this->connection->{$database}->command(array('dropDatabase' => 1));

        return (bool) $result['ok'];
    }

    /**
     * Retrieve a database object from a connection
     *
     * @param string  $database database name
     * @param boolean $wrap     whether to wrap in a Database object
     *
     * @return object MongoDB or Monga\Database instance
     */
    public function database($database, $wrap = true)
    {
        $database = $this->connection->{$database};

        return $wrap ? new Database($database, $this) : $database;
    }

    /**
     * Returns whether a database exists.
     *
     * @param boolean $name database name
     *
     * @return boolean whether the database exists
     */
    public function hasDatabase($name)
    {
        return in_array($name, $this->listDatabases(false));
    }

    /**
     * Returns a list of databases.
     *
     * @param boolean $detailed return detailed information
     *
     * @return array array containing database name or info arrays
     */
    public function listDatabases($detailed = false)
    {
        $result = $this->connection->listDBs();

        if ($detailed) {
            return $result;
        }

        return array_map(
            function ($database) {
                return $database['name'];
            },
            $result['databases']
        );
    }
}
