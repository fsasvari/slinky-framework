<?php

namespace Slinky\Model;

use Slinky\Pagination\Pagination;
use Slinky\Model\Collection;

class CollectionFactory
{
	private $pagination;
	
	
	/**
	 * @param Pagination $pagination
	 * @return void
	 */
	public function __construct(Pagination $pagination)
	{
		$this->pagination = $pagination;
	}
	
	
	/**
	 * Build Collection object
	 *
	 * @return object
	 */
	public function build()
	{
		return new Collection($this->pagination);
	}
}
