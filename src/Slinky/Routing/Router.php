<?php

namespace Slinky\Routing;

use Closure;
use Slinky\Http\Request;
use Slinky\Routing\Route;

class Router
{
	/**
	 * The request instance
	 * 
	 * @var \Slinky\Http\Request
	 */
	private $request;
	
	/**
	 * The currently url
	 * 
	 * @var string 
	 */
	private $url;
	
	/**
	 * The currently url parts
	 * 
	 * @var array 
	 */
	private $urlParts = [];
	
	/**
	 * The currently url parts count
	 * 
	 * @var int 
	 */
	private $urlPartsCount = 0;
	
	/**
	 * List of defined routes
	 * 
	 * @var array
	 */
	private $routes = [];
	
	/**
	 * List of defined route groups
	 * 
	 * @var array
	 */
	private $routeGroups = [];
	
	/**
	 * List of global middleware stack
	 * 
	 * @var array
	 */
	private $middlewares = [];
	
	/**
	 * List of applications route middleware
	 * 
	 * @var array
	 */
	private $routeMiddlewares = [];
	
	/**
	 * The currently dispatched route instance
	 * 
	 * @var \Slinky\Routing\Route
	 */
	private $route;
	
	/**
	 * The globally available parameter patterns
	 * 
	 * @var array
	 */
	private $patterns = [
		'-' => '[-]',
		'/' => '\/',
		'{all}' => '(.+)',
		'{slug}' => '[a-z0-9-]+',
		'{string}' => '[a-z-]+',
		'{int}' => '[1-9][0-9]*',
		'{id}' => '[1-9][0-9]*',
		'{page}' => '[1-9][0-9]*',
		'{year}' => '[12][0-9]{3}',
		'{month}' => '0[1-9]|1[012]',
		'{day}' => '0[1-9]|[12][0-9]|3[01]'
	];
	
	/**
	 * Create router instance
	 * 
	 * @param \Slinky\Http\Request $request
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
		
		$this->url = $this->request->get('url');
		
		$this->setUrlParts($this->url);
	}
	
	/**
	 * Match any route method
	 *
	 * @param string $link
	 * @param string $controller
	 * @param string $action
	 * @param array $parameters
	 * @return void
	 */
	public function any($link, $controller, $action, array $parameters = [])
	{
		$this->get($link, $controller, $action, $parameters);
		$this->post($link, $controller, $action, $parameters);
		$this->put($link, $controller, $action, $parameters);
		$this->delete($link, $controller, $action, $parameters);
	}
	
	/**
	 * Match multiple route methods
	 *
	 * @param array $methods
	 * @param string $link
	 * @param string $controller
	 * @param string $action
	 * @param array $parameters
	 * @return void
	 */
	public function match($methods, $link, $controller, $action, array $parameters = [])
	{
		foreach ($methods as $method) {
			$this->add(str_upper_case($method), $link, $controller, $action, $parameters);
		}
	}
	
	/**
	 * Match GET route method
	 *
	 * @param string $link
	 * @param string $controller
	 * @param string $action
	 * @param array $parameters
	 * @return Route
	 */
	public function get($link, $controller, $action, array $parameters = [])
	{
		return $this->add('GET', $link, $controller, $action, $parameters);
	}
	
	/**
	 * Match POST route method
	 *
	 * @param string $link
	 * @param string $controller
	 * @param string $action
	 * @param array $parameters
	 * @return Route
	 */
	public function post($link, $controller, $action, array $parameters = [])
	{
		return $this->add('POST', $link, $controller, $action, $parameters);
	}
	
	/**
	 * Match PUT route method
	 *
	 * @param string $link
	 * @param string $controller
	 * @param string $action
	 * @param array $parameters
	 * @return Route
	 */
	public function put($link, $controller, $action, array $parameters = [])
	{
		return $this->add('PUT', $link, $controller, $action, $parameters);
	}
	
	/**
	 * Match DELETE route method
	 *
	 * @param string $link
	 * @param string $controller
	 * @param string $action
	 * @param array $parameters
	 * @return Route
	 */
	public function delete($link, $controller, $action, array $parameters = [])
	{
		return $this->add('DELETE', $link, $controller, $action, $parameters);
	}
	
	/**
	 * Add single route to the router map - set route and regular expression of route
	 *
	 * @param string $method GET/POST/PUT/DELETE
	 * @param string $link
	 * @param string $controller
	 * @param string $action
	 * @param array $parameters
	 * @return \Slinky\Routing\Route
	 */
	private function add($method, $link, $controller, $action, array $parameters)
	{
        $link = $this->processGroupsPrefix() . '/' . trim($link, '/');
		$controller = $this->processGroupsNamespace() . $controller;
		$middlewares = $this->processGroupsMiddlewares();
		
		$route = new Route($method, $link, $controller, $action, $parameters, $middlewares);
		$this->routes[] = $route;
		
		return $route;
	}
	
	/**
	 * Route groups
	 * 
	 * @param array $params Group parameters: namespace, prefix, middleware
	 * @param \Closure $closure
	 * @return void
	 */
	public function group(array $params, Closure $closure)
	{
		$group = $this->pushGroup($params, $closure);
		$group();
		$this->popGroup();
	}
	
