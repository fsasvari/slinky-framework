<?php

namespace Slinky\Http;

class Header
{
	/**
	 * List of all HTTP headers
	 * 
	 * @var array
	 */
	private $headers = [];
	
	/**
	 * Set HTTP header value
	 * 
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function set($key, $value)
	{
		$this->headers[$key] = $value;
	}
	
	/**
	 * Get HTTP header value
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if ($key) {
			return arr_get($this->headers, $key);
		}
		
		return $this->all();
	}
	
	/**
	 * Return array of HTTP header names and values
	 * 
	 * @return array
	 */
	public function all()
	{
		return $this->headers;
	}
}
