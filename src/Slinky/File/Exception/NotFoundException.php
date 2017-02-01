<?php

namespace Slinky\File\Exception;

use Slinky\Exception\BaseException;

class NotFoundException extends BaseException
{
	/**
	 * @param string $message
	 * @return void
	 */
	public function __construct($message)
	{
		parent::__construct('File not found', $message);
	}
}
