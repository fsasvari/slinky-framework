<?php

namespace Slinky\Cookie;

use Slinky\Encryption\Crypt;

class Cookie
{
	/**
	 * The crypt instance
	 * 
	 * @var \Slinky\Encryption\Crypt
	 */
	private $crypt;
	
	/**
	 * Create new cookie instance
	 * 
	 * @param \Slinky\Encryption\Crypt
	 * @return void
	 */
	public function __construct(Crypt $crypt)
	{
		$this->crypt = $crypt;
	}
	
	/**
	 * Save cookie with value and expiration time
	 * 
	 * @param string $name
	 * @param string/array $value
	 * @param int $expire Time in seconds
	 * @return bool
	 */
	public function set($name, $value, $expire = 3600, $path = '/')
	{
		if ($value) {
			setcookie($name, $this->crypt->encrypt($value), time() + $expire, $path);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Load cookie
	 *
	 * @return cookie value or bool false
	 */
	public function get($name)
	{
		if ($this->exists($name)) {
			return $this->crypt->decrypt($this->filter($name));
		} else {
			return false;
		}
	}
	
	/**
	 * Delete cookie
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function remove($name, $path = '/')
	{
		if ($this->exists($name)) {
			setcookie($name, '', -1, $path);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check if cookie exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function exists($name)
	{
		if ($this->filter($name)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Filter cookie with filter_input
	 * 
	 * @param string $name
	 * @return string
	 */
	private function filter($name)
	{
		return filter_input(INPUT_COOKIE, $name);
	}
}
