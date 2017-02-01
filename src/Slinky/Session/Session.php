<?php

namespace Slinky\Session;

class Session
{
	/**
	 * List of flash sessions
	 * 
	 * @var array
	 */
	private $flash = [];
	
	/**
	 * Create new session instance
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->flash = $this->get('flash');
		$this->remove('flash');
	}
	
	/**
	 * Create new session
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @param bool $isFlash
	 * @return void
	 */
	public function set($name, $value, $isFlash = false)
	{
		if ($value) {
			if ($isFlash) {
				$this->flash[$name] = $value;
				$_SESSION['flash'][$name] = $value;
			} else {
				$_SESSION[$name] = $value;
			}
		}
	}
	
	/**
	 * Create new flash session
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function flash($name, $value)
	{
		$this->set($name, $value, true);
	}
	
	/**
	 * Reflash all flash sessions
	 * 
	 * return void
	 */
	public function reflash()
	{
		$this->set('flash', $this->flash);
	}
	
	/**
	 * Load session
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name)
	{
		if ($name == 'flash' && $this->exists($name)) {
			return $_SESSION[$name];
		} elseif ($this->existsFlash($name)) {
			return arr_get($this->flash, $name);
		} elseif ($this->exists($name)) {
			return $_SESSION[$name];
		} else {
			return false;
		}
	}
	
	/**
	 * Get all flash data
	 * 
	 * @return array
	 */
	public function getAllFlash()
	{
		return $this->flash;
	}
	
	/**
	 * Delete session
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function remove($name)
	{
		if ($this->existsFlash($name)) {
			unset($this->flash[$name]);
			return true;
		} elseif ($this->exists($name)) {
			unset($_SESSION[$name]);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check if session exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function exists($name)
	{
		if (isset($_SESSION[$name])) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check if flash session exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function existsFlash($name)
	{
		return arr_has($this->flash, $name);
	}
}
