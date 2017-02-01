<?php

namespace Slinky\Database;

use PDO;
use Closure;
use Exception;
use Throwable;
use Slinky\Database\Query\Builder as QueryBuilder;
use Slinky\Database\Query\Grammar as QueryGrammar;
use Slinky\Database\QueryLog;
use Slinky\Database\QueryException;

class Connection
{
	/**
	 * The active PDO connection
	 * 
	 * @var \PDO
	 */
	private $pdo;
	
	/**
     * The QueryGrammar instance
     *
     * @var \Slinky\Database\Query\Grammar
     */
    private $queryGrammar;
	
	/**
     * The QueryLog instance
     *
     * @var array
     */
    private $queryLog;
	
	/**
	 * The name of the connected database
	 * 
	 * @var string
	 */
	private $database;
	
	/**
     * The default fetch mode of the connection
     *
     * @var int
     */
    private $fetchMode = PDO::FETCH_ASSOC;
	
	/**
     * The number of active transactions
     * 
     * @var int
     */
	private $transactions = 0;
	
	/**
	 * The number of active queries
	 * 
	 * @var int
	 */
	private $queries = 0;
	
	/**
	 * Create a new database connection instance
	 * 
	 * @param \PDO $pdo
	 * @param \Slinky\Database\QueryLog
	 * @param \Slinky\Database\Query\Grammar
	 * @param string $database
	 * @return void
	 */
	public function __construct(PDO $pdo, QueryGrammar $queryGrammar, QueryLog $queryLog, $database = '')
	{
		$this->pdo = $pdo;
		$this->queryGrammar = $queryGrammar;
		$this->queryLog = $queryLog;
		
		$this->database = $database;
	}
	
	/**
	 * Disconnect from the PDO connection
	 * 
	 * @return void
	 */
	public function disconnect()
	{
		$this->pdo = null;
	}
	
	/**
	 * Alias for disconnect()
	 * 
	 * @return void
	 */
	public function close()
	{
		$this->disconnect();
	}
	
	/**
	 * Get a new QueryBuilder instance
	 * 
	 * @return \Slinky\Database\Query\Builder
	 */
	public function query()
	{
		return new QueryBuilder($this, $this->queryGrammar);
	}
	
	/**
	 * Begin a query from database table
	 * 
	 * @param string $table
	 * @return \Slinky\Database\Query\Builder
	 */
	public function table($table)
	{
		return $this->query()->from($table);
	}
	
	/**
	 * Run a select statement and return a single result
	 * 
	 * @param string $query
	 * @param array $bindings
	 * @return mixed
	 */
	public function selectOne($query, $bindings = [])
	{
		$records = $this->select($query, $bindings);
		
        return count($records) > 0 ? reset($records) : null;
	}
	
	/**
     * Run a select statement against the database
     *
     * @param string $query
     * @param array $bindings
     * @return array
     */
	public function select($query, $bindings = [])
	{
		return $this->run($query, $bindings, function($me, $query, $bindings) {
			$statement = $me->pdo->prepare($query);
			
			$statement->execute($bindings);
			
			return $statement->fetchAll($me->getFetchMode());
		});
	}
	
	/**
     * Run an insert statement against the database
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
	public function insert($query, $bindings = [])
	{
		return $this->lastInsertStatement($query, $bindings);
	}
	
	/**
     * Run an update statement against the database
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
	public function update($query, $bindings = [])
	{
		return $this->affectingStatement($query, $bindings);
	}
	
	/**
     * Run a delete statement against the database
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
	public function delete($query, $bindings = [])
	{
		return $this->affectingStatement($query, $bindings);
	}
	
	/**
	 * Run an SQL statement and return the boolean result
	 * 
	 * @param string $query
	 * @param array $bindings
	 * @return bool
	 */
	public function statement($query, $bindings = [])
	{
		return $this->run($query, $bindings, function($me, $query, $bindings) {
			return $me->pdo->prepare($query)->execute($bindings);
		});
	}
	
	/**
	 * Run an SQL statement and return the last inserted id
	 * 
	 * @param string $query
	 * @param array $bindings
	 * @return int
	 */
	public function lastInsertStatement($query, $bindings = [])
	{
		return $this->run($query, $bindings, function($me, $query, $bindings) {
			$me->pdo->prepare($query)->execute($bindings);
			
			return $me->pdo->lastInsertId();
		});
	}
	
	/**
	 * Run an SQL statement and get the number of rows affected
	 * 
	 * @param string $query
	 * @param array $bindings
	 * @return int
	 */
	public function affectingStatement($query, $bindings = [])
	{
		return $this->run($query, $bindings, function($me, $query, $bindings) {
			$statement = $me->pdo->prepare($query);
			
			$statement->execute($bindings);
			
			return $statement->rowCount();
		});
	}
	
	/**
	 * Run a SQL statement and logs its execution context
	 * 
	 * @param string $query
	 * @param array $bindings
	 * @param \Closure $callback
	 * @return mixed
	 */
	private function run($query, $bindings, Closure $callback)
	{
		$start = microtime(true);
		
		try {
			$result = $callback($this, $query, $bindings);
		} catch (Exception $e) {
			throw new QueryException($e->getMessage(), $query, $bindings);
		}
		
		$end = $this->getElapsedTime($start);
		
		$this->getQueryLog()->set($query, $bindings, $end);
		
		++$this->queries;
		
		return $result;
	}
	
	/**
     * Execute a Closure within a transaction
     *
     * @param \Closure $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback)
    {
        $this->transactionStart();
		
        try {
            $result = $callback($this);
            $this->transactionCommit();
        }
		
        catch (Exception $e) {
            $this->transactionRollback();
            throw $e;
        } catch (Throwable $e) {
            $this->transactionRollback();
            throw $e;
        }
		
        return $result;
    }
	
	/**
	 * Start a new database transaction
	 * 
	 * @return void
	 */
	public function transactionStart()
	{
		++$this->transactions;
		
		$this->pdo->beginTransaction();
	}
	
	/**
	 * Commit the active database transaction
	 * 
	 * @return void
	 */
	public function transactionCommit()
	{
		$this->pdo->commit();
	}
	
	/**
	 * Rollback the active database transaction
	 * 
	 * @return void
	 */
	public function transactionRollback()
	{
		--$this->transactions;
		
		$this->pdo->rollBack();
	}
	
	/**
     * Get the name of the connected database
     *
     * @return string
     */
	public function getDatabaseName()
	{
		return $this->database;
	}
	
	/**
     * Set the name of the connected database
     *
     * @param string $database
     * @return void
     */
	public function setDatabaseName($database)
	{
		$this->database = $database;
	}
	
	/**
     * Get the default fetch mode for the connection
     *
     * @return int
     */
	public function getFetchMode()
	{
		return $this->fetchMode;
	}
	
	/**
     * Set the default fetch mode for the connection
     *
     * @param int $fetchMode
     * @return void
     */
	public function setFetchMode($fetchMode)
	{
		$this->fetchMode = $fetchMode;
	}
	
	/**
	 * Get QueryLog instance
	 * 
	 * @return \Slinky\Database\QueryLog
	 */
	public function getQueryLog()
	{
		return $this->queryLog;
	}
	
	/**
	 * Get the number of queries
	 * 
	 * @return int
	 */
	public function getQueries()
	{
		return $this->queries;
	}
	
	/**
	 * Get the number of transactions
	 * 
	 * @return int
	 */
	public function getTransactions()
	{
		return $this->transactions;
	}
	
	/**
     * Get the elapsed time since a given starting point
     *
     * @param int $start
     * @return float
     */
    private function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }
}
