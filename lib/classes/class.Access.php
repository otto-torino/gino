<?php
/**
 * @file class.Access.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Access
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

use \Gino\App\Auth\User;
use \Gino\Http\Redirect;

/**
 * @brief Classe per la gestione dell'autenticazione ed accesso alla funzionalità
 * 
 * La classe gestisce il processo di autenticazione e l'accesso al sito e alle sue funzionalità
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Access {

    protected $_home;
    protected $_db, $_session;

    /**
     * @brief Costruttore
     * @return istanza di Gino.Access
     */
    function __construct(){

        $this->_db = Db::instance();
        $this->_session = Session::instance();

    }

    /**
     * @brief Autenticazione all'applicazione
     *
     * Parametri POST: \n
     *     - @a action (string), con valore auth (procedura di autenticazione)
     *     - @a user (string), lo username
     *     - @a pwd (string), la password
     *
     * Parametri GET: \n
     *     - @a action (string), con valore logout (procedura di logout)
     *
     * @see AuthenticationMethod()
     * @see loginSuccess()
     * @see loginError()
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect o FALSE se non avvengono autenticazione e logout
     */
    public function Authentication(\Gino\Http\Request $request){

        Loader::import('auth', 'User');

        if($request->checkPOSTKey('action', 'auth')) {
            $user = cleanVar($request->POST, 'user', 'string', '');
            $password = cleanVar($request->POST, 'pwd', 'string', '');
            $result = $this->AuthenticationMethod($user, $password);
            $request->user = new User($this->_session->user_id);
            return $result ? $this->loginSuccess($request) : $this->loginError($request);
        }
        elseif($request->checkGETKey('action', 'logout')) {
            $this->_session->destroy();
            return new \Gino\Http\Redirect($request->META['SCRIPT_FILE_NAME']);
        }
        else {
            $request->user = new User($this->_session->user_id);
            return FALSE;
        }
    }

    /**
     * @brief Autenticazione errata
     *
     * @description Setta l'errore in sessione e ritorna una Gino.Http.Redirect
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect alla pagina di autenticazione
     */
    private function loginError(\Gino\Http\Request $request) {

        $registry = registry::instance();
        $url = $registry->router->link('auth', 'login');
        return Error::errorMessage(array('error'=>_("autenticazione errata")), $url);
    }

    /**
     * @brief Autenticazione valida
     *
     * @description Reindirizza alla home page o all'url impostato in sessione (auth_redirect).
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect
     */
    private function loginSuccess(\Gino\Http\Request $request) {

        $url = $this->_session->auth_redirect ? $this->_session->auth_redirect : $request->META['SCRIPT_FILE_NAME'];
        return new Redirect($url);
    }

    /**
     * @brief Verifica utente/password
     *
     * @description Imposta le variabili di sessione user_id, user_name, e richiama il metodo logAccess()
     *
     * @see User::getFromUserPwd()
     * @see logAccess()
     * @param string $user
     * @param string $pwd
     * @return risultato autenticazione, bool
     */
    private function AuthenticationMethod($user, $pwd){

        $registry = registry::instance();

        // Uncomment fo ldap
        /*
        include_once(PLUGIN_DIR.OS."plugin.ldap.php");
        $ldap = new \Gino\Plugin\Ldap($user, $pwd);
        if(!$ldap->authentication()) {
            return FALSE;
        }
        */

        $user = \Gino\App\Auth\User::getFromUserPwd($user, $pwd);
        if($user) {
            $this->_session->user_id = $user->id;
            $this->_session->user_name = \Gino\htmlChars($user->firstname.' '.$user->lastname);

            if($registry->sysconf->log_access) {
                $this->logAccess($user->id);
            }
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @brief Registra il log dell'accesso all'applicazione
     *
     * @param integer $userid valore ID dell'utente
     * @return risultato operazione, bool
     */
    private function logAccess($userid) {

        Loader::import('statistics', 'LogAccess');

        \date_default_timezone_set('Europe/Rome');

        $log_access = new \Gino\App\Statistics\LogAccess(null);
        $log_access->user_id = $userid;
        $log_access->date = date("Y-m-d H:i:s");

        return $log_access->save();

    }

    /**
     * @brief Verifica che l'utente si amministratore del sito
     *
     * @description Se la condizione non è verificata getta una Gino.Exception.Exception403
     * @return void
     */
    public function requireAdmin() {
        $request = \Gino\Http\Request::instance();
        if(!$request->user->is_admin) {
            throw new \Gino\Exception\Exception403();
        }
    }

    /**
     * @brief Verifica se l'utente non ha almeno uno dei permessi dati
     *
     * @description Se la condizione non è verificata getta una Gino.Exception.Exception403 se l'utente è autenticato,
     *              altrimenti reindirizza alla pagina di login e ferma l'esecuzione
     * @param string $class nome classe senza namespace
     * @param string $perm codice permesso
     * @param int $instance id istanza modulo
     * @return void
     */
    public function requirePerm($class, $perm, $instance = 0) {

        $request = \Gino\Http\Request::instance();
        if(!$request->user->hasPerm($class, $perm, $instance)) {
            if($this->_session->user_id) {
                throw new \Gino\Exception\Exception403();
            }
            else
            {
                throw new \Gino\Exception\Exception403(array('redirect' => TRUE));
            }
        }
    }
}
