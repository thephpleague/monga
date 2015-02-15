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

class Update extends Where
{
    /**
     * @var  bool  $upsert  whether to allow upserts
     */
    protected $upsert = false;

    /**
     * @var  bool  $multiple  whether to update multiple or only one
     */
    protected $multiple = true;

    /**
     * @var  bool  $atomic  whether to use atomic mode
     */
    protected $atomic = false;

    /**
     * @var  array  @update  update query
     */
    protected $update = array();

    /**
     * Returns the query options
     *
     * @return array query options
     */
    public function getOptions()
    {
        $options = parent::getOptions();

        $options['upsert'] = $this->upsert;
        $options['multiple'] = $this->multiple;

        return $options;
    }

    /**
     * Set the multiple option negatively.
     *
     * @param boolean $single whether to allow single updates
     *
     * @return object $this
     */
    public function single($single = true)
    {
        $this->multiple = ! $single;

        return $this;
    }

    /**
     * Set the multiple option.
     *
     * @param boolean $multiple whether to allow multiple updates
     *
     * @return object $this
     */
    public function multiple($multiple = true)
    {
        $this->multiple = (bool) $multiple;

        return $this;
    }

    /**
     * Set the multiple option.
     *
     * @param boolean $atomic whether to use atomic more
     *
     * @return object $this
     */
    public function atomic($atomic = true)
    {
        $this->atomic = (bool) $atomic;

        return $this;
    }

    /**
     * Set the upsert option.
     *
     * @param boolean $upsert whether to allow upserts
     *
     * @return object $this
     */
    public function upsert($upsert = true)
    {
        $this->upsert = (bool) $upsert;

        return $this;
    }

    /**
     * Sets the value of the insert.
     *
     * @param string $type  update modifier
     * @param string $field field to update
     * @param mixed  $value update value
     */
    protected function update($type, $field, $value)
    {
        $this->update[$field] = array($type, $value);

        return $this;
    }

    /**
     * Update the field from a document.
     *
     * @param string $field field name
     * @param string $value new value
     *
     * @return object $this
     */
    public function set($field, $value = null)
    {
        if (! is_array($field)) {
            $field = array($field => $value);
        }

        foreach ($field as $f => $v) {
            $this->update('$set', $f, $v);
        }

        return $this;
    }

    /**
     * Removes a field from a document.
     *
     * @param string $field field to remove
     *
     * @return object $this
     */
    public function remove($field)
    {
        if (! is_array($field)) {
            $field = func_get_args();
        }

        foreach ($field as $f) {
            $this->update('$unset', $f, 1);
        }

        return $this;
    }

    /**
     * Rename a field.
     *
     * @param string $field field name
     * @param string $to    new field name
     *
     * @return object $this
     */
    public function rename($field, $to)
    {
        return $this->update('$rename', $field, $to);
    }

    /**
     * Pushes a value onto a field array
     *
     * @param string $field field name
     * @param mixed  $value value to append to the array
     * @param bool   $all   whether to remove all (must be array)
     *
     * @return object $this
     */
    public function push($field, $value, $all = false)
    {
        return $this->update($all ? '$pushAll' : '$push', $field, $value);
    }

    /**
     * Pushes a value onto a field array
     *
     * @param string $field  field name
     * @param mixed  $values value to append to the array
     *
     * @return object $this
     */
    public function pushAll($field, array $values)
    {
        return $this->push($field, $values, true);
    }

    /**
     * Removes all matched instances from a field array
     *
     * @param string $field    field to unshift
     * @param string $value    values to remove
     * @param string $operator condition operator
     * @param bool   $all
     *
     * @return object $this
     */
    public function pull($field, $value, $operator = null, $all = false)
    {
        if ($operator) {
            $value = array($operator => $value);
        }

        return $this->update($all ? '$pullAll' : '$pull', $field, $value);
    }

    /**
     * Removes all matched instances from a field array
     *
     * @param string $field    field to unshift
     * @param string $value    values to remove
     * @param string $operator condition operator
     *
     * @return object $this
     */
    public function pullAll($field, $value, $operator = null)
    {
        return $this->pull($field, $value, $operator, true);
    }

    /**
     * Adds the values of an array to the set only
     * when the array doesn't contain them already
     *
     * @param string  $field field name
     * @param integer $value value to increment by
     *
     * @return object $this
     */
    public function addToSet($field, $value)
    {
        return $this->update('$addToSet', $field, (array) $value);
    }

    /**
     * Removes an item off the beginning of a field array.
     *
     * @param string $field field name
     *
     * @return object $this
     */
    public function unshift($field)
    {
        return $this->update('$pop', $field, -1);
    }

    /**
     * Removes an item off the ending of a field array.
     *
     * @param string $field field name
     *
     * @return object $this
     */
    public function pop($field)
    {
        return $this->update('$pop', $field, 1);
    }

    /**
     * Increments a field value.
     *
     * @param string  $field field to increment
     * @param integer $by    value to increment by
     *
     * @return object $this
     */
    public function increment($field, $by = 1)
    {
        return $this->update('$inc', $field, (int) $by);
    }

    /**
     * Returns the formatted update query.
     *
     * @return array update query
     */
    public function getUpdate()
    {
        $update = array();

        foreach ($this->update as $field => $data) {
            list($type, $value) = $data;

            if (! isset($update[$type])) {
                $update[$type] = array();
            }

            $update[$type][$field] = $value;
        }

        if ($this->atomic) {
            $update['$atomic'] = 1;
        }

        return $update;
    }
}
