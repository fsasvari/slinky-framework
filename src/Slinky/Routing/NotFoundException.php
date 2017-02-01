<?php

namespace Slinky\Routing;

use Slinky\Exception\BaseException;

class NotFoundException extends BaseException
{
	/**
	 * Create a new not found exception instance
	 * 
	 * @param string $message
	 * @return void
	 */
	public function __construct($message)
	{
		parent::__construct('Not found error', $message);
	}
}
