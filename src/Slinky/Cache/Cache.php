<?php

namespace Slinky\Cache;

use Slinky\File\File;

use Slinky\File\Exception\NotFoundException;

class Cache
{
	/**
	 * The filesystem instance
	 * 
	 * @var \Slinky\File\File 
	 */
	private $file;
	
	/**
	 * Path to cache directory
	 * 
	 * @var string 
	 */
	private $path;
	
	/**
	 * List of allowed cache types - json, serialize, none
	 * 
	 * @var array
	 */
	private $allowed_types = ['json', 'serialize', 'none'];
	
	/**
	 * Cache type
	 * 
	 * @var string
	 */
	private $type = 'json';
	
	/**
	 * Create new Cache instance
	 * 
	 * @param \Slinky\File\File $file
	 * @param string $path
	 * @param string $type
	 * @return void
	 */
	public function __construct(File $file, $path, $type = 'json')
	{
		$this->file = $file;
		$this->setPath($path);
		$this->setType($type);
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
			throw new NotFoundException('Invalid path "' . $path . '" to cache directory.');
		}
	}
	
	/**
	 * Set cache type - json, serialize, none
	 * 
	 * @param string $type
	 * @return void
	 */
	private function setType($type)
	{
		if (in_array($type, $this->allowed_types)) {
			$this->type = $type;
		}
	}
	
	/**
	 * Store file in cache
	 * 
	 * @param string $filename
	 * @param string $value
	 * @param int $minutes
	 * @return void
	 */
	public function set($filename, $value, $minutes = 60)
	{
		$file = $this->path . $filename;
		
		switch ($this->type) {
			case 'json':
				$value = json_encode($value, JSON_UNESCAPED_UNICODE);
				break;
			case 'serialize':
				$value = serialize($value);
				break;
		}
		
		$seconds = $minutes * 60;
		
		$this->file->set($file, $value);
		
		$this->file->setModifiedTime($file, time() + $seconds);
	}
	
	/**
	 * Get file from cache
	 * 
	 * @param string $filename
	 * @return file contents or bool false
	 */
	public function get($filename)
	{
		if ($this->exists($filename)) {
			$data = $this->file->get($this->path . $filename);
			
			switch ($this->type) {
				case 'json':
					$value = json_decode($data, true);
					break;
				case 'serialize':
					$value = unserialize($data);
					break;
				default:
					$value = $data;
					break;
			}
			
			return $value;
		}
		
		return false;
	}
	
	/**
	 * Remove file from cache
	 * 
	 * @param string $filename
	 * @return bool
	 */
	public function remove($filename)
	{
		$file = $this->path . $filename;
		if ($this->file->exists($file)) {
			$this->file->delete($file);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if cache file exists
	 * 
	 * @param string $filename
	 * @return bool
	 */
	public function exists($filename)
	{
		$file = $this->path . $filename;
		if ($this->expired($file) && $this->file->exists($file)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if cache file is expired
	 * 
	 * @param string $filename
	 * @return bool
	 */
	private function expired($filename)
	{
		if ($this->file->lastModified($filename) > time()) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Clear whole cache folder
	 * 
	 * @return bool
	 */
	public function clear()
	{
		foreach ($this->file->files($this->path) as $file) {
			$this->remove($file);
		}
		
		return true;
	}
}
