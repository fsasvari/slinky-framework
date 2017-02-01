<?php

namespace Slinky\Database;

use PDO;
use Slinky\Database\Connector;
use Slinky\Database\Connection;
use Slinky\Database\Query\Grammar as QueryGrammar;
use Slinky\Database\QueryLog;

class ConnectionFactory
{
	/**
	 * Establish a PDO connection
	 * 
	 * @param array $config
	 * @param bool $logQueries
	 * @return \Slinky\Database\Connection
	 */
	public function make(array $config, $logQueries = false)
	{
		$pdo = $this->createConnector()->connect($config);
		$grammar = $this->createGrammar();
		$log = $this->createLog($logQueries);
		
		return $this->createConnection($pdo, $grammar, $log, $config['database']);
	}
	
	/**
	 * Create a new Connector instance
	 * 
	 * @return \Slinky\Database\Connector
	 */
	private function createConnector()
	{
		return new Connector();
	}
	
	/**
	 * Create a new Connection instance
	 * 
	 * @param \PDO $pdo
	 * @param \Slinky\Database\Query\Grammar $queryGrammar
	 * @param \Slinky\Database\QueryLog $queryLog
	 * @param string $databaseName
	 * @return \Slinky\Database\Connection
	 */
	private function createConnection(PDO $pdo, QueryGrammar $queryGrammar, QueryLog $queryLog, $databaseName)
	{
		return new Connection($pdo, $queryGrammar, $queryLog, $databaseName);
	}
	
	/**
	 * Create a new QueryLog instance
	 * 
	 * @param bool $logQueries
	 * @return \Slinky\Database\QueryLog
	 */
	private function createLog($logQueries)
	{
		$queryLog = new QueryLog();
		if ($logQueries) {
			$queryLog->enable();
		}
		return $queryLog;
	}
	
	/**
	 * Create a new QueryGrammar instance
	 * 
	 * @return \Slinky\Database\Query\Grammar
	 */
	private function createGrammar()
	{
		return new QueryGrammar();
	}
}
