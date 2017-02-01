<?php

namespace Slinky\Slinky;

use ErrorException;
use Slinky\Exception\ExceptionHandler;
use Slinky\Config\Config;

class HandleExceptions
{
	/**
	 * The exception handler instance
	 * 
	 * @var \Slinky\Exception\Handler 
	 */
	private $handler;
	
	/**
	 * The configuration instance
	 * @var \Slinky\Config\Config
	 */
	private $config;
	
	/**
	 * Create a new handle exceptions instance
	 * 
	 * @param \SLinky\Exception\ExceptionHandler $handler
	 * @param \Slinky\Config\Config $config
	 * @return void
	 */
	public function __construct(ExceptionHandler $handler, Config $config)
	{
		$this->handler = $handler;
		$this->config = $config;
		
		error_reporting(E_ALL);
		
		set_error_handler([$this, 'handleError']);
		
		set_exception_handler([$this, 'handleException']);
		
		register_shutdown_function([$this, 'handleShutdown']);
		
		ini_set('display_errors', 'Off');
	}
	
	/**
     * Convert a PHP error to an ErrorException
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() && $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }
	
	/**
	 * Handle an uncaught exception from the application.
	 * 
	 * @param \Throwable $e
	 * @return void
	 */
	public function handleException($e)
	{
		if ($this->config->get('app.log')) {
			$this->handler->report($e);
		}
		
		if ($this->config->get('app.debug')) {
			$this->handler->debug($e);
		} else {
			$this->handler->render($e);
		}
	}
	
	/**
     * Handle the PHP shutdown event
     *
     * @return void
     */
    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }
    }
	
	/**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    private function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}
