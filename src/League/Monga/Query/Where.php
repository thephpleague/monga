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
use MongoId;
use MongoRegex;

class Where extends Builder
{
    /**
     * @var  array  $where  query conditions
     */
    protected $where;

    /**
     * Replaces the where statement. The statement
     * will be reformatted to match, allowing further
     * chaining by Monga.
     *
     * @param array $where An array of where conditions
     *
     * @return object $this
     */
    public function setWhere(array $where)
    {
        // When the statement is empty set the
        // where property to null to prevent
        // empty statement errors.
        if (empty($where)) {
            // delete the current where statement
            $this->where = null;

            // Skip further execution
            return $this;
        }

        // Ensure the base $or clause
        if (! isset($where['$or'])) {
            // wrap the statement in an $or clause
            $where = array('$or' => array($where));
        }

        // Fetch the last clause in the $or array
        // to allow further chaining it's required
        // to be wrapped in an @and statement.
        $lastClause = array_pop($where['$or']);

        if (! isset($lastClause['$and'])) {
            // Wrap in an @and statement
            $lastClause = array('$and' => array($lastClause));
        }

        // Re-append the $and clause
        $where['$or'][] = $lastClause;

        // Replace the base where statement
        $this->where = $where;

        return $this;
    }

    /**
     * Internal where statement formatter
     *
     * @param string $type      chain type
     * @param string $field     fieldname
     * @param mixed  $statement filter statement
     *
     * @return object $this
     */
    protected function formatWhere($type, $field, $statement = null)
    {
        if (is_array($field)) {
            foreach ($field as $_field => $_statement) {
                $this->formatWhere($type, $_field, $_statement);
            }

            return $this;
        }

        $isNested = false;

        // Closures represent nested where clauses.
        if ($field instanceof Closure) {
            // Create a new query.
            $nested = new static();

            // Fire the contents of the closure
            // on the newly created object
            $field($nested);

            // Compile the query
            $statement = $nested->getWhere();

            if (empty($statement)) {
                return $this;
            }

            // set the $field to $type so we can detect it later on
            $field = $type;

            // set the query as nested
            $isNested = true;
        }

        // Ensure the master $or clause
        $this->where || $this->where = array('$or' => array());

        // Because $or is the base for every query
        // $or queries can always be appended to the
        // main stack.
        if ($type === '$or') {
            if ($isNested) {
                $this->where['$or'][] = array('$and' => array($statement));
            } else {
                $this->where['$or'][] = array('$and' => array(array($field => $statement)));
            }

            return $this;
        }

        // Retrieve the last $or clause or create a new one
        // when none available.
        $lastOrClause = array_pop($this->where['$or']) ?: array('$and' => array());

        // Retrieve the last $and clause from the $or clause
        // or create a new one when none available.
        $lastAndClause = array_pop($lastOrClause['$and']) ?: array();

        // Handle the result from nested where statements
        if ($isNested) {
            // Re-append the last $and clause
            empty($lastAndClause) || $lastOrClause['$and'][] = $lastAndClause;

            // The nested query has a base key (either $and or $or)
            // So following queries will always begin in a new clause
            $lastAndClause = $statement;
        } else {
            if (! isset($lastAndClause['$and'])) {
                if (! isset($lastAndClause[$field])) {
                    $lastAndClause[$field] = $statement;
                } elseif ($field === '$nor') {
                    $lastAndClause['$nor'] = array_merge($lastAndClause['$nor'], $statement);
                } else {
                    $lastAndClause = array(
                        '$and' => array(
                            $lastAndClause,
                            array($field => $statement),
                        ),
                    );
                }
            } else {
                $lastSubAnd = array_pop($lastAndClause['$and']) ?: array();

                if (isset($lastSubAnd['$and']) || isset($lastSubAnd['$or']) || isset($lastSubAnd[$field])) {
                    empty($lastSubAnd) || $lastAndClause['$and'][] = $lastSubAnd;
                    $lastSubAnd = array();
                }

                $lastSubAnd[$field] = $statement;

                $lastAndClause['$and'][] = $lastSubAnd;
            }
        }

        // Re-append the last $and clause
        $lastOrClause['$and'][] = $lastAndClause;

        // Re-append the last $or clause
        $this->where['$or'][] = $lastOrClause;

        return $this;
    }

