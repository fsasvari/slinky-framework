<?php

namespace Slinky\Model;

use Slinky\Exception\Core\InvalidArgumentException;

abstract class Model
{
	/**
	 * The connection name for the model
	 *
	 * @var string
	 */
	protected $connection;
	
	/**
	 * The table associated with the model
	 *
	 * @var string
	 */
	protected $table;
	
	/**
	 * The primary key for the model
	 * 
	 * @var string
	 */
	protected $primary = 'id';
	
    /**
     * The model attribute's original state.
     *
     * @var array
     */
    protected $original = [];
	
	/**
	 * Array of hidden fields
	 * 
	 * @var array
	 */
	protected $hidden = [];
	
	/**
	 * Number of pages
	 * 
	 * @var int
	 */
	protected $pages = 10;
	
	/**
	 * Has one relationship
	 * 
	 * @var array
	 */
	protected $hasOne = [];
	
	/**
	 * Has many relationship
	 * 
	 * @var array
	 */
	protected $hasMany = [];
	
	/**
	 * Belongs to relationship
	 * 
	 * @var array
	 */
	protected $belongsTo = [];
	
	/**
	 * Belongs to many relationship
	 * 
	 * @var array
	 */
	protected $belongsToMany = [];
	
	/**
	 * Primary key value
	 * 
	 * @var int
	 */
	protected $id;
	
	/**
	 * Create a new entity model
	 * 
	 * @param array $attributes
	 * @param bool $saveToOriginal Save to original attributes array
	 * @return void
	 */
	public function __construct(array $attributes = [], $saveToOriginal = false)
	{
		$this->import($attributes, $saveToOriginal);
	}
	
	/**
	 * Get connection name
	 * 
	 * @return string
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * Get table name
	 * 
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}
	
	/**
	 * Get primary key
	 * 
	 * @return string
	 */
	public function getPrimary()
	{
		return $this->primary;
	}
	
	/**
	 * Get hidden variables
	 * 
	 * @return array
	 */
	public function getHidden()
	{
		return $this->hidden;
	}
	
	/**
	 * Get all has-one relationships
	 * 
	 * @param string $key
	 * @return array
	 */
	public function getHasOne($key = '')
	{
		if ($key && arr_has($this->hasOne, $key)) {
			return arr_get($this->hasOne, $key);
		} else {
			return $this->hasOne;
		}
	}
	
	/**
	 * Get all has-many relationships
	 * 
	 * @return array
	 */
	public function getHasMany()
	{
		return $this->hasMany;
	}
	
	/**
	 * Get all belongs-to relationships
	 * 
	 * @param string $key
	 * @return array
	 */
	public function getBelongsTo($key = '')
	{
		if ($key && arr_has($this->belongsTo, $key)) {
			return arr_get($this->belongsTo, $key);
		} else {
			return $this->belongsTo;
		}
	}
	
	/**
	 * Get all belongs-to-many relationships
	 * 
	 * @return array
	 */
	public function getBelongsToMany()
	{
		return $this->belongsToMany;
	}
	
	/**
     * Get the default foreign key name for the model
     *
     * @return string
     */
	public function getForeignKey()
	{
		return str_snake_case($this->table) . '_id';
	}
	
	/**
	 * Import data array to model
	 * 
	 * @param array $attributes
	 * @param bool $saveToOriginal
	 * @return void
	 */
	protected function import(array $attributes = [], $saveToOriginal = false)
	{
		foreach ($attributes as $key => $value) {
			$this->set($key, $value, $saveToOriginal);
		}
	}
	
	/**
	 * Set attribute data
	 * 
	 * @param type $key
	 * @param type $value
	 * @param type $saveToOriginal
	 * @return type
	 */
	protected function set($key, $value, $saveToOriginal)
	{
		$method = 'set'.str_studly_caps($key);
		
		if ($saveToOriginal) {
			$this->original[$key] = $value;
		}
		
		return $this->{$method}($value);
	}
	
	/**
	 * Get model id value
	 * 
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set mode id value
	 * 
	 * @param int $id
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function setId($id)
	{
		if ($this->getId()) {
			throw new InvalidArgumentException('Cannot change already set ID value from "'.$this->getId().'" to "'.$id.'".');
		}
		
		$this->id = $id;
	}
}