	/**
	 * Process route groups for url prefixes
	 * 
	 * @return string
	 */
	private function processGroupsPrefix()
	{
		$link = '';
        foreach ($this->routeGroups as $group) {
            $link .= $group->getPrefix();
        }
        return $link;
	}
	
	/**
	 * Process route groups for Controller namespaces
	 * 
	 * @return string
	 */
	private function processGroupsNamespace()
	{
		$namespace = '';
        foreach ($this->routeGroups as $group) {
            $namespace .= $group->getNamespace();
        }
        return $namespace;
	}
	
	/**
	 * Process route groups for middlewares
	 * 
	 * @return array
	 */
	private function processGroupsMiddlewares()
	{
		$middlewares = [];
		
        foreach ($this->routeGroups as $group) {
            $middlewares = array_merge($middlewares, $group->getMiddlewares());
        }
		
        return $middlewares;
	}
	
	/**
	 * Add a route group to the array
	 * 
	 * @param array $params Group parameters: namespace, prefix, middleware
	 * @param Closure $closure
	 * @return \Slinky\Routing\RouteGroup
	 */
	private function pushGroup(array $params, Closure $closure)
	{
		$group = new RouteGroup($params, $closure);
		array_push($this->routeGroups, $group);
		
		return $group;
	}
	
	/**
     * Removes the last route group from the array
     *
     * @return \Slinky\Routing\RouteGroup|bool The RouteGroup if successful, else false
     */
	private function popGroup()
	{
		$group = array_pop($this->routeGroups);
		
        return ($group instanceof RouteGroup ? $group : false);
	}
	
	/**
	 * Set applications global middlewares stack
	 * 
	 * @param array $middlewares
	 * @return void
	 */
	public function setMiddlewares(array $middlewares = [])
	{
		$this->middlewares = $middlewares;
	}
	
	/**
	 * Set applications route middleware stack
	 * 
	 * @param array $routeMiddlewares
	 * @return void
	 */
	public function setRouteMiddlewares(array $routeMiddlewares = [])
	{
		$this->routeMiddlewares = $routeMiddlewares;
	}
	
	/**
	 * Get applications global middleware stack
	 * 
	 * @return array
	 */
	public function getMiddlewares()
	{
		return $this->middlewares;
	}
	
	/**
	 * Get applications route middleware stack
	 * 
	 * @return array
	 */
	public function getRouteMiddlewares()
	{
		return $this->routeMiddlewares;
	}
	
	/**
	 * Create url parts from url with '/' delimiter
	 * @example part1/part2/ => [0] = part1, [1] = part2
	 *
	 * @param string $url
	 * @return void
	 */
	private function setUrlParts($url)
	{
		$this->urlParts = array_filter(explode('/', $url));
		$this->urlPartsCount = count($this->urlParts);
	}
	
	/**
	 * @return \Slinky\Routing\Route
	 */
	public function getRoute()
	{
		return $this->route;
	}
	
	/**
	 * @return array
	 */
	public function getUrlParts()
	{
		return $this->urlParts;
	}
	
	/**
	 * Get specific url part
	 * @example part1/part2/part3/ => [1] = part2
	 * 
	 * @param int $key
	 * @return string
	 */
	public function getUrlPart($key = 0)
	{
		return arr_get($this->urlParts, $key, '');
	}
	
	/**
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->routes;
	}
	
	/**
	 * Create regular expression from url
	 *
	 * @param string $url
	 * @return string
	 */
	private function createRegexp($url)
	{
		return '/^(' . ($url == '' || $url == '/' ? '\s*' : str_replace(array_keys($this->patterns), $this->patterns, $url)) . ')(\/|)$/';
	}
	
	/**
	 * Add regexp to pattern
	 *
	 * @param string $name
	 * @param string $regexp
	 * @return void
	 */
	public function pattern($name, $regexp)
	{
		$this->patterns['{' . $name . '}'] = $regexp;
	}
	
	/**
	 * Create arguments with real keys and values
	 *
	 * @param \Slinky\Routing\Route $route
	 * @return array
	 */
	private function getArguments(Route $route)
	{
		$ret = [];
		
		if (empty($route->getParameters())) {
			return $ret;
		}
		
		foreach ($route->getParameters() as $param => $position) {
			$ret[$param] = $this->getUrlPart($position);
		}
		
		return $ret;
	}
	
	/**
	 * Match route with url and request method
	 * 
	 * @param \Slinky\Routing\Route $route
	 * @return bool
	 */
	private function matchRoute(Route $route)
	{
		$url = '/' . $this->url;
		
		return (($route->getUrl() == $url || preg_match($this->createRegexp($route->getUrl()), $url)) && $this->request->server('REQUEST_METHOD') == $route->getMethod());
	}
	
	/**
	 * Get the controller, action and parameters based on the current url
	 *
	 * @throws \Slinky\Routing\NotFoundException
	 * @return bool
	 */
	public function route()
	{
		foreach ($this->routes as $route) {
			if ($this->matchRoute($route)) {
				$arguments = $this->getArguments($route);
				$route->setArguments($arguments);
				
				$this->route = $route;

				return true;
			}
		}
		
		throw new NotFoundException('Route with url "'.$this->url.'" was not found.');
	}
}
