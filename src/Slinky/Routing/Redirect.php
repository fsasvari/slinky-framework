<?php

namespace Slinky\Routing;

use Slinky\Routing\Router;
use Slinky\Http\Request;
use Slinky\Session\Session;
use Slinky\Exception\Core\InvalidArgumentException;

class Redirect
{
	/**
	 * The router instance
	 * 
	 * @var \Slinky\Routing\Router
	 */
	private $router;
	
	/**
	 * The request instance
	 * 
	 * @var \Slinky\Http\Request
	 */
	private $request;
	
	/**
	 * The session instance
	 * 
	 * @var \Slinky\Session\Session
	 */
	private $session;
	
	/**
	 *
	 * @var type 
	 */
	private $url;
	
	/**
	 * Create new redirect instance
	 * 
	 * @param \Slinky\Routing\Router $router
	 * @param \Slinky\Http\Request $request
	 * @param \Slinky\Session\Session $session
	 * @return void
	 */
	public function __construct(Router $router, Request $request, Session $session)
	{
		$this->router = $router;
		$this->request = $request;
		$this->session = $session;
	}
	
	/**
	 * Redirect request based on url
	 * 
	 * @param string $url
	 * @param array $params
	 * @return $this
	 */
	public function url($url, $params = [])
	{
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			$this->url = $url . ($params ? '?' . http_build_query($params) : '');
		} else {
			throw new InvalidArgumentException('"' . $url . '" is not valid url.');
		}
		
		return $this;
	}
	
	/**
	 * Redirect request based on route name
	 * 
	 * @param string $route
	 * @param array $params
	 * @param bool $params_type
	 * @return $this
	 */
	public function route($route = '', $params = [], $params_type = true)
	{
		foreach ($this->router->getRoutes() as $key => $router) {
			if ($router->getMethod() == 'GET' && $route == $key) {
				$this->setUrl($router, $params, $params_type);
				break;
			}
		}
		
		return $this;
	}
	
	/**
	 * Redirect request based or controller and action
	 * 
	 * @param string $controller
	 * @param string $action
	 * @param array $params
	 * @param bool $params_type
	 * @return $this
	 */
	public function action($controller, $action = 'index', $params = [], $params_type = true)
	{
		foreach ($this->router->getRoutes() as $router) {
			if ($router->getMethod() == 'GET' && $router->getController() == $controller && $router->getAction() == $action) {
				$this->setUrl($router, $params, $params_type);
				break;
			}
		}
		
		return $this;
	}
	
	/**
	 * Redirect request based on last visited url
	 * 
	 * @return $this
	 */
	public function back()
	{
		$referer = $this->request->server('HTTP_REFERER');
		
		$this->url = ($referer ? $referer : $this->request->getBaseUrl());
		
		return $this;
	}
	
	/**
	 * Serialize and save data to flash session
	 * 
	 * @param array $data
	 * @return $this
	 */
	public function with(array $data = [])
	{
		foreach ($data as $key => $value) {
			$this->session->flash($key, serialize($value));
		}
		
		return $this;
	}
	
	/**
	 * Set url from route with params
	 * 
	 * @param \Slinky\Routing\Route $route
	 * @param array $params
	 * @param bool $params_type
	 */
	private function setUrl($route, $params, $params_type)
	{		
		if ($params_type) {
			$this->url = $this->request->getBaseUrl() . $route->getUrl() . '/' . ($params ? '?' . http_build_query($params) : '');
		} else {
			foreach ($route->getParams() as $key => $param) {
				$this->url = $this->request->getBaseUrl() . str_replace('{' . $key . '}', $param, $route->getUrl());
			}
		}
	}
	
	/**
	 * Get redirect url
	 * 
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}
}
