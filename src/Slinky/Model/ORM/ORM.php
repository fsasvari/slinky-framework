<?php

namespace Slinky\Model\ORM;

use Exception;
use Slinky\Database\Database;
use Slinky\Model\ORM\Mapper;
use Slinky\Model\ModelFactory;
use Slinky\Model\CollectionFactory;
use Slinky\Model\ORM\Builder;
use Slinky\Model\Model;

class ORM
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
	 * Create new ORM instance
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
	 * Get a new Builder instance
	 * 
	 * @return \Slinky\Model\ORM\Builder
	 */
	private function builder()
	{
		return new Builder($this->database, $this->mapper, $this->modelFactory, $this->collectionFactory);
	}
	
	/**
	 * Set new model name
	 * 
	 * @param string $modelName
	 * @return \Slinky\Model\ORM\Builder
	 */
	public function model($modelName)
	{
		return $this->builder()->build($modelName);
	}
	
	/**
	 * Insert or update row in database
	 * 
	 * @param \Slinky\Model\Model $model
	 * @return int|void Return inserted id on insert, affected rows on update
	 */
	public function save(Model $model)
	{
		$data = $this->mapper->toArray($model);
		
		if ($model->getId()) {
			unset($data['id']);
			return $this->database->table($model->getTable())->update($data, $model->getId());
		}
		
		return $this->database->table($model->getTable())->insert($data);
	}
	
	/**
	 * Delete row from database where model id
	 * 
	 * @param \Slinky\Model\Model $model
	 * @return bool
	 */
	public function delete(Model $model)
	{
		if (is_null($model->getPrimary())) {
            throw new Exception('No primary key defined on model.');
        }
		
		if ($model->getId()) {
			$this->database->table($model->getTable())->delete($model->getId());
			
			return true;
		}
		
		return false;
	}
}
