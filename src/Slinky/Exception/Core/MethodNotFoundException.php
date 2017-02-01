<?php

namespace Slinky\Exception\Core;

use Slinky\Exception\BaseException;

class MethodNotFoundException extends BaseException
{
	/**
	 * @param string $message
	 * @return void
	 */
	public function __construct($message)
	{
		parent::__construct('Method not found', $message);
	}
}
