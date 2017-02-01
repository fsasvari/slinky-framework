<?php

namespace Slinky\Log;

use Slinky\File\File;

use Slinky\Exception\File\NotFoundException;

class Log
{
	/**
	 * The file instance
	 * 
	 * @var \Slinky\File\File
	 */
	private $file;
	
	/**
	 * Log directory path
	 * 
	 * @var string
	 */
	private $path;
	
	/**
	 * Allowed log types
	 * 
	 * @var array
	 */
	private $types_allowed = ['single', 'daily'];
	
	/**
	 * Log type - single file, daily files
	 * 
	 * @var string
	 */
	private $type = 'daily';
	
	/**
	 * Simulate log
	 * 
	 * @var bool
	 */
	private $simulate = false;
	
	/**
	 * @param \Slinky\File\File $file
	 * @param string $path
	 * @param string $type
	 * @param bool $simulate
	 * @return void
	 */
	public function __construct(File $file, $path, $type = 'daily', $simulate = false)
	{
		$this->file = $file;
		$this->setPath($path);
		$this->setType($type);
		$this->simulate = $simulate;
	}
	
	/**
	 * Set cache directory path
	 * 
	 * @param string $path
	 * @return void
	 */
	private function setPath($path)
	{
		if ($this->file->isDirectory($path)) {
			$this->path = $path;
		} else {
			throw new NotFoundException('Invalid path "' . $path . '" to log directory.');
		}
	}
	
	/**
	 * Set log type - single file, daily files
	 * 
	 * @param string $type
	 * @return void
	 */
	private function setType($type)
	{
		if (in_array($type, $this->types_allowed)) {
			$this->type = $type;
		}
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function info($message, $context = [])
	{
		$this->log('INFO', $message, $context);
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function warning($message, $context = [])
	{
		$this->log('WARNING', $message, $context);
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function notice($message, $context = [])
	{
		$this->log('NOTICE', $message, $context);
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function error($message, $context = [])
	{
		$this->log('ERROR', $message, $context);
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function critical($message, $context = [])
	{
		$this->log('CRITICAL', $message, $context);
	}
	
	/**
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	public function debug($message, $context = [])
	{
		$this->log('DEBUG', $message, $context);
	}
	
	/**
	 * @param string $type
	 * @param string $message
	 * @param array $context
	 * @return void
	 */
	private function log($type, $message, $context)
	{
		if (! $this->simulate) {
			$file = $this->getFilename();
			$record = $this->getRecord($type, $message, $context);

			$this->file->append($file, $record);
		}
	}
	
	/**
	 * @return string
	 */
	private function getFilename()
	{
		switch ($this->type) {
			case 'single':
				$file = 'Error';
				break;
			case 'daily':
				$file = date('Ymd');
				break;
		}
		
		return $this->path . $file . '.log';
	}
	
	/**
	 * Format and get log record
	 * 
	 * @param string $type
	 * @param string $message
	 * @param array $context
	 * @return string
	 */
	private function getRecord($type, $message, $context)
	{
		return 'Date: ' . date('Y-m-d H:i:s') . "\r\n"
				. 'Type: ' . $type . "\r\n"
				. 'Message: ' . $message . "\r\n"
				. ($context ? 'Context: ' . implode(', ', $context) . "\r\n" : '') . "\r\n"
				. "----------------------------------------------------------------------------------------\r\n\r\n";
	}
}
