<?php

namespace Slinky\Http;

class Request
{
	/**
	 * The header instance
	 * 
	 * @var \Slinky\Http\Header
	 */
	private $header;
	
	/**
	 * Create new request instance
	 * 
	 * @param \Slinky\Http\Header $header
	 * @return void
	 */
	public function __construct(Header $header)
	{
		$this->header = $header;
	}
	
	/**
	 * Get $_GET global variable
	 * 
	 * @param string $name
	 * @return string
	 */
	public function get($name)
	{
		return (isset($_GET[$name]) ? $_GET[$name] : false);
	}
	
	/**
	 * Get $_POST global variable
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function post($name)
	{
		return (isset($_POST[$name]) ? $_POST[$name] : false);
	}
	
	/**
	 * Alias for post() method
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function input($name)
	{
		return $this->post($name);
	}
	
	/**
	 * Get header instance
	 * 
	 * @return \Slinky\Http\Header
	 */
	public function header()
	{
		return $this->header;
	}
	
	/**
	 * Get body request
	 * 
	 * @return mixed
	 */
	public function body()
	{
		$body = file_get_contents('php://input');
		
		return (is_object(json_decode($body)) ? json_decode($body, true) : $body);
	}
	
	/**
	 * Get uploaded file
	 * 
	 * @param string $name
	 * @return string
	 */
	public function file($name)
	{
		return (isset($_FILES[$name]) ? $_FILES[$name] : false);
	}
	
	/**
	 * Get $_SERVER global variable
	 * 
	 * @param string $name
	 * @return string
	 */
	public function server($name)
	{
		return (isset($_SERVER[$name]) ? $_SERVER[$name] : false);
	}
	
	/**
	 * Get $_ENV global variable
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @return string
	 */
	public function env($name, $default = null)
	{
		return env($name, $default);
	}
	
	/**
	 * Get client IP address
	 * 
	 * @return string
	 */
	public function getClientIp()
	{
		if ($this->server('HTTP_CLIENT_IP')) {
			$ip = $this->server('HTTP_CLIENT_IP');
		} elseif ($this->server('HTTP_X_FORWARDED_FOR')) {
			$ip = $this->server('HTTP_X_FORWARDED_FOR');
		} elseif ($this->server('REMOTE_ADDR')) {
			$ip = $this->server('REMOTE_ADDR');
		} else {
			$ip = '';
		}
		
		return $ip;
	}
	
	/**
	 * Get server request method
	 * 
	 * @return string
	 */
	public function getMethod()
	{
		return $this->server('REQUEST_METHOD');
	}
	
	/**
	 * Determine if the request is the result of an AJAX call
	 * 
	 * @return bool
	 */
	public function isAjax()
	{
		return ! empty($this->server('HTTP_X_REQUESTED_WITH')) && strtolower($this->server('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest';
	}
	
	/**
	 * Determine if the request is sending JSON
	 * 
	 * @return bool
	 */
	public function isJson()
	{
		return str_contains($this->header()->get('CONTENT_TYPE'), ['/json', '+json']);
	}
	
	/**
	 * Determine if the request is over HTTPS
	 * 
	 * @return bool
	 */
	public function isSecure()
	{
		return $this->server('HTTPS') && $this->server('HTTPS') == 'on';
	}
	
	/**
	 * Get base url
	 * 
	 * @return string
	 */
	public function getBaseUrl()
	{
		return ($this->isSecure() ? 'https' : 'http') . '://' . $this->server('SERVER_NAME') . str_replace([$this->get('url'), 'public/', 'index.php'], '', $this->server('REQUEST_URI'));
	}
}
