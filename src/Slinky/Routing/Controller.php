<?php

namespace Slinky\Routing;

use Slinky\Http\Response;

abstract class Controller
{
	/**
	 * Response class
	 * 
	 * @var \Slinky\Http\Response
	 */
	protected $response;
	
	/**
	 * Create a new Controller instance
	 * 
	 * @param \Slinky\Http\Response $response
	 * @return void
	 */
	public function __construct(Response $response)
	{
		$this->response = $response;
	}
}
