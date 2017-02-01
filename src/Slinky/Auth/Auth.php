<?php

namespace Slinky\Auth;

use Slinky\Library\Session;
use Slinky\Library\Cookie;
use Slinky\Library\Hash;
use Application\Model\Repository\UserRepository;

use Application\Model\Entity\User;

class Auth
{
	private $session;
	private $cookie;
	private $hash;
	private $userRepository;
	
	private $user;
	private $loggedOut = false;
	
	
	/**
	 * @param Session $session
	 * @param Cookie $cookie
	 * @param UserRepository $user_repository
	 * @return void
	 */
	public function __construct(Session $session, Cookie $cookie, Hash $hash, UserRepository $user_repository)
	{
		$this->session = $session;
		$this->cookie = $cookie;
		$this->hash = $hash;
		$this->userRepository = $user_repository;
	}
	
	
	/**
     * Determine if the current user is authenticated
     *
     * @return bool
     */
    public function check()
    {
		return !is_null($this->user()) && $this->user();
    }
	
	
	/**
	 * Get the currently authenticated user
	 * 
	 * @return User|bool
	 */
	public function user()
	{
		if ($this->loggedOut) {
			return null;
		}
		
		if (!is_null($this->user)) {
			return $this->user;
		}
		
		$user = null;
		
		// first try via session
		$session_id = $this->session->get($this->getName());
        if ($session_id) {
			$user = $this->userRepository->loadBySessionId($session_id);
        }
		
		// then via cookie
		if (is_null($user)) {
			$cookie_id = $this->cookie->get($this->getName());
			if ($cookie_id) {
				$user = $this->userRepository->loadBySessionId($cookie_id);
			}
		}
		
		return $this->user = $user;
	}
	
	
	/**
	 * Attempt to authenticate a user using the given credentials
	 * 
	 * @param string $email
	 * @param string $password
	 * @param bool $remember
	 * @param bool $login
	 */
	public function attempt($email, $password, $remember = false, $login = true)
	{
		$user = $this->loadByCredentials($email);
		
		if ($this->hasValidCredentials($user, $email, $password)) {
			if ($login) {
				$this->login($user, $remember);
			}
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Log a user into the application
	 * 
	 * @param User $user
	 * @param bool $remember
	 */
	public function login(User $user, $remember = false)
	{
		$this->updateSession($user->getId());
		
		if ($remember) {
			$this->updateCookie($user->getId());
		}
		
		$this->user = $user;
		$this->loggedOut = false;
	}
	
	
	/**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $this->clearUserData();
		
		$this->loggedOut = true;
	}
	
	
	/**
	 * Remove the user data from the session and cookies
	 * 
	 * @return void
	 */
	private function clearUserData()
	{
		$this->session->remove($this->getName());
		$this->cookie->remove($this->getName());
		
		$this->user = null;
	}
	
	
	/**
	 * Load user data by credentials
	 * 
	 * @param string $email
	 * @return User|bool
	 */
	public function loadByCredentials($email)
	{
		$user = $this->userRepository->loadByCredentials($email);
		
		if (!$user) {
			return false;
		} else {
			$this->user = $user;
			return $user;
		}
	}
	
	
	/**
     * Determine if the user matches the credentials
     *
     * @param mixed $user
	 * @param string $email
     * @param string $password
     * @return bool
     */
    protected function hasValidCredentials($user, $email, $password)
    {
		if (is_null($user) || !$user) {
			return false;
		}
		
		if ($user->getEmail() != $email) {
			return false;
		}
		
		if ($user->getPasswordType() == 1) {
			return $this->hash->check($password, $user->getPassword());
		} else {
			return $user->getPassword() == $password;
		}
    }
	
	
	/**
	 * Update the session with the given ID
	 * 
	 * @param string $id
	 * @return void
	 */
	private function updateSession($id)
	{
		$this->session->set($this->getName(), $id);
	}
	
	
	/**
	 * Update the cookie with the given ID
	 * 
	 * @param string $id
	 * @return void
	 */
	private function updateCookie($id)
	{
		$this->cookie->set($this->getName(), $id, 60 * 60 * 24 * 30);
	}
	
	
	/**
	 * Get a unique identifier name
	 * 
	 * @return string
	 */
	private function getName()
	{
		return 'login_' . md5(get_class($this));
	}
}
