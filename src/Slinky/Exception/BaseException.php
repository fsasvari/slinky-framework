<?php

namespace Slinky\Exception;

use Exception;

abstract class BaseException extends Exception
{
	/**
	 * Title of the exception
	 * 
	 * @var string 
	 */
	protected $title;
	
	/**
	 * Create a new base exception instance
	 * 
	 * @param string $title
	 * @param string $message
	 * @param int $code
	 * @param \Exception $previous
	 * @return void
	 */
	public function __construct($title, $message, $code = 0, Exception $previous = null)
	{
		$this->title = $title;
		
		parent::__construct($message, $code, $previous);
	}
	
	/**
	 * Get the title of the exception
	 * 
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
}
