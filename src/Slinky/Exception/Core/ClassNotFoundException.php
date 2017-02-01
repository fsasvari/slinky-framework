<?php

namespace Slinky\Exception\Core;

use Slinky\Exception\BaseException;

class ClassNotFoundException extends BaseException
{
	/**
	 * @param string $message
	 * @return void
	 */
	public function __construct($message)
	{
		parent::__construct('Class not found', $message);
	}
}
