<?php
/**
 * Monga is a swift MongoDB Abstraction for PHP 5.4+
 *
 * @package    Monga
 * @version    1.0
 * @author     Frank de Jonge
 * @license    MIT License
 * @copyright  2011 - 2015 Frank de Jonge
 * @see        http://github.com/thephpleague/monga
 */

namespace League;

use League\Monga\Connection;
use MongoBinData;
use MongoCode;
use MongoConnectionException;
use MongoDate;
use MongoId;
use MongoRegex;

/**
 * Class Monga
 */
class Monga
{
    /**
     * Returns a MongoBinData object
     *
     * @param string $data data
     * @param int $type data type
     *
     * @return MongoBinData
     */
    public static function data($data, $type = null)
    {
        $type === null && $type = MongoBinData::BYTE_ARRAY;

        return new MongoBinData($data, $type);
    }

    /**
     * Create a MongoId object
     *
     * @param string $id id string
     *
     * @return MongoId
     */
    public static function id($id)
    {
        return new MongoId($id);
    }

    /**
     * Create a MongoCode object
     *
     * @param string $code javascript string
     * @param array $scope function scope
     *
     * @return MongoCode
     */
    public static function code($code, array $scope = [])
    {
        return new MongoCode($code, $scope);
    }

    /**
     * Create a MongoDate object
     *
     * @param int $sec timestamp
     * @param int $usec
     *
     * @return MongoDate
     */
    public static function date($sec = null, $usec = 0)
    {
        $sec === null && $sec = time();

        return new MongoDate($sec, $usec);
    }

    /**
     * Create MongoRegex object
     *
     * @param string $regex regex
     *
     * @return MongoRegex
     */
    public static function regex($regex)
    {
        return new MongoRegex($regex);
    }

    /**
     * Create a Monga\Connection object
     *
     * @param string $server server dns
     * @param array $options connection options
     * @param array $driverOptions [optional] driver options
     * @throws MongoConnectionException
     * @return Connection
     */
    public static function connection($server = null, array $options = [], array $driverOptions = [])
    {
        return new Connection($server, $options, $driverOptions);
    }
}
