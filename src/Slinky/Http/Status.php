<?php

namespace Slinky\Http;

use Slinky\Exception\Core\InvalidArgumentException;

class Status
{
	/**
	 * Current status message
	 * 
	 * @var int
	 */
	private $status = 200;
	
	/**
	 * List of possible status messages
	 * 
	 * @var array
	 */
	private $messages = [
		//Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
		
        //Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
		
        //Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
		
        //Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
		
        //Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
	];
	
	/**
	 * Set server status code
	 * 
	 * @param int $status
	 * @return void
	 */
	public function setStatus($status)
	{
		if (arr_has($this->messages, $status)) {
			http_response_code($status);
			$this->status = $status;
		} else {
			throw new InvalidArgumentException('Invalid status code "' . $status . '".');
		}
	}
	
	/**
	 * Get server status code
	 * 
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
     * Is this response empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return in_array($this->getStatus(), [204, 205, 304]);
    }
	
    /**
     * Is this response informational?
     *
     * @return bool
     */
    public function isInformational()
    {
        return $this->getStatus() >= 100 && $this->getStatus() < 200;
    }
	
	/**
     * Is this response OK?
     *
     * @return bool
     */
    public function isOk()
    {
		return $this->getStatus() === 200;
    }
	
    /**
     * Is this response successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getStatus() >= 200 && $this->getStatus() < 300;
    }
	
    /**
     * Is this response a redirect?
     *
     * @return bool
     */
    public function isRedirect()
    {
        return in_array($this->getStatus(), [301, 302, 303, 307]);
    }
	
    /**
     * Is this response a redirection?
     *
     * @return bool
     */
    public function isRedirection()
    {
        return $this->getStatus() >= 300 && $this->getStatus() < 400;
    }
	
    /**
     * Is this response forbidden?
     *
     * @return bool
     */
    public function isForbidden()
    {
        return $this->getStatus() === 403;
    }
	
    /**
     * Is this response not Found?
     *
     * @return bool
     */
    public function isNotFound()
    {
        return $this->getStatus() === 404;
    }
	
    /**
     * Is this response a client error?
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->getStatus() >= 400 && $this->getStatus() < 500;
    }
	
    /**
     * Is this response a server error?
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->getStatus() >= 500 && $this->getStatus() < 600;
    }
}
