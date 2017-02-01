<?php

namespace Slinky\Validation;

class Validate
{
	private $error = false;
	private $errors = array();
	
	
	/**
	 * @param bool $error
	 * @param array $errors
	 * @return void
	 */
	public function __construct($error, $errors)
	{
		$this->error = $error;
		$this->errors = $errors;
	}
	
	
	/**
	 * @return bool
	 */
	public function isValid()
	{
		return !$this->error;
	}
	
	
	/**
	 * Get all error messages for all field
	 * 
	 * @return array
	 */
	public function errors()
	{
		$errors = array();
		foreach ($this->errors as $messages) {
			$errors[] = $messages;
		}
		return $errors;
	}
	
	
	/**
	 * Get all error messages for a field
	 * 
	 * @param string $name
	 * @return array|bool
	 */
	public function get($name)
	{
		if ($this->has($name)) {
			return $this->errors[$name];
		}
		return false;
	}
	
	
	/**
	 * Get first error message
	 * 
	 * @return string
	 */
	public function first($name = '')
	{
		if ($name && $this->has($name)) {
			return reset($this->errors[$name]);
		} else {
			foreach ($this->errors as $messages) {
				return $messages[0];
			}
		}
	}
	
	
	/**
	 * Check if messages exists for a field
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function has($name)
	{
		return arr_has($this->errors, $name);
	}
}
