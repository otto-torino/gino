<?php

ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);

session_name(SESSION_NAME);

class session extends singleton {
	
	const SESSION_STARTED = TRUE;
	const SESSION_NOT_STARTED = FALSE;

	// The state of the session
	private $sessionState = self::SESSION_NOT_STARTED;

	protected function __construct() {
		
		$this->startSession();
	}
	
	/**
	*	(Re)starts the session.
	*
	*	@param	session_name	Name of the session.
	*	@return	bool	TRUE if the session has been initialized, else FALSE.
	**/
	public function startSession($session_name='')
	{
		if($this->sessionState == self::SESSION_NOT_STARTED)
		{
			if($session_name)
				session_name($session_name);
			
			$this->sessionState = session_start();
		}
	
		return $this->sessionState;
	}
	
	/**
	*	Stores datas in the session.
	*	Example: $instance->foo = 'bar';
	*
	*	@param	name	Name of the datas.
	*	@param	value	Your datas.
	*	@return	void
	**/
	public function __set($name , $value)
	{
		$_SESSION[$name] = $value;
	}

	/**
	*	Gets datas from the session.
	*	Example: echo $instance->foo;
	*
	*	@param	name	Name of the datas to get.
	*	@return	mixed	Datas stored in session.
	**/
	public function __get($name)
	{
		if(isset($_SESSION[$name]))
		{
			return $_SESSION[$name];
		}
		else return null;
	}

	public function __isset($name)
	{
		return isset($_SESSION[$name]);
	}

	public function __unset($name)
	{
		unset($_SESSION[$name]);
	}

	/**
	*	Destroys the current session.
	*
	*	@return	bool	TRUE is session has been deleted, else FALSE.
	**/
	public function destroy()
	{
		if($this->sessionState == self::SESSION_STARTED)
		{
			$this->sessionState = !session_destroy();
			unset($_SESSION);
		
			return !$this->sessionState;
		}
	
		return FALSE;
	}
}
?>