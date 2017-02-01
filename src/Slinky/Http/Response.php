<?php

namespace Slinky\Http;

use Slinky\File\File;
use Slinky\Routing\Redirect;
use Slinky\Session\Session;
use Slinky\Http\Header;
use Slinky\Http\Status;

use Slinky\File\Exception\NotFoundException;

class Response
{
	/**
	 * The file instance
	 * 
	 * @var \Slinky\File\File 
	 */
	private $file;
	
	/**
	 * The redirect instance
	 * 
	 * @var \Slinky\Routing\Redirect
	 */
	private $redirect;
	
	/**
	 * The session instance
	 * 
	 * @var \Slinky\Session\Session
	 */
	private $session;
	
	/**
	 * The header instance
	 * 
	 * @var \Slinky\Http\Header
	 */
	private $header;
	
	/**
	 * The status instance
	 * 
	 * @var \SLinky\Http\Status
	 */
	private $status;
	
	/**
	 * Path to view directory
	 * 
	 * @var string 
	 */
	private $path;
	
	/**
	 * Current file used for view page
	 * 
	 * @var string
	 */
	private $filename;
	
	/**
	 * List of variables used in view
	 * 
	 * @var array
	 */
	private $variables = [];
	
	/**
	 * Body data
	 * 
	 * @var string
	 */
	private $body;
	
	/**
	 * Create response instance
	 * 
	 * @param \Slinky\File\File $file
	 * @param \Slinky\Routing\Redirect $redirect
	 * @param \Slinky\Session\Session $session
	 * @param \Slinky\Http\Header $header
	 * @param \Slinky\Http\Status $status
	 * @param string $path
	 * @return void
	 */
	public function __construct(File $file, Redirect $redirect, Session $session, Header $header, Status $status, $path = '')
	{
		$this->file = $file;
		$this->redirect = $redirect;
		$this->session = $session;
		$this->header = $header;
		$this->status = $status;
		if ($path) {
			$this->setPath($path);
		}
		
		$this->getFlash();
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
		$this->set($key, $value);
	}
	
	/**
	 * Set variable with value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value)
	{
		$this->variables[$key] = $value;
	}
	
	/**
	 * Set template directory path
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
	 * Get path to view directory
	 * 
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	/**
	 * Get view filename
	 * 
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}
	
	/**
	 * Set header information
	 * 
	 * @param string $type Header type
	 * @param string $value Header value
	 * @return void
	 */
	public function setHeader($type, $value)
	{
		$this->header->set($type, $value);
	}
	
	/**
	 * Get all header information and sends them to server
	 * 
	 * @return void
	 */
	private function getHeaders()
	{
		foreach ($this->header->all() as $type => $value) {
			header($type . ': ' . $value);
		}
	}
	
	/**
	 * Get Redirect instance
	 * 
	 * @return \Slinky\Routing\Redirect
	 */
	public function getRedirect()
	{
		return $this->redirect;
	}
	
	/**
	 * Redirect page to url
	 * 
	 * @return void
	 */
	public function redirect($status = 301)
	{
		$this->setStatus($status);
		$this->setHeader('Location:', $this->getRedirect()->getUrl());
		$this->show();
	}
	
	/**
	 * Get flash data from session
	 * 
	 * @return void
	 */
	public function getFlash()
	{
		if ($this->session->getAllFlash()) {
			foreach ($this->session->getAllFlash() as $key => $flash) {
				$this->set($key, unserialize($flash));
			}
		}
	}
	
	/**
	 * Set server status
	 * 
	 * @param int $status
	 * @return void
	 */
	public function setStatus($status)
	{
		$this->status->setStatus($status);
	}
	
	/**
	 * Load the template file => {string}.temp.php
	 *
	 * @param string $name
	 * @return void
	 */
	public function view($name = '')
	{
		$this->filename = $name . '.temp.php';
		$file = $this->path . $this->filename;

		if ($this->file->exists($file)) {
			$this->body = $this->file->load($file, $this->variables);
			
			$this->show();
		} else {
			throw new NotFoundException('Invalid path "' . $file . '" to view file.');
		}
	}
	
	/**
	 * Show error page
	 * 
	 * @param type $status
	 * @return void
	 */
	public function error($status)
	{
		$this->setStatus($status);
		$this->view('Errors/' . $status);
	}
	
	/**
	 * Make response without template include, just pure data
	 * 
	 * @param mixed $value
	 * @return void
	 */
	public function make($value)
	{
		$this->body = (is_array($value) ? print_r($value) : $value);
		
		$this->show();
	}
	
	/**
	 * Make JSON response
	 * 
	 * @param mixed $value
	 * @param int $status
	 * @return void
	 */
	public function json($value, $status = 200)
	{
		$this->setStatus($status);
		$this->setHeader('Content-Type', 'application/json');
		$this->body = json_encode($value);
		
		$this->show();
	}
	
	/**
	 * Make download file header
	 * 
	 * @param string $file
	 * @return void
	 */
	public function download($file)
	{
		$this->setHeader('Content-Description', 'File Transfer');
		$this->setHeader('Content-Type', 'application/octet-stream');
		$this->setHeader('Content-Disposition', 'attachment; filename=' . $file);
		$this->setHeader('Content-Transfer-Encoding', 'binary');
		$this->setHeader('Connection', 'Keep-Alive');
		$this->setHeader('Expires', '0');
		$this->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$this->setHeader('Pragma', 'public');
		$this->setHeader('Content-Length', $this->file->size($file));
		
		$this->show();
	}
	
	/**
	 * Show response data with headers and body
	 * 
	 * @return void
	 */
	private function show()
	{
		$this->getHeaders();
		
		if ($this->body) {
			echo $this->file->minify($this->body);
		}
	}
}
