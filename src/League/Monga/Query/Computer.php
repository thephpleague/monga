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

abstract class Computer
{
    /**
     * @var  array  $fields  fields array
     */
    protected $fields = array();

    /**
     * Aggregate a sum from a field.
     *
     * @param string $result result key
     * @param mixed  $field  field to take the sum from, 1 for totals
     *
     * @return object $this
     */
    public function sum($result, $field = 1)
    {
        $field = $this->prepareField($field);

        $this->fields[$result] = array('$sum' => $field);

        return $this;
    }

    /**
     * Aggregate a unique set of values.
     *
     * @param string $result result key
     * @param string $field  field to generate the set from
     *
     * @return object $this
     */
    public function addToSet($result, $field = null)
    {
        $field = $this->prepareField($field ?: $result);

        $this->fields[$result] = array('$addToSet' => $field);

        return $this;
    }

    /**
     * Aggregate the first value of a set.
     *
     * @param string $result result key
     * @param string $field  field to generate get the first from
     *
     * @return object $this
     */
    public function first($result, $field = null)
    {
        $field = $this->prepareField($field ?: $result);

        $this->fields[$result] = array('$first' => $field);

        return $this;
    }

    /**
     * Aggregate the last value of a set.
     *
     * @param string $result result key
     * @param string $field  field to generate get the last from
     *
     * @return object $this
     */
    public function last($result, $field = null)
    {
        $field = $this->prepareField($field ?: $result);

        $this->fields[$result] = array('$last' => $field);

        return $this;
    }

    /**
     * Aggregate the max value of a set.
     *
     * @param string $result result key
     * @param string $field  field to generate get the max from
     *
     * @return object $this
     */
    public function max($result, $field = null)
    {
        $field = $this->prepareField($field ?: $result);

        $this->fields[$result] = array('$max' => $field);

        return $this;
    }

    /**
     * Aggregate the min value of a set.
     *
     * @param string $result result key
     * @param string $field  field to generate get the min from
     *
     * @return object $this
     */
    public function min($result, $field = null)
    {
        $field = $this->prepareField($field ?: $result);

        $this->fields[$result] = array('$min' => $field);

        return $this;
    }

    /**
     * Aggregate the all the values of a given key.
     *
     * @param string $result result key
     * @param string $field  field to get all the values from
     *
     * @return object $this
     */
    public function push($result, $field = null)
    {
        $field = $this->prepareField($field ?: $result);

        $this->fields[$result] = array('$push' => $field);

        return $this;
    }

    /**
     * Prepends a dollar sign to field names
     *
     * @param string $field key name
     *
     * @return string prepared key fieldname
     */
    protected function prepareField($field)
    {
        if (is_string($field)) {
            $field = '$'.ltrim($field, '$');
        }

        return $field;
    }
}
