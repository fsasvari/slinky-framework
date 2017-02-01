<?php

namespace Slinky\Database;

class QueryLog
{
	/**
     * Indicates whether queries are being logged
     *
     * @var bool
     */
	private $log = false;
	
	/**
     * List of all queries
     *
     * @var array
     */
	private $queries = [];
	
	/**
     * Get the query log
     *
     * @return array
     */
    public function get()
    {
        return $this->queries;
    }
	
	/**
     * Log a query in the query log
     *
     * @param string $query
     * @param array $bindings
     * @param float|null $time
     * @return void
     */
	public function set($query, $bindings, $time = null)
	{
		if ($this->logging()) {
			$this->queries[] = compact('query', 'bindings', 'time');
		}
	}
	
    /**
     * Clear the query log
     *
     * @return void
     */
    public function flush()
    {
        $this->queries = [];
    }
	
	/**
     * Enable the query log
     *
     * @return void
     */
    public function enable()
    {
        $this->log = true;
    }
	
    /**
     * Disable the query log
     *
     * @return void
     */
    public function disable()
    {
        $this->log = false;
    }
	
	/**
     * Determine whether we're logging queries
     *
     * @return bool
     */
    public function logging()
    {
        return $this->log;
    }
}
