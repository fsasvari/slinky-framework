<?php

namespace Slinky\Exception;

use Exception;
use Slinky\Routing\NotFoundException;
use Slinky\Exception\ExceptionLog;
use Slinky\Mail\Mail;
use Slinky\Http\Response;
use Slinky\Translation\Language;

class ExceptionHandler
{
	/**
	 * The log instance
	 * 
	 * @var \Slinky\Exception\ExceptionLog
	 */
	protected $log;
	
	/**
	 * The mail instance
	 * 
	 * @var \Slinky\Mail\Mail
	 */
	protected $mail;
	
	/**
	 * The response instance
	 * 
	 * @var \Slinky\Http\Response
	 */
	protected $response;
	
	/**
	 * The language instance
	 * 
	 * @var \Slinky\Translation\Language
	 */
	protected $language;
	
	/**
     * A list of the exception types that should not be reported
     *
     * @var array
     */
	protected $dontReport = [];
	
	/**
	 * Create new exception handler instance
	 * 
	 * @param \Slinky\Exception\ExceptionLog $log
	 * @param \Slinky\Http\Response $response
	 * @param \Slinky\Translation\Language $language
	 * @return void
	 */
	public function __construct(ExceptionLog $log, Response $response, Language $language)
	{
		$this->log = $log;
		//$this->mail = $mail;
		$this->response = $response;
		$this->language = $language;
	}
	
	/**
     * Report or log an exception
     *
     * @param \Exception $e
     * @return void
     */
	public function report(Exception $e)
	{
		if ($this->shouldReport($e)) {
			$this->log->log($e);
		}
	}
	
	/**
     * Determine if the exception should be reported
     *
     * @param \Exception $e
     * @return bool
     */
    protected function shouldReport(Exception $e)
    {
        return ! $this->shouldntReport($e);
    }
	
	/**
     * Determine if the exception is in the "do not report" list
     *
     * @param \Exception $e
     * @return bool
     */
    protected function shouldntReport(Exception $e)
    {
        $dontReport = array_merge($this->dontReport, [NotFoundException::class]);
		
        foreach ($dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
		
        return false;
    }
	
	/**
     * Render an exception into a response
     *
     * @param \Exception $e
     * @return void
     */
	public function render(Exception $e)
	{
		$this->response->set('lang', $this->language);
		$this->response->set('exception', $e);
		
		if ($e instanceof NotFoundException) {
			$this->response->error(404);
		} else {
			$this->response->error(500);
		}
	}
	
	/**
     * Render an exception as debug response
     *
     * @param \Exception $e
     * @return void
     */
	public function debug(Exception $e)
	{
		var_dump($e);
	}
}
