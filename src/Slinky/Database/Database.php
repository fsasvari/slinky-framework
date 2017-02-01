<?php

namespace Slinky\Database;

use Slinky\Database\ConnectionFactory;
use Slinky\Config\Config;

/**
 * @method \Slinky\Database\Query\Builder table() table(string $table) Begin a query from database table
 */
class Database
{
	/**
	 * The database connection factory instance
	 * 
	 * @var \Slinky\Database\ConnectionFactory
	 */
	private $factory;
	
	/**
	 * The config instance
	 * 
	 * @var \Slinky\Config\Config
	 */
	private $config;
	
	/**
	 * The active connection instances
	 * 
	 * @var array
	 */
	private $connections;
	
	/**
	 * Create a new Database instance
	 * 
	 * @param \Slinky\Database\ConnectionFactory $factory
	 * @param \Slinky\Config\Config $config
	 * @return void
	 */
    public function __construct(ConnectionFactory $factory, Config $config)
    {
		$this->factory = $factory;
		$this->config = $config;
    }
	
	/**
	 * Get a database connection instance
	 * 
	 * @param string $name
	 * @return \Slinky\Database\Connection
	 */
	public function connection($name = null)
	{
		$name = $name ?: $this->getDefaultConnection();
		
		if (!arr_has($this->connections, $name)) {
			$connection = $this->makeConnection($name);
			
			$this->connections[$name] = $connection;
		}
		
		return $this->connections[$name];
	}
	
	/**
	 * Make the database connection instance
	 * 
	 * @param string $name
	 * @return \Slinky\Database\Connection
	 */
	private function makeConnection($name)
	{
		$config = $this->getConfig($name);
		$logQueries = $this->logQueries();
		
		return $this->factory->make($config, $logQueries);
	}
	
	/**
	 * Get the configuration for a connection
	 * 
	 * @param string $name
	 * @return array
	 */
	private function getConfig($name)
	{
		$name = $name ?: $this->getDefaultConnection();
		
		$connections = $this->config->get('database.connections');
		
		$config = arr_get($connections, $name);
		
		return $config;
	}
	
	/**
	 * Log queries or not ?
	 * 
	 * @return bool
	 */
	public function logQueries()
	{
		return $this->config->get('database.log');
	}
	
	/**
	 * Get the default connection name
	 * 
	 * @return string
	 */
	public function getDefaultConnection()
	{
		return $this->config->get('database.default');
	}
	
	/**
	 * Set the default connection name
	 * 
	 * @return string
	 */
	public function setDefaultConnection($name)
	{
		$this->config->set('database.default', $name);
	}
	
	/**
     * Return all of the created connections
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }
	
	/**
     * Dynamically pass methods to the default connection
     *
     * @param string $method
     * @param array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->connection(), $method], $parameters);
    }
}
