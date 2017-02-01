<?php

namespace Slinky\Middleware;

use Slinky\Http\Request;
use Slinky\Encryption\Crypt;
use Slinky\Session\TokenMismatchException;

class CsrfTokenMiddleware
{
	/**
	 * Request instance
	 * 
	 * @var \Slinky\Http\Request;
	 */
	protected $request;
	
	/**
	 * Crypt instance
	 * 
	 * @var \Slinky\Encryption\Crypt;
	 */
	protected $crypt;
	
	/**
	 * The URIs that should be excluded from CSRF vertification
	 * 
	 * @var array
	 */
	protected $except = [];
	
	/**
	 * Create a new CSRF validation token middleware instance
	 * 
	 * @param \Slinky\Http\Request $request
	 * @param \Slinky\Encryption\Crypt $crypt
	 * @return void
	 */
	public function __construct(Request $request, Crypt $crypt)
	{
		$this->request = $request;
		$this->crypt = $crypt;
	}
	
	/**
	 * Handle middleware
	 * 
	 * @return bool
	 * 
	 * @throws \Slinky\Session\TokenMismatchException
	 */
	public function handle()
	{
		if ($this->isReading() || $this->shouldPassThrough() || $this->tokensMatch()) {
			return true;
		}
		
		throw new TokenMismatchException();
	}
	
	/**
	 * Determine if the request has a URI that should pass through CSRF verification
	 * 
	 * @return bool
	 */
	protected function shouldPassThrough()
	{
		foreach ($this->except as $except) {
			if ($this->request->get('url') == $except) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Determine if the session and input SCRF tokens match
	 * 
	 * @return bool
	 */
	protected function tokensMatch()
	{
		$sessionToken = $this->request->session()->get('token');
		
		$token = $this->request->input('_token') ?: $this->request->header('X-CSRF-TOKEN');
		
		if (! is_string($sessionToken) || ! is_string($token)) {
            return false;
        }
		
		return hash_equals($sessionToken, $token);
	}
	
	/**
	 * Determine if the HTTP request is read
	 * 
	 * @return bool
	 */
	protected function isReading()
	{
		return in_array($this->request->getMethod(), ['GET']);
	}
}
