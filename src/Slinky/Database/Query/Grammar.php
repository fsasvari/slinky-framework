<?php

namespace Slinky\Database\Query;

use Slinky\Database\Query\Builder;

class Grammar
{
	/**
     * The components that make up a select clause
     *
     * @var array
     */
	private $selectComponents = [
		'aggregate',
		'columns',
		'from',
		'joins',
		'where',
		'groups',
		'having',
		'orders',
		'limit',
		'offset'
	];
	
	/**
     * Compile a select query into SQL
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
	public function compileSelect(Builder $query)
	{
		if (is_null($query->columns)) {
            $query->columns = ['*'];
        }
		
		$sql = trim($this->concatenate($this->compileComponents($query)));
		
		return $sql;
	}
	
	/**
     * Compile the components necessary for a select clause
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return array
     */
	private function compileComponents(Builder $query)
	{
		$sql = [];
		
		foreach ($this->selectComponents as $component) {
			if (! is_null($query->$component)) {
				$method = 'compile' . ucfirst($component);
				
				$sql[$component] = $this->$method($query);
			}
		}
		
		return $sql;
	}
	
	 /**
     * Compile an aggregated select clause
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
    private function compileAggregate(Builder $query)
    {
		$aggregate = $query->aggregate;
		
        $column = $this->getColumns($aggregate['columns']);
		
        if ($query->distinct && $column !== '*') {
            $column = 'DISTINCT ' . $column;
        }
		
        return 'SELECT ' . strtoupper($aggregate['function']) . '(' . $column . ') AS aggregate';
    }
	
	/**
     * Compile the "select *" portion of the query
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string|null
     */
    private function compileColumns(Builder $query)
    {
		if (! is_null($query->aggregate)) {
            return;
        }
		
        $select = $query->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
		
        return $select . $this->getColumns($query->columns);
    }
	
	/**
     * Compile the "from" portion of the query
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
	private function compileFrom(Builder $query)
	{
		return 'FROM ' . $query->from;
	}
	
	/**
     * Compile the "join" portions of the query
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
	private function compileJoins(Builder $query)
	{
		$sql = [];
		
		foreach ($query->joins as $join) {
			$type = $join['type'];
			$table = $join['table'];
			$condition = $join['condition'];
			
			$sql[] = "$type JOIN $table ON $condition";
		}
		
		return implode(' ', $sql);
	}
	
	/**
	 * Compile the "where" portion of the query
	 * 
	 * @param \SLinky\Database\Query\Builder $query
	 * @return string
	 */
	private function compileWhere(Builder $query)
	{
		if (is_null($query->where)) {
			return '';
		}
		
		return 'WHERE ' . $query->where;
	}
	
	/**
     * Compile the "group by" portions of the query
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
	private function compileGroups(Builder $query)
	{
		return 'GROUP BY ' . $this->getColumns($query->groups);
	}
	
	/**
	 * Compile the "having" portion of the query
	 * 
	 * @param \SLinky\Database\Query\Builder $query
	 * @return string
	 */
	private function compileHaving(Builder $query)
	{
		return 'HAVING ' . $query->having;
	}
	
	/**
     * Compile the "order by" portions of the query
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
	private function compileOrders(Builder $query)
	{
		return 'ORDER BY ' . implode(', ', array_map(function ($order) {
			return $order['column'] . ' ' . $order['direction'];
		}, $query->orders));
	}
	
	/**
     * Compile the "limit" portions of the query
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
    private function compileLimit(Builder $query)
    {
        return 'LIMIT ' . (int) $query->limit;
    }
	
    /**
     * Compile the "offset" portions of the query
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
    private function compileOffset(Builder $query)
    {
        return 'OFFSET ' . (int) $query->offset;
    }
	
	/**
     * Compile an insert statement into SQL
     *
     * @param \SLinky\Database\Query\Builder $query
     * @param array $values
     * @return string
     */
	public function compileInsert(Builder $query, array $values)
	{
		$table = $query->from;
		
		if (! is_array(reset($values))) {
            $values = [$values];
        }
		
		$columns = $this->getColumns(array_keys(reset($values)));
		
		$parameters = [];
		
        foreach ($values as $record) {
            $parameters[] = '(' . $this->getParameters(array_keys(array_values($record))) . ')';
        }
		
        $parameters = implode(', ', $parameters);
		
		return "INSERT INTO $table ($columns) VALUES $parameters";
	}
	
	/**
     * Compile an update statement into SQL
     *
     * @param \SLinky\Database\Query\Builder $query
     * @param array $values
     * @return string
     */
	public function compileUpdate(Builder $query, array $values)
	{
		$table = $query->from;
		
		$columns = [];
		
        foreach (array_keys($values) as $key) {
            $columns[] = $key . ' = ' . $this->getParameter($key);
        }
		
        $columns = implode(', ', $columns);
		
		if (isset($query->joins)) {
            $joins = ' ' . $this->compileJoins($query);
        } else {
            $joins = '';
        }
		
		$where = $this->compileWhere($query);
		
		return trim("UPDATE {$table}{$joins} SET $columns $where");
	}
	
	/**
	 * Compile a delete statement into SQL
	 * 
	 * @param \SLinky\Database\Query\Builder $query
	 * @return string
	 */
	public function compileDelete(Builder $query)
	{
		$table = $query->from;
		
		$where = $this->compileWhere($query);
		
		return trim("DELETE FROM $table $where");
	}
	
	/**
     * Compile a truncate table statement into SQL
     *
     * @param \SLinky\Database\Query\Builder $query
     * @return string
     */
	public function compileTruncate(Builder $query)
	{
		return 'TRUNCATE ' . $query->from;
	}
	
	/**
	 * Convert an array of column names into a delimited string
	 * 
	 * @param array $columns
	 * @return string
	 */
	private function getColumns($columns)
	{
		return implode(', ', $columns);
	}
	
	/**
	 * Create query parameter place-holders for an array
	 * 
	 * @param array $values
	 * @return string
	 */
	private function getParameters($values)
	{
		return implode(', ', array_map([$this, 'getParameter'], $values));
	}
	
	/**
     * Get the appropriate query parameter place-holder for a value
     *
     * @param string $value
     * @return string
     */
	private function getParameter($value)
	{
		return is_int($value) ? '?' : ':' . $value;
	}
	
	/**
     * Concatenate an array of segments, removing empties
     *
     * @param array $segments
     * @return string
     */
    protected function concatenate($segments)
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string) $value !== '';
        }));
    }
}
