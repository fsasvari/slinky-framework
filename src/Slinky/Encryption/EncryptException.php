<?php

namespace Slinky\Encryption;

use Slinky\Exception\BaseException;

class EncryptException extends BaseException
{
	/**
	 * Create a new encrypt exception instance
	 * 
	 * @param string $message
	 * @return void
	 */
	public function __construct($message)
	{
		parent::__construct('Encryption error', $message);
	}
}