    /**
     * Appends a where statement.
     *
     * @param mixed $field field name or nested query closure
     * @param mixed $value filter value
     *
     * @return object $this
     */
    public function where($field, $value = null)
    {
        return $this->formatWhere('$and', $field, $value);
    }

    /**
     * Appends a where statement.
     *
     * @param mixed $field field name or nested query closure
     * @param mixed $value filter value
     *
     * @return object $this
     */
    public function andWhere($field, $value = null)
    {
        return $this->formatWhere('$and', $field, $value);
    }

    /**
     * Appends a or-where statement.
     *
     * @param mixed $field field name or nested query closure
     * @param mixed $value filter value
     *
     * @return object $this
     */
    public function orWhere($field, $value = null)
    {
        if ($field instanceof Closure) {
            return $this->formatWhere('$or', $field);
        }

        return $this->formatWhere('$or', $field, $value);
    }

    /**
     * Appends an and-where-not statement.
     *
     * @param string $field field name
     * @param mixed  $value filter value
     *
     * @return object $this
     */
    public function whereNot($field, $value)
    {
        return $this->formatWhere('$and', $field, array('$ne' => $value));
    }

    /**
     * Appends a or-where-not statement.
     *
     * @param string $field field name
     * @param mixed  $value filter value
     *
     * @return object $this
     */
    public function orWhereNot($field, $value)
    {
        return $this->formatWhere('$or', $field, array('$ne' => $value));
    }

    /**
     * Appends an and-where-not statement.
     *
     * @param string $field field name
     * @param mixed  $value filter value
     *
     * @return object $this
     */
    public function andWhereNot($field, $value)
    {
        return call_user_func_array(array($this, 'whereNot'), func_get_args());
    }

    /**
     * Appends an and-where-regex statement defined in sql syntax.
     *
     * @param string $field     field name
     * @param mixed  $value     filter value
     * @param string $flags     regex flags
     * @param string $delimiter preg_quote delimiter
     * @param string $type      chain type
     *
     * @return object $this
     */
    public function whereLike($field, $value, $flags = 'imxsu', $delimiter = '/', $type = '$and')
    {
        $value = preg_quote($value, '/');

        if (preg_match('#^%(.*)$#isU', $value)) {
            $value = substr($value, 1);
        } else {
            $value = '^'.$value;
        }

        if (preg_match('/^(.*)%$/iU', $value)) {
            $value = substr($value, 0, -1);
        } else {
            $value = $value.'$';
        }

        return $this->formatWhere($type, $field, new MongoRegex('/'.$value.'/'.$flags));
    }

    /**
     * Appends a or-where-regex statement defined in sql syntax.
     *
     * @param string $field     field name
     * @param mixed  $value     filter value
     * @param string $flags     regex flags
     * @param string $delimiter preg_quote delimiter
     *
     * @return object $this
     */
    public function orWhereLike($field, $value, $flags = 'imxsu', $delimiter = null)
    {
        return $this->whereLike($field, $value, $flags, $delimiter, '$or');
    }

    /**
     * Appends an and-where-regex statement defined in sql syntax.
     *
     * @param string $field     field name
     * @param mixed  $value     filter value
     * @param string $flags     regex flags
     * @param string $delimiter preg_quote delimiter
     *
     * @return object $this
     */
    public function andWhereLike($field, $value, $flags = 'imxsu', $delimiter = null)
    {
        return $this->whereLike($field, $value, $flags, $delimiter, '$and');
    }

    /**
     * Appends an and-where-regex statement
     *
     * @param string $field field name
     * @param mixed  $value filter value
     *
     * @return object $this
     */
    public function whereRegex($field, $value)
    {
        if (! $value instanceof MongoRegex) {
            $value = new MongoRegex($value);
        }

        return $this->formatWhere('$and', $field, $value);
    }

    /**
     * Appends an and-where-regex statement
     *
     * @param string $field fieldname
     * @param mixed  $value filter value
     *
     * @return object $this
     */
    public function andWhereRegex($field, $value)
    {
        return call_user_func_array(array($this, 'whereRegex'), array($field, $value));
    }

