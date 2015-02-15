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

class Remove extends Where
{
    /**
     * Whether to remove just one or multiple records.
     */
    protected $justOne = false;

    /**
     * Set the justOne option.
     *
     * @param boolean $single justOne option
     *
     * @return object $this
     */
    public function single($single = true)
    {
        $this->justOne = (bool) $single;

        return $this;
    }

    /**
     * Set the justOne option, reversed.
     *
     * @param boolean $multiple reversed justOne option
     *
     * @return object $this
     */
    public function multiple($multiple = true)
    {
        $this->justOne = ! $multiple;

        return $this;
    }

    /**
     * Retrieve query options.
     *
     * @return array query options.
     */
    public function getOptions()
    {
        $conditions = parent::getOptions();

        // Append remove specific options
        $conditions['justOne'] = $this->justOne;

        return $conditions;
    }
}
