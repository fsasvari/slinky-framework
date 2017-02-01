<?php

namespace Slinky\Exception\Core;

use Slinky\Exception\BaseException;

class InvalidArgumentException extends BaseException
{
	/**
	 * @param string $message
	 * @return void
	 */
	public function __construct($message)
	{
		parent::__construct('Invalid argument', $message);
	}
}
