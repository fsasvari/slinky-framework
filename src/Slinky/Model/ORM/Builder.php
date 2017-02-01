<?php

namespace Slinky\Model\ORM;

use Slinky\Database\Database;
use Slinky\Model\ORM\Mapper;
use Slinky\Model\ModelFactory;
use Slinky\Model\CollectionFactory;
use Slinky\Model\Model;

class Builder
{
	/**
	 * The database instance
	 * 
	 * @var \Slinky\Database\Database
	 */
	private $database;
	
	/**
	 * The mapper instance
	 * 
	 * @var \Slinky\Model\ORM\Mapper
	 */
	private $mapper;
	
	/**
	 * The model factory instance
	 * 
	 * @var \Slinky\Model\ModelFactory
	 */
	private $modelFactory;
	
	/**
	 * The collection factory instance
	 * 
	 * @var \Slinky\Model\CollectionFactory
	 */
	private $collectionFactory;
	
	/**
	 * Name of the model instance
	 * 
	 * @var string
	 */
	private $modelName;
	
	/**
	 * The model instance
	 * 
	 * @var \Slinky\Model\Model
	 */
	private $model;
	
	/**
	 * Instance of query builder
	 * 
	 * @var \Slinky\Database\Query\Builder
	 */
	private $query;
	
	/**
	 * List of one-to-many joins
	 * 
	 * @var array
	 */
	private $joins = [];
	
	/**
	 * Create new ORM builder instance
	 * 
	 * @param \Slinky\Database\Database $database
	 * @param \Slinky\Model\ORM\Mapper $mapper
	 * @param \Slinky\Model\ModelFactory $modelFactory
	 * @param \Slinky\Model\CollectionFactory $collectionFactory
	 * @return void
	*/
	public function __construct(Database $database, Mapper $mapper, ModelFactory $modelFactory, CollectionFactory $collectionFactory)
	{
		$this->database = $database;
		$this->mapper = $mapper;
		$this->modelFactory = $modelFactory;
		$this->collectionFactory = $collectionFactory;
	}
	
	/**
	 * Build new model instance
	 * 
	 * @param type $modelName
	 * @return $this
	 */
	public function build($modelName)
	{
		$this->modelName = $modelName;
		
		$model = $this->modelFactory->build($modelName);
		$this->model = $model;
		
		$this->query = $this->database->table($this->getDatabaseName().'.'.$model->getTable());
		
		$this->query->select($this->getDatabaseName().'.'.$model->getTable().'.*');
		
		return $this;
	}
	
	/**
	 * Set new join model name
	 * 
	 * @param string $modelName
	 * @return $this
	 */
	public function with($modelName)
	{
		$model = $this->modelFactory->build($modelName);

		$this->query->join($this->getDatabaseName($model).'.'.$model->getTable(), $this->getDatabaseName($model).'.'.$model->getTable().'.'.$this->model->getForeignKey().' = '.$this->getDatabaseName().'.'.$this->model->getTable().'.'.$this->model->getPrimary());
		
		return $this;
	}
	
	/**
	 * Set new join many model name
	 * 
	 * @param string $modelName
	 * @param string $where
	 * @param array $bind
	 * @return $this
	 */
	public function withMany($modelName, $where = '', array $bind = [])
	{
		$joinModel = arr_get($this->model->getHasMany(), $modelName);
		
		if (!$joinModel || !$joinModel[0]) {
			throw new Exception('ne valja');
		}
		
		$this->joins[$modelName] = [
			'model' => $this->modelFactory->build($joinModel[0]),
			'where' => $where,
			'bind' => $bind
		];
		
		return $this;
	}
	
	/**
	 * Set the "limit" value
	 * 
	 * @param int $value
	 * @return $this
	 */
	public function limit($value)
	{
		$this->query->limit($value);
		
        return $this;
	}
	
	/**
     * Set the "offset" value
     *
     * @param int $value
     * @return $this
     */
    public function offset($value)
    {
        $this->query->offset($value);
		
        return $this;
    }
	
	/**
	 * Get all results from database
	 * 
	 * @return \Slinky\Model\Collection|false
	 */
	public function get()
	{
		$data = $this->query->get();
		
		if ($data) {
			$collection = $this->collectionFactory->build();
			
			foreach ($data as $row) {
				$entity = $this->modelFactory->build($this->modelName, $row);
				$collection->set($entity);
			}
			
			return $collection;
		}
		
		return false;
	}
	
	/**
	 * Alias for get() method - Get all results from database
	 * 
	 * @return \Slinky\Model\Collection|false
	 */
	public function all()
	{
		return $this->get();
	}
	
	/**
	 * Get first result from database
	 * 
	 * @return \Slinky\Model\Model|false
	 */
	public function first()
	{
		$data = $this->query->first();
		
		if ($data) {
			$entity = $this->modelFactory->build($this->modelName, $data);
			
			return $entity;
		}
		
		return false;
	}
	
	/**
	 * Get connection name
	 * 
	 * @param \Slinky\Model\Model $model
	 * @return string
	 */
	private function getDatabaseName(Model $model = null)
	{
		if ($model) {
			return $this->database->connection($model->getConnection())->getDatabaseName();
		}
		
		return $this->database->connection($this->model->getConnection())->getDatabaseName();
	}
}