    /**
     * Appends a or-where-regex statement
     *
     * @param string $field field name
     * @param mixed  $value filter value
     *
     * @return object $this
     */
    public function orWhereRegex($field, $value)
    {
        if (! $value instanceof MongoRegex) {
            $value = new MongoRegex($value);
        }

        return $this->formatWhere('$or', $field, $value);
    }

    /**
     * Appends an and-where-exists statement
     *
     * @param string $field field name
     *
     * @return object $this
     */
    public function whereExists($field)
    {
        return $this->formatWhere('$and', $field, array('$exists' => true));
    }

    /**
     * Appends a or-where-exists statement
     *
     * @param string $field field name
     *
     * @return object $this
     */
    public function orWhereExists($field)
    {
        return $this->formatWhere('$or', $field, array('$exists' => true));
    }

    /**
     * Appends an and-where-exists statement
     *
     * @param string $field field name
     *
     * @return object $this
     */
    public function andWhereExists($field)
    {
        return call_user_func_array(array($this, 'whereExists'), array($field));
    }

    /**
     * Appends an and-where-not-exists statement
     *
     * @param string $field field name
     *
     * @return object $this
     */
    public function whereNotExists($field)
    {
        return $this->formatWhere('$and', $field, array('$exists' => false));
    }

    /**
     * Appends an and-where-not-exists statement
     *
     * @param string $field field name
     *
     * @return object $this
     */
    public function andWhereNotExists($field)
    {
        return call_user_func_array(array($this, 'whereNotExists'), array($field));
    }

    /**
     * Appends an or-where-not-exists statement
     *
     * @param string $field field name
     *
     * @return object $this
     */
    public function orWhereNotExists($field)
    {
        return $this->formatWhere('$or', $field, array('$exists' => false));
    }

    /**
     * Appends a and-where-in statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function whereIn($field, $values)
    {
        return $this->formatWhere('$and', $field, array('$in' => array_values($values)));
    }

    /**
     * Appends a and-where-in statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function andWhereIn($field, $values)
    {
        return call_user_func_array(array($this, 'whereIn'), array($field, $values));
    }

    /**
     * Appends a or-where-in statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function orWhereIn($field, $values)
    {
        return $this->formatWhere('$or', $field, array('$in' => array_values($values)));
    }

    /**
     * Appends a and-where-all statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function whereAll($field, $values)
    {
        return $this->formatWhere('$and', $field, array('$all' => array_values($values)));
    }

    /**
     * Appends a and-where-all statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function andWhereAll($field, $values)
    {
        return call_user_func_array(array($this, 'whereAll'), array($field, $values));
    }

    /**
     * Appends a or-where-all statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function orWhereAll($field, $values)
    {
        return $this->formatWhere('$or', $field, array('$all' => array_values($values)));
    }

    /**
     * Appends a and-where-not-in statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function whereNotIn($field, $values)
    {
        return $this->formatWhere('$and', $field, array('$nin' => array_values($values)));
    }

    /**
     * Appends a and-where-not-in statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function andWhereNotIn($field, $values)
    {
        return call_user_func_array(array($this, 'whereNotIn'), array($field, $values));
    }

    /**
     * Appends a or-where-not-in statement
     *
     * @param string $field  field name
     * @param array  $values search values
     *
     * @return object $this
     */
    public function orWhereNotIn($field, $values)
    {
        return $this->formatWhere('$or', $field, array('$nin' => array_values($values)));
    }

    /**
     * Appends a and-where-size statement
     *
     * @param string  $field field name
     * @param integer $size  array size
     *
     * @return object $this
     */
    public function whereSize($field, $size)
    {
        return $this->formatWhere('$and', $field, array('$size' => $size));
    }

    /**
     * Appends a and-where-size statement
     *
     * @param string  $field field name
     * @param integer $size  array size
     *
     * @return object $this
     */
    public function andWhereSize($field, $size)
    {
        return call_user_func_array(array($this, 'whereSize'), array($field, $size));
    }

