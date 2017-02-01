<?php

namespace Slinky\Image;

use Intervention\Image\ImageManager;

class ImageFactory
{
	/**
	 * Intervention ImageManager instance
	 * 
	 * @var \Intervention\Image\ImageManager 
	 */
	private $image_manager;
	
	/**
	 * Creates new Image Factory instance
	 * 
	 * @param string $driver
	 * @return void
	 */
	public function __construct($driver)
	{
		$config = [
			'driver' => $driver
		];
		
		$this->image_manager = new ImageManager($config);
	}
	
	/**
	 * Initiates an Intervention Image instance from different input types
	 * 
	 * @param mixed $data
	 * @return \Intervention\Image\Image
	 */
	public function make($data)
	{
		return $this->image_manager->make($data);
	}
}
