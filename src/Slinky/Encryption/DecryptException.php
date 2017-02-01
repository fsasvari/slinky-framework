<?php

namespace Slinky\Encryption;

use Slinky\Exception\BaseException;

class DecryptException extends BaseException
{
	/**
	 * Create a new decrypt exception instance
	 * 
	 * @param string $message
	 * @return void
	 */
	public function __construct($message)
	{
		parent::__construct('Decryption error', $message);
	}
}
