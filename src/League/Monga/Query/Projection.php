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

class Projection extends Computer
{
    /**
     * Specifies a field to be included
     *
     * @param string $field The field to include
     *
     * @return object $this
     */
    public function select($field)
    {
        $this->fields[$field] = 1;

        return $this;
    }

    /**
     * Specifies a field to be suppressed
     *
     * @param string $field The field to suppress
     *
     * @return object $this
     */
    public function exclude($field)
    {
        $this->fields[$field] = -1;

        return $this;
    }

    /**
     * Sets an alias for a field.
     *
     * @param string $field The field's name
     * @param string $alias The field's alias
     *
     * @return object $this
     */
    public function alias($field, $alias)
    {
        $this->fields[$alias] = $this->prepareField($field);

        return $this;
    }

    /**
     * Retrieve the projection
     *
     * @return array projection array
     */
    public function getProjection()
    {
        return $this->fields;
    }
}
