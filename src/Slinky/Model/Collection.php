<?php

namespace Slinky\Model;

use IteratorAggregate;
use Slinky\Pagination\Pagination;
use Slinky\Exception\Core\InvalidArgumentException;

class Collection implements IteratorAggregate
{
	/**
	 * The objects contained in the collection
	 * 
	 * @var array
	 */
	private $objects = [];
	
	/**
	 * The pagination instance
	 * 
	 * @var \Slinky\Pagination\Pagination
	 */
	private $pagination;
	
	/**
	 * Iterate objects array
	 * 
	 * @return array
	 */
	public function getIterator()
	{
		foreach ($this->objects as $object) {
			yield $object;
		}
	}
	
	/**
	 * Import array of objects into collection
	 * 
	 * @param array $objects
	 * @return void
	 */
	public function import(array $objects = [])
	{
		foreach ($objects as $key => $object) {
			$this->set($object, $key);
		}
	}
	
	/**
	 * Add object to collection
	 * 
	 * @param Object $object
	 * @param string $key
	 * @return void
	 */
	public function set($object, $key = null)
	{
		if ($key == null) {
			$this->objects[] = $object;
		} elseif ($this->exists($key)) {
            throw new InvalidArgumentException('Key ' . $key . ' already in use in collection.');
        } else {
            $this->objects[$key] = $object;
        }
	}
	
	/**
	 * Get specific object or all objects from collection
	 * 
	 * @param string $key
	 * @return object|bool
	 */
	public function get($key = null)
	{
		if ($key === null) {
			return $this->getAll();
		} elseif ($this->exists($key)) {
			return $this->objects[$key];
		} else {
			return false;
		}
	}
	
	/**
	 * Get all objects from collection
	 * 
	 * @return array
	 */
	public function getAll()
	{
		return $this->objects;
	}
	
	/**
	 * Delete object from collection
	 * 
	 * @param string $key
	 * @return void
	 */
	public function remove($key)
	{
		if ($this->exists($key)) {
			unset($this->objects[$key]);
		} else {
			throw new InvalidArgumentException('Invalid key ' . $key . ' in collection.');
		}
	}
	
	/**
	 * Check if key exists in collection
	 * 
	 * @param string $key
	 * @return int
	 */
	public function exists($key)
	{
		 return isset($this->objects[$key]);
	}
	
	/**
	 * Chunk objects in collection array
	 * 
	 * @param int $size
	 * @return array
	 */
	public function chunk($size)
	{
		return array_chunk($this->objects, $size);
	}
	
	/**
	 * Clear whole object collection
	 * 
	 * @return void
	 */
	public function clear()
	{
		$this->objects = [];
	}
	
	/**
	 * Get number of objects in collection
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->objects);
	}
	
	/**
	 * Set pagination
	 * 
	 * @param \Slinky\Pagination\Pagination $pagination
	 * @return void
	 */
	public function setPagination(Pagination $pagination)
	{
		$this->pagination = $pagination;
	}
	
	/**
	 * Get pagination
	 * 
	 * @return \Slinky\Pagination\Pagination
	 */
	public function getPagination()
	{
		return $this->pagination;
	}
}