    /**
     * Appends a or-where-size statement
     *
     * @param string  $field field name
     * @param integer $size  array size
     *
     * @return object $this
     */
    public function orWhereSize($field, $size)
    {
        return $this->formatWhere('$or', $field, array('$size' => $size));
    }

    /**
     * Appends a and-where-type statement
     *
     * @param string  $field field name
     * @param integer $type  value type
     *
     * @return object $this
     */
    public function whereType($field, $type)
    {
        return $this->formatWhere('$and', $field, array('$type' => $this->resolveType($type)));
    }

    /**
     * Appends a and-where-type statement
     *
     * @param string  $field field name
     * @param integer $type  value type
     *
     * @return object $this
     */
    public function andWhereType($field, $type)
    {
        return call_user_func_array(array($this, 'whereType'), array($field, $type));
    }

    /**
     * Appends a or-where-type statement
     *
     * @param string  $field field name
     * @param integer $type  value type
     *
     * @return object $this
     */
    public function orWhereType($field, $type)
    {
        return $this->formatWhere('$or', $field, array('$type' => $this->resolveType($type)));
    }

    /**
     * Appends a and-where-lower-than statement
     *
     * @param string  $field field name
     * @param integer $value lower boundary
     *
     * @return object $this
     */
    public function whereLt($field, $value)
    {
        return $this->formatWhere('$and', $field, array('$lt' => $value));
    }

    /**
     * Appends a and-where-lower-than statement
     *
     * @param string  $field field name
     * @param integer $value lower boundary
     *
     * @return object $this
     */
    public function andWhereLt($field, $value)
    {
        return call_user_func_array(array($this, 'whereLt'), array($field, $value));
    }

    /**
     * Appends a or-where-lower-than statement
     *
     * @param string  $field field name
     * @param integer $value lower boundary
     *
     * @return object $this
     */
    public function orWhereLt($field, $value)
    {
        return $this->formatWhere('$or', $field, array('$lt' => $value));
    }

    /**
     * Appends a and-where-lower-than-or-equal statement
     *
     * @param string  $field field name
     * @param integer $value lower boundary
     *
     * @return object $this
     */
    public function whereLte($field, $value)
    {
        return $this->formatWhere('$and', $field, array('$lte' => $value));
    }

    /**
     * Appends a and-where-lower-than-or-equal statement
     *
     * @param string  $field field name
     * @param integer $value lower boundary
     *
     * @return object $this
     */
    public function andWhereLte($field, $value)
    {
        return call_user_func_array(array($this, 'whereLte'), array($field, $value));
    }

    /**
     * Appends a or-where-lower-than-or-equal statement
     *
     * @param string  $field field name
     * @param integer $value lower boundary
     *
     * @return object $this
     */
    public function orWhereLte($field, $value)
    {
        return $this->formatWhere('$or', $field, array('$lte' => $value));
    }

    /**
     * Appends a and-where-greater-than statement
     *
     * @param string  $field field name
     * @param integer $value high boundary
     *
     * @return object $this
     */
    public function whereGt($field, $value)
    {
        return $this->formatWhere('$and', $field, array('$gt' => $value));
    }

    /**
     * Appends a and-where-greater-than statement
     *
     * @param string  $field field name
     * @param integer $value high boundary
     *
     * @return object $this
     */
    public function andWhereGt($field, $value)
    {
        return call_user_func_array(array($this, 'whereGt'), array($field, $value));
    }

    /**
     * Appends a or-where-greater-than statement
     *
     * @param string  $field field name
     * @param integer $value high boundary
     *
     * @return object $this
     */
    public function orWhereGt($field, $value)
    {
        return $this->formatWhere('$or', $field, array('$gt' => $value));
    }

    /**
     * Appends a and-where-greater-than-or-equal statement
     *
     * @param string  $field field name
     * @param integer $value high boundary
     *
     * @return object $this
     */
    public function whereGte($field, $value)
    {
        return $this->formatWhere('$and', $field, array('$gte' => $value));
    }

    /**
     * Appends a and-where-greater-than-or-equal statement
     *
     * @param string  $field field name
     * @param integer $value high boundary
     *
     * @return object $this
     */
    public function andWhereGte($field, $value)
    {
        return call_user_func_array(array($this, 'whereGte'), array($field, $value));
    }

