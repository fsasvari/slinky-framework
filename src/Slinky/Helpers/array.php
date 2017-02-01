<?php

/**
 * Check if an item exists in an array using "dot" notation
 * 
 * @param array $array
 * @param string $key
 * @return bool
 */
function arr_has($array, $key)
{
	if (empty($array) || is_null($key)) {
		return false;
	}
	
    if (array_key_exists($key, $array)) {
        return true;
    }
	
    foreach (explode('.', $key) as $segment) {
        if (! is_array($array) || ! array_key_exists($segment, $array)) {
            return false;
        }
    }
	
    return true;
}

/**
 * Get an item from an array using "dot" notation
 * 
 * @param array $array
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function arr_get($array, $key, $default = null)
{
	if (is_null($key)) {
		return $array;
	}
	
    if (isset($array[$key])) {
        return $array[$key];
    }
	
    foreach (explode('.', $key) as $segment) {
        if (! is_array($array) || ! array_key_exists($segment, $array)) {
            return $default;
        }
		
        $array = $array[$segment];
    }
	
    return $array;
}

/**
 * Set an array item to a given value using "dot" notation
 *
 * If no key is given to the method, the entire array will be replaced
 *
 * @param array $array
 * @param string $key
 * @param mixed $value
 * @return array
 */
function arr_set(&$array, $key, $value)
{
	if (is_null($key)) {
		return $array = $value;
    }
	
	$keys = explode('.', $key);
	
	while (count($keys) > 1) {
        $key = array_shift($keys);
        
		if (! isset($array[$key]) || ! is_array($array[$key])) {
			$array[$key] = [];
		}
		
		$array = &$array[$key];
    }
	
	$array[array_shift($keys)] = $value;
	
	return $array;
}
