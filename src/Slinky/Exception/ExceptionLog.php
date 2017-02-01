<?php

namespace Slinky\Exception;

use Exception;
use Slinky\Database\QueryException;
use Slinky\Log\Log;

class ExceptionLog
{
	/**
	 * The log instance
	 * 
	 * @var \Slinky\Log\Log
	 */
	private $log;
	
	/**
	 * Create exception log instance
	 * 
	 * @param \Slinky\Log\Log $log
	 * @return void
	 */
	public function __construct(Log $log)
	{
		$this->log = $log;
	}
	
	/**
	 * Log exception
	 * 
	 * @param \Exception $e
	 * @return void
	 */
	public function log(Exception $e)
	{
		$message = $this->format($e);
		
		$this->log->error($message);
	}
	
	/**
	 * Format message for log file
	 * 
	 * @param Exception $e
	 * @return string
	 */
	private function format(Exception $e)
	{
		return $e->getMessage() . "\r\n"
				. 'File: ' . $e->getFile() . "\r\n"
				. 'Line: ' . $e->getLine() . "\r\n"
				. ($e instanceof QueryException ? 'Query: ' . $e->getSql() . "\r\n" : '')
				. 'Stack trace: ' . $this->formatStackTrace($e->getTrace());
	}
	
	/**
	 * Format exception stack trace
	 * 
	 * @param array $traces
	 * @return string
	 */
	private function formatStackTrace($traces)
	{
		$ret = '';
		
		foreach ($traces as $i => $trace) {
			$ret .= "\r\n#$i "
				. (isset($trace['file']) ? $trace['file'] : '')
				. (isset($trace['line']) ? ' ('.$trace['line'].')' : '')
				. (isset($trace['file']) && isset($trace['line']) ? ': ' : '')
				. (isset($trace['class']) ? $trace['class'] : '')
				. (isset($trace['type']) ? $trace['type'] : '')
				. (isset($trace['function']) ? $trace['function'] : '')
				. (isset($trace['args']) ? '('.$this->formatArgs($trace['args']).')' : '');
		}
		
		return $ret;
	}
	
	/**
	 * Format exception arguments
	 * 
	 * @param array $args
	 * @return string
	 */
	private function formatArgs(array $args)
	{
		$arr = [];
		
		foreach ($args as $arg) {
			if (is_string($arg)) {
				$arr[] = "'" . $arg . "'";
			} elseif (is_array($arg)) {
				$arr[] = "Array";
			} elseif (is_null($arg)) {
				$arr[] = 'NULL';
			} elseif (is_bool($arg)) {
				$arr[] = ($arg) ? "true" : "false";
			} elseif (is_object($arg)) {
				$arr[] = get_class($arg);
			} elseif (is_resource($arg)) {
				$arr[] = get_resource_type($arg);
			} else {
				$arr[] = $arg;
			}   
		}
		
		return implode(', ', $arr);
	}
}
