<?php

namespace Slinky\Routing;

class Route
{
	/**
	 * The HTTP method the route responds to
	 * 
	 * @var string
	 */
	private $method;
	
	/**
	 * The URL pattern the route responds to
	 * 
	 * @var string 
	 */
	private $url;
	
	/**
	 * The controller
	 * 
	 * @var string
	 */
	private $controller;
	
	/**
	 * The action method
	 * 
	 * @var string
	 */
	private $action;
	
	/**
	 * List of parameters
	 * 
	 * @var array
	 */
	private $parameters = [];
	
	/**
	 * List of finished arguments with keys and values
	 * 
	 * @var array
	 */
	private $arguments = [];
	
	/**
	 * List of middlewares
	 * 
	 * @var array
	 */
	private $middlewares = [];
	
	/**
	 * Create route instance
	 * 
	 * @param string $method
	 * @param string $url
	 * @param string $controller
	 * @param string $action
	 * @param array $parameters
	 * @return void
	 */
	public function __construct($method, $url, $controller, $action, array $parameters, array $middlewares)
	{
		$this->method = $method;
		$this->url = $url;
		$this->controller = $controller;
		$this->action = $action;
		$this->parameters = $parameters;
		$this->middlewares = $middlewares;
	}
	
	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}
	
	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}
	
	/**
	 * @return string
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}
	
	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}
	
	/**
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}
	
	/**
	 * @return array
	 */
	public function getMiddlewares()
	{
		return $this->middlewares;
	}
	
	/**
	 * @param array $arguments
	 * @return void
	 */
	public function setArguments(array $arguments)
	{
		$this->arguments = $arguments;
	}
}
