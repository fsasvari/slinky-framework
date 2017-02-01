<?php

namespace Slinky\Pagination;

class Pagination
{
	private $results_per_page;
	private $results_from = 0;
	private $results_to = 0;
	private $results_total = 0;
	
	private $page_first = 1;
	private $page_previous;
	private $page_current;
	private $page_next;
	private $page_last = 1;
	
	private $pages_show_limit = 9;
	private $pages = array();
	
	private $canonical;
	private $canonical_addon;
	
	
	/**
	 * @param int $results_total
	 * @param int $page_current
	 * @param int $results_per_page
	 */
	public function make($results_total, $page_current = 1, $results_per_page = 10)
	{
		$this->results_total = $results_total;
		$this->results_per_page = $results_per_page;
		
		$this->setPageLast();
		$this->setPageCurrent($page_current);
		$this->setPagePrevious();
		$this->setPageNext();
		$this->setResultsTo();
		$this->setResultsFrom();
		$this->setPages();
	}
	
	
	/**
	 * @return void
	 */
	private function setPageLast()
	{
		$this->page_last = ceil($this->results_total / $this->results_per_page);
	}
	
	
	/**
	 * @param int $page_current
	 * @return void
	 */
	private function setPageCurrent($page_current)
	{
		$this->page_current = ($page_current < 1 || $page_current > $this->page_last ? 1 : $page_current);
	}
	
	
	/**
	 * Set previous page from current
	 * 
	 * @return void
	 */
	private function setPagePrevious()
	{
		$this->page_previous = ($this->page_current <= $this->page_first ? '' : $this->page_current - 1);
	}
	
	
	/**
	 * Set next page from current
	 * 
	 * @return void
	 */
	private function setPageNext()
	{
		$this->page_previous = ($this->page_current >= $this->page_last ? '' : $this->page_last + 1);
	}
	
	
	/**
	 * @return void
	 */
	private function setResultsTo()
	{
		$this->results_to = ($this->page_current * $this->results_per_page > $this->results_total ? $this->results_total : $this->page_current * $this->results_per_page);
	}
	
	
	/**
	 * @return void
	 */
	private function setResultsFrom()
	{
		$this->results_from = (($this->page_current - 1) * $this->results_per_page) + 1;
	}
	
	
	/**
	 * Set list of pages
	 * 
	 * @return void
	 */
	private function setPages()
	{
		if ($this->page_current + ($this->pages_show_limit / 2) > $this->page_last) {
			$start = $this->page_last - $this->pages_show_limit + 1;
		} else {
			$start = $this->page_current - intval($this->pages_show_limit / 2);
		}
		
		if ($start < 2) {
			$start = 2;
		}
		
		$end = $start + $this->pages_show_limit;
		if ($end > $this->page_last) {
			$end = $this->page_last;
		}
		
		$this->pages[] = 1;
		
		if ($this->page_last > 1) {
			for ($i = $start; $i < $end; $i++) {
				$this->pages[] = $i;
			}
			
			$this->pages[] = $this->page_last;
		}
	}
	
	
	/**
	 * Set canonical url
	 * 
	 * @param string $canonical
	 * @return void
	 */
	public function setCanonical($canonical)
	{
		$this->canonical = $canonical;
	}
	
	
	/**
	 * Set canonical addon url
	 * 
	 * @param string $canonical_addon
	 * @return void
	 */
	public function setCanonicalAddon($canonical_addon)
	{
		$this->canonical_addon = $canonical_addon;
	}
	
	
	/**
	 * @return int
	 */
	public function getResultsFrom()
	{
		return $this->results_from;
	}
	
	
	/*
	 * @return int
	 */
	public function getResultsTo()
	{
		return $this->results_to;
	}
	
	
	/**
	 * @return int
	 */
	public function getResultsTotal()
	{
		return $this->results_total;
	}
	
	
	/**
	 * @return int
	 */
	public function getPageFirst()
	{
		return $this->page_first;
	}
	
	
	/**
	 * @return int
	 */
	public function getPagePrevious()
	{
		return $this->page_previous;
	}
	
	
	/**
	 * @return int
	 */
	public function getPageCurrent()
	{
		return $this->page_current;
	}
	
	
	/**
	 * @return int
	 */
	public function getPageNext()
	{
		return $this->page_next;
	}
	
	
	/**
	 * @return int
	 */
	public function getPageLast()
	{
		return $this->page_last;
	}
	
	
	/**
	 * Get list of pages
	 * 
	 * @return array
	 */
	public function getPages()
	{
		return $this->pages;
	}
	
	
	/**
	 * Get canonical url
	 * 
	 * @return string
	 */
	public function getCanonical()
	{
		return $this->canonical;
	}
	
	
	/**
	 * Get canonical addon url
	 * 
	 * @return string
	 */
	public function getCanonicalAddon()
	{
		return $this->canonical_addon;
	}
}
