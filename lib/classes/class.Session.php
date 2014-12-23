<?php
/**
 * @file class.Session.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Session
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

\ini_set('session.use_cookies', 1);
\ini_set('session.use_only_cookies', 1);

\session_name(SESSION_NAME);

/**
 * @brief Classe per la gestione delle variabili di sessione
 *
 * @description La classe è di tipo Gino.Singleton per garantire l'esistenza di una sola istanza a runtime.
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Session extends Singleton {

    const SESSION_STARTED = TRUE;
    const SESSION_NOT_STARTED = FALSE;

    // The state of the session
    private $sessionState = self::SESSION_NOT_STARTED;
    private $_vars;

    /**
     * @brief Costruttore
     * @description Avvia la sessione
     */
    protected function __construct() {

        $this->startSession();
        $this->_vars = $_SESSION;
    }

    /**
     * @brief (Ri)avvia la sessione
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

            $this->sessionState = @session_start();
        }

        return $this->sessionState;
    }

    /**
     * @brief Imposta il valore di una variabile di sessione
     *
     * Esempio
     * @code
     * $instance->foo = 'bar';
     * @endcode
     *
     * @param string $name nome della variabile di sessione
     * @param mixed $value valore della variabile di sessione
     * @return void
     */
    public function __set($name , $value)
    {
        $this->_vars[$name] = $value;
        $_SESSION[$name] = $value;
    }

    /**
     * @brief Ritorna il valore di una variabile di sessione
     *
     * Esempio
     * @code
     * echo $instance->foo;
     * @endcode
     *
     * @param string $name nome della variabile di sessione
     * @return valore variabile di sessione o null
     */
    public function __get($name)
    {
        return isset($this->_vars[$name]) ? $this->_vars[$name] : null;
    }

    /**
     * @brief Verifica se una variabile di sessione esiste
     *
     * @param string $name nome della variabile di sessione
     * @return TRUE se esiste, FALSE altrimenti
     */
    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * @brief Distrugge una variabile di sessione
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
     * @brief Distrugge la sessione corrente
     * @return FALSE
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
