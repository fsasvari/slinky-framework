<?php

namespace Slinky\Model\ORM;

use Slinky\Exception\Core\InvalidArgumentException;
use Slinky\Model\Model;

class Mapper
{
	/**
	 * List of protected keys
	 * 
	 * @var array
	 */
	private $protected_keys = ['connection', 'table', 'primary', 'hidden', 'original', 'pages', 'hasOne', 'hasMany', 'belongsTo', 'belongsToMany'];
	
	/**
	 * Get protected keys
	 * 
	 * @return array
	 */
	private function getProtectedKeys()
	{
		return $this->protected_keys;
	}
	
	/**
	 * Fill object with array data
	 * 
	 * @param \Slinky\Model\Model $entity
	 * @param array $data
	 * @return Entity
	 */
	public function fromArray(Model $entity, $data)
	{
		$array = $this->prepareFromData($entity, $data);
		
		foreach ($array as $key => $value) {		
			if (array_key_exists($key, $entity->getBelongsTo())) {
				$class = $this->fromArray($entity->getBelongsTo($key), $value);
				$entity->{str_camel_case('set' . $key)}($class);
			} elseif (array_key_exists($key, $entity->getHasOne())) {
				$class = $this->fromArray($entity->getHasOne($key), $value);
				$entity->{str_camel_case('set' . $key)}($class);
			} elseif (in_array($key, $entity->getHasMany())) {
				$entity->{'set' . $key}($value);
			} elseif ($key != 'id' || ($key == 'id' && !$entity->getId())) {
				$method = str_camel_case('set ' . $key);

				if (is_callable($entity->$method($value))) {
					$this->$method($value);
				}
			} else {
				throw new InvalidArgumentException('Invalid variable `' . $key . '` with value `' . $value . '`.');
			}
		}
		
		return $entity;
	}
	
	
	/**
	 * Convert object to array
	 * 
	 * @param \Slinky\Model\Model $model
	 * @return array
	 */
	public function toArray(Model $model)
	{
		$temp_array = (array) $model;
				
		$array = array();
		foreach ($temp_array as $key => $value) {
			if ($value || $value == '0') {
				$key = preg_match('/^\x00(?:.*?)\x00(.+)/', $key, $matches) ? $matches[1] : $key;
				
				if (!in_array($key, array_merge($this->getProtectedKeys(), $entity->getHasMany())) && !array_key_exists($key, array_merge($entity->getHasOne(), $entity->getBelongsTo()))) {
					$array[$key] = $value;
				}
			}
		}
		
		return $array;
	}
	
	
	/**
	 * Prepare previosuly fetched data for inserting into Entity object
	 * 
	 * @param Entity $entity
	 * @param array $data
	 * @return array
	*/
	private function prepareFromData($entity, $data = array())
	{
		$array = array();
		
		//$data_filtered = array_filter($data);

		foreach ($data as $key => $value) {
			if (($value || $value == '0') && !in_array($key, $this->getProtectedKeys())) {
				$table = '';

				foreach (array_keys(array_merge($entity->getBelongsTo(), $entity->getHasOne())) as $k) {
					$pos = strpos($key, $k . '_');

					if ($pos !== false && $pos === 0) {
						$table = $k;
						break;
					}
				}

				if ($table) {
					if ($pos !== false && $pos === 0) {
						$key = substr_replace($key, '', $pos, strlen($table) + 1);
						if ($key == '') {
							$key = $table;
						} elseif ($key == 'id') {
							$array[$table . '_' . $key] = $value;
						}
					}

					$array[$table][$key] = $value;

				} elseif (array_key_exists($key, array_merge($entity->getBelongsTo(), $entity->getHasOne()))) {
					$array[$key][$key] = $value;
				} else {
					$array[$key] = $value;
				}
			}
		}
		
		
		
		return $array;
	}
}
