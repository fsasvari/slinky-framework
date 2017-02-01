<?php

namespace Slinky\Model;

class Pivot extends Entity
{
	/**
     * The parent model of the relationship
     *
     * @var \Slinky\Model\Entity
     */
	protected $parent;
	
	/**
     * The name of the foreign key column
     *
     * @var string
     */
	protected $foreignKey;
	
	/**
     * The name of the "other key" column
     *
     * @var string
     */
	protected $otherKey;
	
	/**
     * Create a new pivot model instance
     *
     * @param \Slinky\Model\Entity $parent
     * @param string $table
     * @return void
     */
	public function __construct(Entity $parent, $table)
	{
		$this->parent = $parent;
		$this->table = $table;
	}
	
	/**
     * Get the foreign key column name
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
	
    /**
     * Get the "other key" column name
     *
     * @return string
     */
    public function getOtherKey()
    {
        return $this->otherKey;
    }
}
