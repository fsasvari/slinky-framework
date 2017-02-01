<?php

namespace Slinky\Routing;

use Closure;

class RouteGroup
{
	/**
	 * Route group prefix
	 * 
	 * @var string
	 */
	private $prefix;
	
	/**
	 * Route group namespace
	 * 
	 * @var string
	 */
	private $namespace;
	
	/**
	 * List of route group middlewares
	 * 
	 * @var array
	 */
	private $middlewares = [];
	
	/**
	 * Route group closure
	 * 
	 * @var \Closure
	 */
	private $closure;
	
	/**
     * Create a new route group instance
     *
     * @param array $params Group parameters: prefix, namespace, middleware
     * @param \Closure $closure
	 * @return void
     */
	public function __construct($params, Closure $closure)
	{
		$this->setPrefix(arr_get($params, 'prefix'));
		$this->setNamespace(arr_get($params, 'namespace'));
		$this->setMiddlewares(arr_get($params, 'middleware'));
		
        $this->closure = $closure;
	}
	
	/**
	 * Get group prefix
	 * 
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix ? '/' . $this->prefix : '';
	}
	
	/**
	 * Get group namespace
	 * 
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace ? '/' . $this->namespace : '';
	}
	
	/**
	 * Get route middlewares
	 * 
	 * @return array
	 */
	public function getMiddlewares()
	{
		return $this->middlewares;
	}
	
	/**
	 * Set group prefix
	 * 
	 * @param string $prefix
	 * @return void
	 */
	private function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}
	
	/**
	 * Set group namespace
	 * 
	 * @param string $namespace
	 * @return void
	 */
	private function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}
	
	/**
	 * Set group middlewares
	 * 
	 * @param array $middlewares
	 * @return void
	 */
	private function setMiddlewares($middlewares)
	{
		$this->middlewares = (array) $middlewares;
	}
	
	/**
     * Invoke the group to register any Routable objects within it
     *
     * @return void
     */
    public function __invoke()
    {
        $closure = $this->closure;
        $closure();
    }
}