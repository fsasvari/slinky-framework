<?php

namespace Slinky\Validation;

use Slinky\Validation\Validate;

class ValidateFactory
{
	/**
	 * Build Validate object
	 * 
	 * @param bool $error
	 * @param array $errors
	 * @return Validate
	 */
	public function build($error, $errors)
	{
		return new Validate($error, $errors);
	}
}
