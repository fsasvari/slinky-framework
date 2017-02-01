<?php

namespace Slinky\Translation;

use Slinky\Translation\Translation;

class Language
{
	/**
	 * The translation instance
	 * 
	 * @var \Slinky\Translation\Translation
	 */
	private $translation;
	
	/**
	 * Default language
	 * 
	 * @var string
	 */
	private $languageDefault;
	
	/**
	 * Currently active language
	 * 
	 * @var string
	 */
	private $languageActive;
	
	/**
	 * List of allowed languages
	 * 
	 * @var array
	 */
	private $languagesAllowed = [];
	
	/**
	 * List of alternate languages
	 * 
	 * @var array
	 */
	private $languagesAlt = [];
	
	/**
	 * Create new language instance
	 * 
	 * @param \Slinky\Translation\Translation $translation
	 * @param string $languageDefault
	 * @param array $languagesAllowed
	 * @return void
	 */
	public function __construct(Translation $translation, $languageDefault, $languagesAllowed)
	{
		$this->translation = $translation;
		
		$this->languageDefault = $languageDefault;
		$this->languagesAllowed = $languagesAllowed;
	}
	
	/**
	 * Set currently active language
	 *
	 * @param string $languageActive
	 * @return void
	 */
	public function setLanguage($languageActive)
	{
		$this->languageActive = (in_array($languageActive, $this->languagesAllowed) ? $languageActive : $this->languageDefault);
		
		$this->setLanguagesAlt();
	}
	
	/**
	 * Get currently active language, if not provided get default language
	 * 
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->languageActive ? $this->languageActive : $this->languageDefault;
	}
	
	/**
	 * Get default language
	 * 
	 * @return string
	 */
	public function getLanguageDefault()
	{
		return $this->languageDefault;
	}
	
	/**
	 * Set alternate languages
	 * 
	 * @return void
	 */
	private function setLanguagesAlt()
	{
		$languagesAlt = $this->languagesAllowed;
		
		if (($key = array_search($this->languageActive, $languagesAlt)) !== false) {
			unset($languagesAlt[$key]);
		}
		
		$this->languagesAlt = $languagesAlt;
	}
	
	/**
	 * Get all alternate languages
	 *
	 * @return array
	 */
	public function getLanguagesAlt()
	{
		return $this->languagesAlt;
	}
	
	/**
	 * Get all languages
	 *
	 * @return array
	 */
	public function getLanguages()
	{
		return $this->languagesAllowed;
	}
	
	/**
	 * Get specific language variable
	 *
	 * @param string $name
	 * @param string $bind
	 * @param string $language
	 * @return string|array
	 */
	public function get($name, $bind = [], $language = '')
	{
		return $this->translation->get($name, $bind, ($language ? $language : $this->getLanguage()));
	}
}
