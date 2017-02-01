<?php

namespace Slinky\Mail;

use Slinky\File\File;
use Pelago\Emogrifier;
use Slinky\Translation\Language;
use Slinky\Config\Config;

use Slinky\Exception\File\NotFoundException;

class MailTemplate
{
	private $file;
	private $emogrifier;
	
	private $path;
	private $filename;
	private $css_file;
	
	private $variables = array();
	
	
	/**
	 * @param File $file
	 * @param Emogrifier $emogrifier
	 * @param Language $language
	 * @param string $path
	 * @param string $css_file
	 */
	public function __construct(File $file, Emogrifier $emogrifier, Language $language, Config $config, $path, $css_file)
	{
		$this->file = $file;
		$this->emogrifier = $emogrifier;
		$this->lang = $language;
		$this->config = $config;
		$this->setPath($path);
		$this->setCssFile($css_file);
	}


	/**
	 * Set undefined variables
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->variables[$key] = $value;
	}
	
	
	/**
	 * Set mail template directory path
	 *
	 * @param string $path
	 * @return void
	 */
	private function setPath($path)
	{
		if ($this->file->isDirectory($path)) {
			$this->path = $path;
		} else {
			throw new NotFoundException('Invalid path "' . $path . '" to response directory.');
		}
	}
	
	
	/**
	 * Set CSS file
	 * 
	 * @param string $css_file
	 * @return void
	 */
	private function setCssFile($css_file)
	{
		if ($this->file->isFile($css_file)) {
			$this->css_file = $css_file;
		} else {
			throw new NotFoundException('Invalid path "' . $css_file . '" to css file.');
		}
	}
	
	
	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	
	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}
	
	
	/**
	 * Set bind data to mail template
	 * 
	 * @param array $data
	 * @return void
	 */
	private function setData(array $data = array())
	{
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
		$this->include = $this->getPath() . 'Include/';
	}
	
	
	/**
	 * Get mail template with data
	 * 
	 * @param mixed
	 */
	public function get($name, $data = array())
	{
		$this->filename = $name . '.mail.php';
		$file = $this->path . $this->filename;

		if ($this->file->exists($file)) {
			$this->setData($data);
			
			$html = $this->file->load($file, $this->variables);
			$css = $this->file->load($this->css_file);
			
			$this->emogrifier->setHtml($html);
			$this->emogrifier->setCss($css);
			$this->emogrifier->disableStyleBlocksParsing();
			$content = $this->file->minify($this->emogrifier->emogrify());
			
			echo $content;

			return $content;
		}
		
		return false;
	}
}
