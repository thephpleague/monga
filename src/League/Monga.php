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

namespace League;

use League\Monga\Connection;
use MongoBinData;
use MongoCode;
use MongoDate;
use MongoDB;
use MongoId;
use MongoRegex;

class Monga
{
    /**
     * Returns a MongoBinData object
     *
     * @param string $data data
     * @param int    $type data type
     *
     * @return object MongoBinData
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
     * @return object MongoId
     */
    public static function id($id)
    {
        return new MongoId($id);
    }

    /**
     * Create a MongoCode object
     *
     * @param string $code  javascript string
     * @param array  $scope function scope
     *
     * @return object MongoCode
     */
    public static function code($code, $scope = array())
    {
        return new MongoCode($code, $scope);
    }

    /**
     * Create a MongoDate object
     *
     * @param int $sec  timestamp
     * @param int $usec
     *
     * @return object MongoDate
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
     * @return object MongoRegex
     */
    public static function regex($regex)
    {
        return new MongoRegex($regex);
    }

    /**
     * Create a Monga\Connection object
     *
     * @param string $server  server dns
     * @param array  $options connection options
     *
     * @return object Monga\Connection
     */
    public static function connection($server = null, array $options = array())
    {
        return new Connection($server, $options);
    }
}
