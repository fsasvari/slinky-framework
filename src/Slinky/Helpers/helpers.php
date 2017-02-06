<?php

/**
 * Get env value
 * 
 * @param string $key
 * @param mixed $default
 * @return boolean|string
 */
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

/**
 * Get the path to the storage directory
 *
 * @param string $file
 * @return string
 */
function elixir($file)
{
	$path = ROOT_PATH.'/public_html/rev-manifest.json';

	if (file_exists($path)) {
		$manifest = json_decode(file_get_contents($path), true);
	}

	if (isset($manifest[$file])) {
		return $manifest[$file];
	}

	$unversioned = ROOT_PATH.$file;

	if (file_exists($unversioned)) {
		return $file;
	}
}