    /**
     * Appends a or-where-greater-than-or-equal statement
     *
     * @param string  $field field name
     * @param integer $value high boundary
     *
     * @return object $this
     */
    public function orWhereGte($field, $value)
    {
        return $this->formatWhere('$or', $field, array('$gte' => $value));
    }

    /**
     * Appends a and-where-between statement
     *
     * @param string  $field field name
     * @param integer $min   lower boundary
     * @param integer $max   height boundary
     *
     * @return object $this
     */
    public function whereBetween($field, $min, $max)
    {
        return $this->formatWhere('$and', $field, array('$gt' => $min, '$lt' => $max));
    }

    /**
     * Appends a and-where-between statement
     *
     * @param string  $field field name
     * @param integer $min   lower boundary
     * @param integer $max   height boundary
     *
     * @return object $this
     */
    public function andWhereBetween($field, $min, $max)
    {
        return call_user_func_array(array($this, 'whereBetween'), array($field, $min, $max));
    }

    /**
     * Appends a or-where-between statement
     *
     * @param string  $field field name
     * @param integer $min   lower boundary
     * @param integer $max   height boundary
     *
     * @return object $this
     */
    public function orWhereBetween($field, $min, $max)
    {
        return $this->formatWhere('$or', $field, array('$gt' => $min, '$lt' => $max));
    }

    /**
     * Appends a and-where-id statement
     *
     * @param string  $value id
     * @param integer $field _id field
     *
     * @return object $this
     */
    public function whereId($value, $field = '_id')
    {
        if (! $value instanceof MongoId) {
            $value = new MongoId($value);
        }

        return $this->formatWhere('$and', $field, $value);
    }

    /**
     * Appends a and-where-id statement
     *
     * @param string $value id
     * @param string $field _id field
     *
     * @return object $this
     */
    public function andWhereId($value, $field = '_id')
    {
        return call_user_func_array(array($this, 'whereId'), array($value, $field));
    }

    /**
     * Appends a or-where-id statement
     *
     * @param string $value id
     * @param string $field _id field
     *
     * @return object $this
     */
    public function orWhereId($value, $field = '_id')
    {
        if (! $value instanceof MongoId) {
            $value = new MongoId($value);
        }

        return $this->formatWhere('$or', $field, $value);
    }

    /**
     * Appends a and-where-near statement
     *
     * @param string $field   _id field
     * @param float  $lon     longitude
     * @param float  $lat     latitude
     * @param array  $options options
     *
     * @return object $this
     */
    public function whereNear($field, $lon, $lat, $options = array())
    {
        return $this->formatWhere('$and', $field, array('$near' => array($lon, $lat)) + $options);
    }

    /**
     * Appends a and-where-near statement
     *
     * @param string $field   _id field
     * @param float  $lon     longitude
     * @param float  $lat     latitude
     * @param array  $options options
     *
     * @return object $this
     */
    public function andWhereNear($field, $lon, $lat, $options = array())
    {
        return call_user_func_array(array($this, 'whereNear'), array($field, $lon, $lat, $options));
    }

    /**
     * Appends a and-where-near statement
     *
     * @param string $field   _id field
     * @param float  $lon     longitude
     * @param float  $lat     latitude
     * @param array  $options options
     *
     * @return object $this
     */
    public function orWhereNear($field, $lon, $lat, $options = array())
    {
        return $this->formatWhere('$or', $field, array('$near' => array($lon, $lat)) + $options);
    }

    /**
     * Appends a and-where-within statement
     *
     * @param string $field   _id field
     * @param string $shape   shape
     * @param array  $options options
     *
     * @return object $this
     */
    public function whereWithin($field, $shape, $options = array())
    {
        return $this->formatWhere('$and', $field, array('$within' => $shape) + $options);
    }

    /**
     * Appends a and-where-within statement
     *
     * @param string $field   _id field
     * @param string $shape   shape
     * @param array  $options options
     *
     * @return object $this
     */
    public function andWhereWithin($field, $shape, $options = array())
    {
        return call_user_func_array(array($this, 'whereWithin'), array($field, $shape, $options));
    }

