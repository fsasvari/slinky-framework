<?php

namespace Slinky\Database;

use PDO;

class Connector
{
	/**
	 * The default PDO connection options
	 * 
	 * @var array
	 */
	private $options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => true
	];
	
	/**
	 * Establish a database connection
	 * 
	 * @param array $config
	 * @return \PDO
	 */
	public function connect(array $config)
	{
		$dsn = $this->getDsn($config);
		$options = $this->getOptions($config);
		
		$connection = $this->createConnection($dsn, $config, $options);
		
		// Set "names" and "collate" on the connection so a correct character set will be used by client
		$names = "SET NAMES '" . $config['charset'] . "'" . (isset($config['collation']) ? " collate '" . $config['collation'] . "'" : '');
		
		$connection->prepare($names)->execute();
		
		// Set "timezone" if it has been specified in the config
		if (isset($config['timezone'])) {
			$connection->prepare("SET time_zone='" . $config['timezone'] . "'")->execute();
		}
		
		return $connection;
	}
	
	/**
	 * Create a new PDO connection
	 * 
	 * @param string $dsn
	 * @param array $config
	 * @param array $options
	 * @return \PDO
	 */
	private function createConnection($dsn, array $config, array $options)
	{
		$username = arr_get($config, 'username');
		$password = arr_get($config, 'password');
		
		$pdo = new PDO($dsn, $username, $password, $options);
		
		return $pdo;
	}
	
	/**
	 * Create a DSN string from a configuration
	 * 
	 * @param array $config
	 * @return string
	 */
	private function getDsn(array $config)
	{
		return 'mysql:host=' . $config['host'] . ';dbname=' . $config['database'];
	}
	
	/**
	 * Get the PDO options based on the configuration
	 * 
	 * @param array $config
	 * @return array
	 */
	private function getOptions(array $config)
	{
		$options = arr_get($config, 'options', []);
		
		return array_diff_key($this->options, $options) + $options;
	}
}
