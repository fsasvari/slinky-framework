<?php

namespace Slinky\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use Slinky\Mail\MailTemplate;

class Mail
{
	private $mailer;
	private $template;
	
	
	/**
	 * @param PHPMailer $mailer
	 * @param MailTemplate $template
	 * @return void
	 */
	public function __construct(PHPMailer $mailer, MailTemplate $template)
	{
		$this->mailer = $mailer;
		$this->template = $template;
		
		$this->mailer->isHTML(true);
		$this->mailer->CharSet = 'UTF-8';
	}
	
	
	/**
	 * Set from email address and name
	 * 
	 * @param string $email
	 * @param string $name
	 * @return $this
	 */
	public function setFrom($email, $name = '')
	{
		$this->mailer->setFrom($email, $name);
		
		return $this;
	}
	
	
	/**
	 * Add reply to email address and name
	 * 
	 * @param string $email
	 * @param string $name
	 * @return $this
	 */
	public function addReplyTo($email, $name = '')
	{
		$this->mailer->addReplyTo($email, $name);
		
		return $this;
	}
	
	
	/**
	 * Add to email address and name
	 * 
	 * @param string $email
	 * @param string $name
	 * @return $this
	 */
	public function addTo($email, $name = '')
	{
		$this->mailer->addAddress($email, $name);
		
		return $this;
	}
	
	
	/**
	 * Add CC email address and name
	 * 
	 * @param string $email
	 * @param string $name
	 * @return $this
	 */
	public function addCC($email, $name = '')
	{
		$this->mailer->addCC($email, $name);
		
		return $this;
	}
	
	
	/**
	 * Add BCC email address and name
	 * 
	 * @param string $email
	 * @param string $name
	 * @return $this
	 */
	public function addBCC($email, $name = '')
	{
		$this->mailer->addBCC($email, $name);
		
		return $this;
	}
	
	
	/**
	 * Add attachment to email
	 * 
	 * @param string $subject
	 * @return $this
	 */
	public function addAttachment($file, $filename = '')
	{
		$this->mailer->addAttachment($file, $filename);
		
		return $this;
	}
	
	
	/**
	 * Set email subject
	 * 
	 * @param string $subject
	 * @return $this
	 */
	public function setSubject($subject)
	{
		$this->mailer->Subject = $subject;
		
		return $this;
	}
	
	
	/**
	 * Set email body and plain body
	 * 
	 * @param string $body
	 * @param string $body_plain
	 * @return $this
	 */
	public function setBody($body, $body_plain = '')
	{
		$this->mailer->Body = $body;
		if ($body_plain) {
			$this->mailer->AltBody = $body_plain;
		}
		
		return $this;
	}
	
	
	/**
	 * 
	 * @param string $template
	 * @param string $data
	 */
	private function setData($template, $data)
	{
		$body = $this->template->get($template, $data);
		
		$this->setBody($body);
	}
	
	
	/**
	 * Get error message
	 * 
	 * @return string
	 */
	public function getErrorInfo()
	{
		return $this->mailer->ErrorInfo;
	}
	
	
	/**
	 * Send email
	 * 
	 * @return void
	 */
	public function send($template = '', $data = array())
	{
		if ($template) {
			$this->setData($template, $data);
		}
		
		$ret = $this->mailer->send();
		
		$this->mailer->clearAllRecipients();
		
		return $ret;
	}
}
