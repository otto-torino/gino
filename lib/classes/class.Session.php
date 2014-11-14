<?php
/**
 * @file session.php
 * @brief Contiene la classe session
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

\ini_set('session.use_cookies', 1);
\ini_set('session.use_only_cookies', 1);

\session_name(SESSION_NAME);

/**
 * @brief Gestione delle variabili di sessione
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class session extends singleton {
	
	const SESSION_STARTED = TRUE;
	const SESSION_NOT_STARTED = FALSE;

	// The state of the session
	private $sessionState = self::SESSION_NOT_STARTED;
	
	private $_vars;

	/**
	 * Avvia la sessione
	 */
	protected function __construct() {
		
		$this->startSession();
		$this->_vars = $_SESSION;
	}
	
	/**
	 * (Ri)avvia la sessione
	 *
	 * @param string $session_name nome della sessione (se non indicato viene impostato all'esterno della classe)
	 * @return boolean TRUE se le sessione è stata inizializzata, altrimenti FALSE
	 */
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
	 * Imposta il valore di una variabile di sessione
	 *
	 * @param string $name nome della variabile di sessione
	 * @param mixed $value valore della variabile di sessione
	 * @return void
	 * 
	 * Esempio
	 * @code
	 * $instance->foo = 'bar';
	 * @endcode
	 */
	public function __set($name , $value)
	{
		$this->_vars[$name] = $value;
		$_SESSION[$name] = $value;
	}

	/**
	 * Ritorna il valore di una variabile di sessione
	 *
	 * @param string $name nome della variabile di sessione
	 * @return mixed
	 * 
	 * Esempio
	 * @code
	 * echo $instance->foo;
	 * @endcode
	*/
	public function __get($name)
	{
		return isset($this->_vars[$name]) ? $this->_vars[$name] : null;
	}

	/**
	 * Verifica se una variabile di sessione esiste
	 * 
	 * @param string $name nome della variabile di sessione
	 * @return boolean
	 */
	public function __isset($name)
	{
		return isset($_SESSION[$name]);
	}

	/**
	 * Distrugge una variabile di sessione
	 * 
	 * @param string $name nome della variabile di sessione
	 * @return void
	 */
	public function __unset($name)
	{
		unset($_SESSION[$name]);
		unset($this->_vars[$name]);
	}

	/**
	 * Distrugge la sessione corrente
	 *
	 * @return boolean
	 */
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