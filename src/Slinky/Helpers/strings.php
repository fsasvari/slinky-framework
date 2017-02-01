<?php

use Slinky\Helpers\Slug;

/**
 * Removes whitespace from string
 * 
 * @param string $value
 * @param string $replace
 * @return string
 */
function str_remove_whitespace($value, $replace = '')
{
	return preg_replace('/\s+/', $replace, $value);
}


/**
 * Generate a URL friendly "slug" from a given string
 * 
 * @param string $title
 * @param string $separator
 * @return string
 */
function str_slug($title, $separator = '-')
{
	return Slug::slug($title, $separator);
}


/**
 * Convert a value to camelCase
 * 
 * @param string $value
 * @return string
 */
function str_camel_case($value)
{
	return lcfirst(str_studly_caps($value));
}


/**
 * Convert a value to StudlyCaps
 * 
 * @param string $value
 * @return string
 */
function str_studly_caps($value)
{
	return str_remove_whitespace(ucwords(str_replace(array('-', '_'), ' ', $value)));
}


/**
 * Convert a value to snake_case
 * 
 * @param string $value
 * @return string
 */
function str_snake_case($value, $delimiter = '_')
{
	return str_remove_whitespace(str_lower_case(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value)));
}


/**
 * Convert the given string to UPPER CASE
 * 
 * @param string $value
 * @return string
 */
function str_upper_case($value)
{
	return mb_strtoupper($value);
}


/**
 * Convert the given string to lower case
 * 
 * @param string $value
 * @return string
 */
function str_lower_case($value)
{
	return mb_strtolower($value);
}


/**
 * Convert the given string to Title Case
 * 
 * @param string $value
 * @return string
 */
function str_title_case($value)
{
	return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
}


/**
 * Generate random alpha-numeric string
 * 
 * @param int $length
 * @return string
 */
function str_random($length = 16)
{
	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}


/**
 * Return the lenght of the given string
 * 
 * @param string $value
 * @return int
 */
function str_length($value)
{
	return mb_strlen($value);
}


/**
 * Limit the number of characters in a string
 * 
 * @param string $value
 * @param int $limit
 * @param string $end
 * @return string
 */
function str_limit($value, $limit = 100, $end = '...')
{
	$value_cleaned = str_remove_whitespace(strip_tags($value), ' ');
	
	if (mb_strwidth($value_cleaned, 'UTF-8') <= $limit) {
        return $value_cleaned;
    }
	
	return rtrim(mb_strimwidth($value_cleaned, 0, $limit, '', 'UTF-8')) . $end;
}


/**
 * Determine if a given string starts with a given substring
 * 
 * @param string $haystack
 * @param string|array $needles
 * @return bool
 */
function str_starts_with($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ($needle != '' && strpos($haystack, $needle) === 0) {
			return true;
		}
	}
	
	return false;
}


/**
 * Determine if a given string ends with a given substring
 * 
 * @param string $haystack
 * @param string|array $needles
 * @return bool
 */
function str_ends_with($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ((string) $needle === substr($haystack, -strlen($needle))) {
			return true;
		}
	}
	
	return false;
}


/**
 * Determine if a given string contains a given substring
 * 
 * @param string $haystack
 * @param string|array $needles
 * @return bool
 */
function str_contains($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ($needle != '' && strpos($haystack, $needle) !== false) {
			return true;
		}
	}
	
	return false;
}


/**
 * Get characters after last delimiter
 * 
 * @param string $value
 * @param string $delimiter
 * @return string
 */
function str_get_after_last($value, $delimiter = '-')
{
	return substr($value, strrpos($value, $delimiter) + 1);
}


/**
 * Get all strings between two characters
 * 
 * @param string $value
 * @param string $start
 * @param string $end
 * @return array
 */
function str_get_between($value, $start, $end)
{
	preg_match_all("/" . $start . "(.*?)" . $end . "/", $value, $matches);
	
	return $matches[1];
}
