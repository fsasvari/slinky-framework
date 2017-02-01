<?php

namespace Slinky\Config;

class Config
{
	/**
	 * List of configuration items
	 * 
	 * @var array
	 */
	private $items = [];
	
	/**
	 * Create config instance
	 * 
	 * @param array $items
	 * @return void
	 */
	public function __construct(array $items = [])
	{
		$this->import($items);
	}
	
	/**
	 * Import config file from array
	 * 
	 * @param array $items
	 * @return void
	 */
	public function import(array $items = [])
	{
		$this->items = $items;
	}
	
	/**
	 * Determine if the given configuration value exists
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		return arr_has($this->items, $key);
	}
	
	/**
	 * Get the specified configuration value
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return arr_get($this->items, $key);
	}
	
	/**
	 * Set a given configuration value
	 * 
	 * @param array|string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value = null)
	{
		if (is_array($key)) {
			foreach ($key as $innerKey => $innerValue) {
				arr_set($this->items, $innerKey, $innerValue);
			}
		} else {
			arr_set($this->items, $key, $value);
		}
	}
	
	/**
	 * Get all of the configuration items for the application
	 * 
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}
}
