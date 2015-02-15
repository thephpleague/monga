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

class Group extends Computer
{
    /**
     * Set the group field
     *
     * @param  mixed  group field or hash
     *
     * @return object $this
     */
    public function by($index)
    {
        $this->fields['_id'] = $this->prepareField($index);

        return $this;
    }

    /**
     * Return the group contents.
     *
     * @return array group statement
     */
    public function getGroup()
    {
        return $this->fields;
    }
}
