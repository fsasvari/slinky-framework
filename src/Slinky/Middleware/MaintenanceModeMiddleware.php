<?php

namespace Slinky\Middleware;

use Slinky\Config\Config;
use Slinky\Http\Response;

class MaintenanceModeMiddleware
{
	/**
	 * Config instance
	 * 
	 * @var \Slinky\Config\Config;
	 */
	protected $config;
	
	/**
	 * Response instance
	 * 
	 * @var \Slinky\Http\Response;
	 */
	protected $response;
	
	/**
	 * Create a new check for maintenance mode middleware
	 * 
	 * @param \Slinky\Config\Config $config
	 * @param \Slinky\Http\Response $response
	 * @return void
	 */
	public function __construct(Config $config, Response $response)
	{
		$this->config = $config;
		$this->response = $response;
	}
	
	/**
	 * Handle middleware
	 * 
	 * @return bool
	 */
	public function handle()
	{
		if ($this->config->get('app.maintenance')) {
			$this->response->error(503);
			
			return false;
		}
		
		return true;
	}
}
