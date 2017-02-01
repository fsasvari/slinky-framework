<?php

namespace Slinky\Session;

use Slinky\Exception\BaseException;

class TokenMismatchException extends BaseException
{
	/**
	 * Create new token mismatch exception instance
	 * 
	 * @param string $message
	 * @return void
	 */
	public function __construct($message)
	{
		parent::__construct('Token mismatch', $message);
	}
}
