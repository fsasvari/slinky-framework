<?php

namespace Slinky\Database\Query;

use Slinky\Database\Connection;
use Slinky\Database\Query\Grammar;

class Builder
{
	/**
	 * The database connection instance
	 * 
	 * @var \Slinky\Database\Connection
	 */
	private $connection;
	
	/**
     * The database query grammar instance
     *
     * @var \Slinky\Database\Query\Grammar
     */
    private $grammar;
	
	/**
     * The current query value bindings.
     *
     * @var array
     */
	private $bindings = [];
	
	/**
     * An aggregate function and column to be run
     *
     * @var array
     */
    public $aggregate;
	
	/**
     * The columns that should be returned
     *
     * @var array
     */
    public $columns;
	
	/**
     * Indicates if the query returns distinct results
     *
     * @var bool
     */
    public $distinct = false;
	
	/**
     * The table which the query is targeting
     *
     * @var string
     */
	public $from;
	
	/**
     * The table joins for the query
     *
     * @var array
     */
	public $joins;
	
	/**
     * The where constraints for the query
     *
     * @var string
     */
	public $where;
	
	/**
     * The groupings for the query
     *
     * @var array
     */
	public $groups;
	
	 /**
     * The having constraints for the query
     *
     * @var string
     */
	public $having;
	
	/**
     * The orderings for the query
     *
     * @var array
     */
	public $orders;
	
	/**
     * The maximum number of records to return
     *
     * @var int
     */
	public $limit;
	
	/**
     * The number of records to skip
     *
     * @var int
     */
	public $offset;
	
	/**
	 * Create a new query builder instance
	 * 
	 * @param \SLinky\Database\Connection $connection
	 * @param \Slinky\Database\Query\Grammar $grammar
	 * @return void
	 */
	public function __construct(Connection $connection, Grammar $grammar)
	{
		$this->connection = $connection;
		$this->grammar = $grammar;
	}
	
	/**
     * Set the columns to be selected
     *
     * @param array|mixed $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = array_merge(is_array($columns) ? $columns : func_get_args(), (array) $this->columns);
		
        return $this;
    }
	
	/**
     * Force the query to only return distinct results
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;
		
        return $this;
    }
	
	/**
     * Set the table which the query is targeting
     *
     * @param string $table
     * @return $this
     */
    public function from($table)
    {
        $this->from = $table;
		
        return $this;
    }
	
	/**
     * Add a join clause to the query
     *
     * @param string $table
	 * @param string $condition
     * @param string $type
     * @return $this
     */
    public function join($table, $condition, $type = 'INNER')
    {
        $this->joins[] = compact('table', 'condition', 'type');

        return $this;
    }
	
	/**
     * Alias for join() method
     *
     * @param string $table
	 * @param string $condition
     * @return $this
     */
    public function innerJoin($table, $condition)
    {
        return $this->join($table, $condition);
    }

    /**
     * Add a left join to the query
     *
     * @param string $table
     * @param string $condition
     * @return $this
     */
    public function leftJoin($table, $condition)
    {
        return $this->join($table, $condition, 'LEFT');
    }

    /**
     * Add a right join to the query
     *
     * @param string $table
     * @param string $condition
     * @return $this
     */
    public function rightJoin($table, $condition)
    {
        return $this->join($table, $condition, 'RIGHT');
    }
	
	/**
	 * Add a where clause to the query
	 * 
	 * @param string $where
	 * @param array $bindings
	 * @return $this
	 */
	public function where($where, array $bindings = [])
	{
		$this->where = $where;
		
		$this->addBinding($bindings);
		
		return $this;
	}

	/**
     * Add a "group by" clause to the query
     *
     * @param array|string $column
     * @return $this
     */
    public function group()
    {
        foreach (func_get_args() as $arg) {
            $this->groups = array_merge((array) $this->groups, is_array($arg) ? $arg : [$arg]);
        }
		
        return $this;
    }
	
	/**
	 * Add a having clause to the query
	 * 
	 * @param string $having
	 * @param array $bindings
	 * @return $this
	 */
	public function having($having, $bindings)
	{
		$this->having = $having;
		
		$this->addBinding($bindings);
		
		return $this;
	}
	
	/**
	 * Add an "order by" clause to the query.
	 * 
	 * @param string $column
	 * @param string $direction
	 * @return $this
	 */
	public function order($column, $direction = 'ASC')
	{
		$direction = str_upper_case($direction) == 'ASC' ? 'ASC' : 'DESC';
		
		$this->orders[] = compact('column', 'direction');
		
		return $this;
	}
	
