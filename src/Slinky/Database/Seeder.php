<?php

namespace Slinky\Database;

use Slinky\Container\Container;
use Slinky\Database\Database;

abstract class Seeder
{
	/**
	 * The container instance
	 * 
	 * @var \Slinky\Container\Container
	 */
	protected $container;
	
	/**
	 * The database instance
	 * 
	 * @var \Slinky\Database\Database
	 */
	protected $database;
	
	/**
	 * Create new seeder instance
	 * 
	 * @param \Slinky\Container\Container $container
	 * @param \Slinky\Database\Database $database
	 * @return void
	 */
	public function __construct(Container $container, Database $database)
	{
		$this->container = $container;
		$this->database = $database;
	}
	
	/**
	 * Run the database seeds
	 * 
	 * @return void
	 */
	abstract public function run();
	
	/**
	 * Seed the given connection from the given path
	 * 
	 * @param string $class
	 * @return void
	 */
	protected function call($class)
	{
		$this->resolve($class)->run();
	}
	
	/**
	 * Resolve an instance of the given seeder class
	 * 
	 * @param string $class
	 * @return \Slinky\Database\Seeder
	 */
	protected function resolve($class)
	{
		$instance = $this->container->get('Database\\Seeds\\'.$class.'Seeder');
		
		return $instance;
	}
}
