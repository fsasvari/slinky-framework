<?php

namespace Slinky\Routing;

use Slinky\Routing\Router;
use Slinky\Container\Container;

class Dispatcher
{
	/**
	 * Router instance
	 * 
	 * @var \Slinky\Routing\Router
	 */
	private $router;
	
	/**
	 * DI Container instance
	 * 
	 * @var \Slinky\Countainer\Container
	 */
	private $container;
	
	/**
	 * @param \Slinky\Routing\Router $router
	 * @param \Slinky\Countainer\Container $container
	 * @return void
	 */
	public function __construct(Router $router, Container $container)
	{
		$this->router = $router;
		$this->container = $container;
	}
	
	/**
	 * Load the controller file and action with params => {string}Controller.php?action={string}&params={array}
	 *
	 * @return void
	 */
	public function dispatch()
	{
		$controller = $this->container->get('App\\Controller\\' . $this->router->getRoute()->getController() . 'Controller');
		$action = $this->router->getRoute()->getAction();
		
		if ($this->menageMiddlewares()) {
			return $this->container->getMethod($controller, $action, $this->router->getRoute()->getArguments());
		}
	}
	
	/**
	 * Menage all middlewares in route
	 * 
	 * @return void
	 */
	private function menageMiddlewares()
	{
		$middlewares = array_merge($this->router->getMiddlewares(), $this->router->getRoute()->getMiddlewares());
		
		foreach ($middlewares as $middlewareName) {
			$middleware = $this->container->get($middlewareName);
			
			if (! $middleware->handle()) {
				return false;
			}
		}
		
		return true;
	}
}