    /**
     * Appends a and-where-near statement
     *
     * @param string $field   _id field
     * @param string $shape   shape
     * @param array  $options options
     *
     * @return object $this
     */
    public function orWhereWithin($field, $shape, $options = array())
    {
        return $this->formatWhere('$or', $field, array('$within' => $shape) + $options);
    }

    /**
     * Appends a and-nor-where-clause
     *
     * @param array|closure $clause nor where clause
     * @param integer       $type   chain type
     *
     * @return object $this
     */
    public function norWhere($clause, $type = '$and')
    {
        if ($clause instanceof Closure) {
            $query = new static();
            $clause($query);
            $clause = $query->getWhere();

            if (empty($clause)) {
                return $this;
            }
        }

        if (! is_array($clause)) {
            throw new \InvalidArgumentException('$nor statements should be Closures or arrays.');
        }

        return $this->formatWhere($type, '$nor', array($clause));
    }

    /**
     * Appends a and-nor-where-clause
     *
     * @param array|closure $clause nor where clause
     *
     * @return object $this
     */
    public function andNorWhere($clause)
    {
        return $this->norWhere($clause);
    }

    /**
     * Appends a or-nor-where-clause
     *
     * @param array|closure $clause nor where clause
     *
     * @return object $this
     */
    public function orNorWhere($clause)
    {
        return $this->norWhere($clause, '$or');
    }

    /**
     * Appends a and-not-where-clause
     *
     * @param array|closure $clause nor where clause
     * @param integer       $type   chain type
     *
     * @return object $this
     */
    public function notWhere($clause, $type = '$and')
    {
        if ($clause instanceof Closure) {
            $query = new static();
            $clause($query);
            $clause = $query->getWhere();

            if (empty($clause)) {
                return $this;
            }
        }

        if (! is_array($clause)) {
            throw new \InvalidArgumentException('$not statements should be Closures or arrays.');
        }

        return $this->formatWhere($type, '$not', array($clause));
    }

    /**
     * Appends a and-not-where-clause
     *
     * @param array|closure $clause nor where clause
     *
     * @return object $this
     */
    public function andNotWhere($clause)
    {
        return $this->notWhere($clause);
    }

    /**
     * Appends a or-not-where-clause
     *
     * @param array|closure $clause nor where clause
     *
     * @return object $this
     */
    public function orNotWhere($clause)
    {
        return $this->notWhere($clause, '$or');
    }

    /**
     * Retrieve the formatted where filter
     *
     * @return array filter query
     */
    public function getWhere()
    {
        $where = $this->where;

        if (! $where) {
            return array();
        }

        foreach ($where['$or'] as &$and) {
            if (isset($and['$and']) && count($and['$and']) === 1) {
                $and = reset($and['$and']);
            }

            if (isset($and['$or']) && count($and['$or']) === 1) {
                $and = reset($and['$or']);
            }
        }

        if (count($where['$or']) === 1) {
            $where = reset($where['$or']);
        }

        return $where;
    }

    /**
     * Resolves a string data type to its integer counterpart
     *
     * @param string|int $type data type
     *
     * @return int The data type's integer
     */
    public function resolveType($type)
    {
        if (is_numeric($type)) {
            return (int) $type;
        }

        $type = strtoupper($type);

        static $types = array(
            'DOUBLE' => 1, 'STRING' => 2, 'OBJECT' => 3, 'ARRAY' => 4,
            'BINARY' => 5, 'ID' => 8, 'BOOL' => 8, 'BOOLEAN' => 8,
            'DATE' => 9, 'NULL' => 10, 'REGEX' => 11, 'JAVASCRIPT' => 13,
            'CODE' => 13, 'SYMBOL' => 14, 'JAVASCRIPT_SCOPE' => 15,
            'CODE_SCOPE' => 15, 'INT32' => 16, 'TS' => 17, 'TIMESTAMP' => 17,
            'INT64' => 18, 'MIN' => -1, 'MAX' => 127,
        );

        if (! isset($types[$type])) {
            throw new \InvalidArgumentException('Type "'.$type.'" could not be resolved');
        }

        return $types[$type];
    }
}
