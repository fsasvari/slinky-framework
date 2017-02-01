<?php

namespace Slinky\File;

class File
{
	/**
	 * Set contents to file
	 * 
	 * @param string $filename
	 * @param mixed $content
	 * @return bool
	 */
	public function set($filename, $content = '')
	{
		if (!$this->exists($filename)) {
			file_put_contents($filename, $content);
			
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Get contents from file
	 * 
	 * @param string $filename
	 * @return string/array
	 */
	public function get($filename)
	{
		if ($this->exists($filename)) {
			return file_get_contents($filename);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Get contents from remoteurl
	 * 
	 * @param string $url
	 * @return string/array
	 */
	public function getRemote($url)
	{
		if ($this->existsRemote($url)) {
			return file_get_contents($url);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Include/load whole file
	 * 
	 * @param string $filename
	 * @param array $variables
	 * @return string
	 */
	public function load($filename, array $variables = [])
	{
		if ($this->exists($filename)) {
			ob_start();
			
			foreach ($variables as $key => $value) {
				${$key} = $value;
			}
			include($filename);
			
			return ob_get_clean();
		} else {
			return false;
		}
	}
	
	
	/**
	 * Copy file from one directory to another leaving orignial intact
	 * 
	 * @param string $filename
	 * @param string $destination
	 * @return bool
	 */
	public function copy($filename, $destination)
	{
		if (($this->exists($filename) || $this->existsRemote($filename)) && !$this->exists($destination)) {
			copy($filename, $destination);
			
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Move file from one directory to another
	 * 
	 * @param string $filename
	 * @param string $destination
	 * @return bool
	 */
	public function move($filename, $destination)
	{
		if ($this->exists($filename) && $this->exists($destination)) {
			rename($filename, $destination);
			
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Delete file or directory
	 * 
	 * @param string $filename
	 * @return bool
	 */
	public function delete($filename)
	{
		if ($this->exists($filename)) {
			unlink($filename);
			
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Prepend content to file
	 * 
	 * @param string $filename
	 * @param mixed $content
	 * @return bool
	 */
	public function prepend($filename, $content)
	{
		if ($this->exists($filename)) {
            return $this->set($filename, $content . $this->get($filename));
        }
        return $this->set($filename, $content);
	}
	
	
	/**
	 * Append content to file
	 * 
	 * @param string $filename
	 * @param mixed $content
	 * @return bool
	 */
	public function append($filename, $content)
	{
		return file_put_contents($filename, $content, FILE_APPEND);
	}
	
	
	/**
	 * Returns a list of all files in directory
	 * 
	 * @param string $directory
	 * @return array
	 */
	public function files($directory)
	{
		if ($this->isDirectory($directory)) {
			return array_diff(scandir($directory), array('..', '.'));
		} else {
			return false;
		}
	}
	
	
	/**
	 * Returns a list of all directories in directory
	 * 
	 * @param string $directory
	 * @return array
	 */
	public function directories($directory)
	{
		if ($this->isDirectory($directory)) {
			return array_filter(glob($directory . '*'), 'is_dir');
		} else {
			return false;
		}
	}
	
	
	/**
	 * Check if filename is file or directory
	 * 
	 * @param string $filename
	 * @return string
	 */
	public function type($filename)
	{
		if ($this->isDirectory($filename)) {
			return 'dir';
		} elseif ($this->isFile($filename)) {
			return 'file';
		} else {
			return false;
		}
	}
	
	
	/**
	 * Get the mime-type of a given file
	 * 
	 * @param string $filename
	 * @return string
	 */
	public function mimeType($filename)
	{
		return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filename);
	}
	
	
	/**
	 * Returns basename of filename
	 * 
	 * @param string $filename
	 * @return string
	 */
	public function basename($filename)
	{
		if ($this->exists($filename) && $this->isFile($filename)) {
			return pathinfo($filename, PATHINFO_FILENAME);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Returns extension of filename
	 * 
	 * @param string $filename
	 * @return string
	 */
	public function extension($filename)
	{
		if ($this->exists($filename) && $this->isFile($filename)) {
			return pathinfo($filename, PATHINFO_EXTENSION);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Returns size of filename
	 * 
	 * @param string $filename
	 * @return string
	 */
	public function size($filename)
	{
		if ($this->exists($filename) && $this->isFile($filename)) {
			return filesize($filename);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Set modified time
	 * 
	 * @param string $filename
	 * @param int $time
	 * @return int
	 */
	public function setModifiedTime($filename, $time)
	{
		if ($this->exists($filename) && $this->isFile($filename)) {
			touch($filename, $time);
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Returns last modified time
	 * 
	 * @param string $filename
	 * @return int
	 */
	public function lastModified($filename)
	{
		if ($this->exists($filename) && $this->isFile($filename)) {
			return filemtime($filename);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Check if file or directory exists
	 * 
	 * @param string $filename
	 * @return bool
	 */
	public function exists($filename)
	{
		return file_exists($filename);
	}
	
	
	/**
	 * Check if remote file exists
	 * 
	 * @param string $url
	 * @return bool
	 */
	public function existsRemote($url)
	{
		$contents = @file_get_contents($url);
		
		if ($contents) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Check if filename is file
	 * 
	 * @param string $filename
	 * @return bool
	 */
	public function isFile($filename)
	{
		return is_file($filename);
	}
	
	
	/**
	 * Check if filename is directory
	 * 
	 * @param string $directory
	 * @return bool
	 */
	public function isDirectory($directory)
	{
		return is_dir($directory);
	}
	
	
	/**
	 * Minify content
	 * 
	 * @param string $content
	 * @return string
	 */
	public function minify($content)
	{
		// Clean comments and whitespace
		return preg_replace('/(\r?\n)/', ' ', preg_replace('/\s{2,}/', ' ', preg_replace('/(?<!\S)\/\/\s*[^\r\n]*/', ' ', preg_replace('/<!--([^\[|(<!)].*)/', ' ', $content))));
	}
}
