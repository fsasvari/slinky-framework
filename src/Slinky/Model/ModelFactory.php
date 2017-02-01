<?php

namespace Slinky\Model;

class ModelFactory
{
	/**
	 * Namespace for model object
	 * 
	 * @var string
	 */
	private $namespace;
	
	/**
	 * Create new model factory instance
	 * 
	 * @param string $namespace Namespace for Entity include
	 * @return void
	 */
	public function __construct($namespace)
	{
		$this->namespace = $namespace;
	}
	
	/**
	 * Build new model object
	 *
	 * @param string $name
	 * @param array $data
	 * @return \Slinky\Model\Model
	 */
	public function build($name, array $data = [])
	{
		$model = '\\App\\Model\\'.$name;
		
		return new $model($data, true);
	}
}
