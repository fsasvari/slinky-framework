<?php

namespace Slinky\Hash;

class Hash
{
	/**
	 * Hash the given value
	 * 
	 * @param string $value
	 * @param int $rounds
	 * @return string
	 */
	public function make($value, $rounds = 12)
	{
		return password_hash($value, PASSWORD_DEFAULT, array('cost' => $rounds));
	}
	
	/**
	 * Check the given plain value against a hash
	 * 
	 * @param string $value
	 * @param string $hashedValue
	 * @return bool
	 */
	public function check($value, $hashedValue)
	{
		return password_verify($value, $hashedValue);
	}
	
	/**
	 * Check if the given hash has been hashed using the given rounds
	 * 
	 * @param string $hashedValue
	 * @param int $rounds
	 * @return bool
	 */
	public function needsRehash($hashedValue, $rounds = 12)
	{
		return password_needs_rehash($hashedValue, PASSWORD_DEFAULT, array('cost' => $rounds));
	}
}
