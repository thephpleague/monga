<?php
/**
 * Monga is a swift MongoDB Abstraction for PHP 5.3+
 *
 * @package    Monga
 * @version    1.0
 * @author     Frank de Jonge
 * @license    MIT License
 * @copyright  2011 - 2012 Frank de Jonge
 * @link       http://github.com/php-leop/monga
 */

namespace League\Monga\Query;

use MongoCursor;

abstract class Builder
{
    /**
     * @var  boolean  $safe  safe option
     */
    protected $safe = false;

    /**
     * @var  integer  $timeout  query timeout
     */
    protected $timeout;

    /**
     * @var  boolean  $fsync  fsync option
     */
    protected $fsync = false;

    /**
     * Set the safe option
     *
     * @param  boolean $safe safe mode
     * @return object  $this
     */
    public function safe($safe = true)
    {
        $this->safe = $safe;

        return $this;
    }

    /**
     * Set the timeout option
     *
     * @param  boolean $timeout query timeout
     * @return object  $this
     */
    public function timeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Set the fsync option
     *
     * @param  boolean $fsync fsync option
     * @return object  $this
     */
    public function fsync($fsync = true)
    {
        $this->fsync = $fsync;

        return $this;
    }

    /**
     * Retrieve the query options
     *
     * @return array query options
     */
    public function getOptions()
    {
        $w = 0;
        if ($this->safe) {
            $w = 1;
        }
        return array(
            'w' => $w,
            'fsync' => $this->fsync,
            'timeout' => $this->timeout ?: MongoCursor::$timeout,
        );
    }

    /**
     * Inject query options
     *
     * @param  array  $options query options
     * @return object $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }

        return $this;
    }
}
