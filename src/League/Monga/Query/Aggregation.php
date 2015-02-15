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

use Closure;

class Aggregation
{
    /**
     * @var  array  $pipeline  aggregation pipeline
     */
    protected $pipeline = array();

    /**
     * Project the results.
     *
     * @param array $projection projection
     *
     * @return object $this
     */
    public function project($projection)
    {
        if ($projection instanceof Closure) {
            $callback = $projection;
            $projection = new Projection();
            $callback($projection);
        }

        if ($projection instanceof Projection) {
            $projection = $projection->getProjection();
        }

        $this->pipeline[] = array('$project' => $projection);

        return $this;
    }

    /**
     * Group the results.
     *
     * @param mixed $projection projection array / closure / Query\Group instance
     *
     * @return object $this
     */
    public function group($group)
    {
        if ($group instanceof Closure) {
            $callback = $group;
            $group = new Group();
            is_callable('Closure::bind') && $callback = $callback->bindTo($group, $group);
            $callback($group);
        }

        if ($group instanceof Group) {
            $group = $group->getGroup();
        }

        $this->pipeline[] = array('$group' => $group);

        return $this;
    }

    /**
     * Add a limit operation to the pipeline
     *
     * @param string $field field to unwind
     *
     * @return object $this
     */
    public function unwind($field)
    {
        $this->pipeline[] = array('$unwind' => '$'.ltrim($field, '$'));

        return $this;
    }

    /**
     * Add a skip operation to the pipeline
     *
     * @param int $amount amount to skip
     *
     * @return object $this
     */
    public function skip($amount)
    {
        $this->pipeline[] = array('$skip' => (int) $amount);

        return $this;
    }

    /**
     * Add a limit operation to the pipeline
     *
     * @param int $amount limit
     *
     * @return object $this
     */
    public function limit($amount)
    {
        $this->pipeline[] = array('$limit' => (int) $amount);

        return $this;
    }

    /**
     * Add an operation to the pipeline
     *
     * @param array $operation operation
     *
     * @return object $this
     */
    public function pipe(array $operation)
    {
        $this->pipeline[] = $operation;

        return $this;
    }

    /**
     * Add a match operation to the pipeline
     *
     * @param mixed $filter filter array / filter callback / Query\Where instance
     *
     * @return object $this
     */
    public function match($filter)
    {
        if ($filter instanceof Closure) {
            // Store the callback
            $callback = $filter;

            // Set a new Where filter
            $filter = new Where();

            // Execute the callback
            $callback($filter);
        }

        if ($filter instanceof Where) {
            $filter = $filter->getWhere();
        }

        $this->pipeline[] = array('$match' => $filter);

        return $this;
    }

    /**
     * Retrieve the pipeline
     *
     * @return array pipeline
     */
    public function getPipeline()
    {
        return $this->pipeline;
    }

    /**
     * Inject the aggregation pipeline.
     *
     * @param array $pipeline pipeline
     *
     * @return object $this
     */
    public function setPipeline(array $pipeline)
    {
        $this->pipeline = $pipeline;

        return $this;
    }
}