	/**
     * Set the "offset" value of the query
     *
     * @param int $value
     * @return $this
     */
    public function offset($value)
    {
        $this->offset = max(0, $value);
		
        return $this;
    }
	
	/**
     * Set the "limit" value of the query
     *
     * @param int $value
     * @return $this
     */
    public function limit($value)
    {
        if ($value >= 0) {
            $this->limit = $value;
        }
		
        return $this;
    }
	
	/**
	 * Execute the query as a "select" statement
	 * 
	 * @return mixed
	 */
	public function get($columns = ['*'])
	{
		if (is_null($this->columns)) {
			$this->select($columns);
		}
		
		$query = $this->grammar->compileSelect($this);
		
		return $this->connection->select($query, $this->bindings);
	}
	
	/**
	 * Execute the query and get the first result
	 * 
	 * @return mixed
	 */
	public function first($columns = ['*'])
	{
		$results = $this->limit(1)->get($columns);
		
		return count($results) > 0 ? reset($results) : null;
	}
	
	/**
     * Retrieve the "count" result of the query
     *
     * @param string $columns
     * @return int
     */
	public function count($columns = '*')
	{
		if (! is_array($columns)) {
            $columns = [$columns];
        }
		
		return (int) $this->aggregate(__FUNCTION__, $columns);
	}
	
	/**
     * Retrieve the minimum value of a given column
     *
     * @param string $column
     * @return float|int
     */
	public function min($column)
	{
		return $this->aggregate(__FUNCTION__, [$column]);
	}
	
	/**
     * Retrieve the maximum value of a given column
     *
     * @param string $column
     * @return float|int
     */
	public function max($column)
	{
		return $this->aggregate(__FUNCTION__, [$column]);
	}
	
	/**
     * Retrieve the sum of the values of a given column
     *
     * @param string $column
     * @return float|int
     */
	public function sum($column)
	{
		$result = $this->aggregate(__FUNCTION__, [$column]);
		
        return $result ?: 0;
	}
	
	/**
     * Retrieve the average of the values of a given column
     *
     * @param string $column
     * @return float|int
     */
	public function avg($column)
	{
		return $this->aggregate(__FUNCTION__, [$column]);
	}
	
	/**
     * Execute an aggregate function on the database.
     *
     * @param string $function
     * @param array $columns
     * @return float|int
     */
	private function aggregate($function, $columns = ['*'])
	{
		$this->aggregate = compact('function', 'columns');
		
		$results = $this->get($columns);
		
		if (isset($results[0])) {
            $result = array_change_key_case((array) $results[0]);
            return $result['aggregate'];
        }
	}
	
	/**
	 * Insert a new record into the database
	 * 
	 * @param array $values
	 * @return int
	 */
	public function insert(array $values)
	{
		if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }
		
		$bindings = [];
		foreach ($values as $record) {
            foreach ($record as $value) {
                $bindings[] = $value;
            }
        }
		
		$sql = $this->grammar->compileInsert($this, $values);
		
		return $this->connection->insert($sql, $bindings);
	}
	
	/**
	 * Update a record in a database
	 * 
	 * @param array $values
	 * @return int
	 */
	public function update(array $values)
	{
		$bindings = array_merge($values, $this->bindings);
		
		$sql = $this->grammar->compileUpdate($this, $values);
		
		return $this->connection->update($sql, $bindings);
	}
	
	/**
     * Delete a record from the database
     *
     * @param mixed $id
     * @return int
     */
	public function delete($id = null)
	{
		if (! is_null($id)) {
            $this->where('id = :id', ['id' => $id]);
        }
		
		$sql = $this->grammar->compileDelete($this);
		
		return $this->connection->delete($sql, $this->bindings);
	}
	
	/**
     * Run a truncate statement on the table
     *
     * @return void
     */
	public function truncate()
	{
		$sql = $this->grammar->compileTruncate($this);
		
		$this->connection->statement($sql);
	}
	
	/**
     * Add a binding to the query
     *
     * @param mixed $value
     * @return $this
     */
    public function addBinding($value)
    {
        if (is_array($value)) {
            $this->bindings = array_merge($this->bindings, $value);
        } else {
            $this->bindings[] = $value;
        }

        return $this;
    }
}
