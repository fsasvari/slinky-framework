<?php

function env($key, $default = null)
{
	$value = getenv($key);
	
	if ($value === false) {
		return $default;
	}
	
	switch (str_lower_case($value)) {
		case 'true':
		case '(true)':
			return true;
		case 'false':
		case '(false)':
			return false;
		case 'empty':
		case '(empty)':
			return '';
		case 'null':
		case '(null)':
			return;
	}
	
	if (str_length($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
		return substr($value, 1, -1);
	}
	
	return $value;
}