<?php

namespace Slinky\Database;

use Slinky\Exception\BaseException;

class QueryException extends BaseException
{
	/**
	 * The SQL for the query
	 * 
	 * @var string
	 */
	private $sql;
	
	/**
	 * The bindings for the query
	 * 
	 * @var array
	 */
	private $bindings;
	
	/**
	 * Create a new query exception instance
	 * 
	 * @param string $message
	 * @param string $sql
	 * @param array $bindings
	 * @return void
	 */
	public function __construct($message, $sql, array $bindings = [])
	{
		$this->sql = $sql;
		$this->bindings = $bindings;
		
		parent::__construct('Database query error', $message);
	}
	
	/**
	 * Get the SQL for the query
	 * 
	 * @return string
	 */
	public function getSql()
	{
		return $this->sql;
	}
	
	/**
	 * Get the bindings for the query
	 * 
	 * @return array
	 */
	public function getBindings()
	{
		return $this->bindings;
	}
}
