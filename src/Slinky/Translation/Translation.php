<?php

namespace Slinky\Translation;

use Slinky\File\File;

use Slinky\File\Exception\NotFoundException;
use Slinky\Exception\Core\InvalidArgumentException;

class Translation
{
	/**
	 * The file instance
	 * 
	 * @var \Slinky\File\File
	 */
	private $file;
	
	/**
	 * Path to main language directory
	 * 
	 * @var string
	 */
	private $path;
	
	/**
	 * List of translations
	 * 
	 * @var array
	 */
	private $translations = [];
	
	/**
	 * Create new translation instance
	 * 
	 * @param \Slinky\File\File $file
	 * @param string $path
	 * @return void
	 */
	public function __construct(File $file, $path)
	{
		$this->file = $file;
		
		$this->setPath($path);
	}
	
	/**
	 * Set language directory path
	 *
	 * @param string $path
	 * @throws \Slinky\File\Exception\NotFoundException
	 * @return void
	 */
	private function setPath($path)
	{
		if ($this->file->isDirectory($path)) {
			$this->path = $path;
		} else {
			throw new NotFoundException('Invalid path "' . $path . '" to language directory.');
		}
	}
	
	/**
	 * Get specific language variable
	 *
	 * @param string $name
	 * @param string $bind
	 * @param string $language
	 * @return string|array
	 */
	public function get($name, $bind, $language)
	{
		$translation = $this->getFileAndString($name);
		$file = $translation['file'];
		
		if (! $this->exists($language, $file, $translation['string'])) {
			$this->set($language, $file, $this->loadTranslationFile($language, $file));
		}
		
		if (empty($bind)) {
			return arr_get($this->translations[$language][$file], $translation['string']);
		} else {
			return $this->getBind($bind, arr_get($this->translations[$language][$file], $translation['string']));
		}	
	}
	
	/**
	 * Set translations to appropriate language and file array
	 * 
	 * @param string $language
	 * @param string $file
	 * @param array $translations
	 * @return void
	 */
	private function set($language, $file, $translations)
	{
		$this->translations[$language][$file] = $translations;
	}
	
	/**
	 * Get binded data
	 * 
	 * @param array $bind
	 * @param string $translation
	 * @return string
	 */
	private function getBind($bind, $translation)
	{
		$replace_from = $replace_to = [];
		
		foreach ($bind as $key => $value) {
			if (! in_array($key, $replace_from)) {
				$replace_from[] = '{{' . $key . '}}';
				$replace_to[] = $value;
			}
		}
		
		return str_replace($replace_from, $replace_to, $translation);
	}
	
	/**
	 * Get file and variable name from string
	 * 
	 * @param string $name
	 * @throws \Slinky\Exception\Core\InvalidArgumentException
	 * @return array Returns array of two indexes - 'file' and 'string'
	 */
	private function getFileAndString($name)
	{
		$name_explode = explode('.', $name, 2);
		if (count($name_explode) == 2) {
			$return['file'] = $name_explode[0];
			$return['string'] = $name_explode[1];
			
			return $return;
		}
		
		throw new InvalidArgumentException('File and string does not exist in "' . $name . '".');
	}
	
	/**
	 * Checks if variable exists in language and file
	 * 
	 * @param string $language
	 * @param string $file
	 * @param string $variable
	 * @return bool
	 */
	private function exists($language, $file, $variable)
	{
		if(! isset($this->translations[$language]) || ! isset($this->translations[$language][$file])) {
			return false;
		}
		
		return arr_has($this->translations[$language][$file], $variable);
	}
	
	/**
	 * Load the language file (lang/file.php) from directory and returns array from that file
	 *
	 * @param string $language
	 * @param string $file
	 * @throws \Slinky\File\Exception\NotFoundException
	 * @return array
	 */
	private function loadTranslationFile($language, $file)
	{
		// set language directory
		$file = $this->path . $language . '/' . $file . '.php';
		
		if ($this->file->isFile($file)) {
			return include($file);
		}
		
		throw new NotFoundException('Language file "' . $file . '" does not exist.');
	}
}
