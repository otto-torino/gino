<?php
/**
 * @file class.access.php
 * @brief Contiene la classe Access
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe per la gestione dell'autenticazione ed accesso alla funzionalità
 * 
 * La classe gestisce il processo di autenticazione e l'accesso al sito e alle sue funzionalità
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Access {
  
  protected $_home;
  protected $_crypt;
  protected $_db, $_session;

  private $_block_page;

  /**
   * Costruttore
   */
  function __construct(){

    $this->_db = db::instance();
    $this->_session = session::instance();

    $this->_home = HOME_FILE;
    $this->_crypt = pub::getConf('password_crypt');
    
    $this->_block_page = $this->_home."?evt[auth-login]";
  }

  /**
   * Autenticazione all'applicazione
   * 
   * @see AuthenticationMethod()
   * @see loginSuccess()
   * @see loginError()
   * @return void
   * 
   * Parametri POST: \n
   *   - @a action (string), con valore auth (procedura di autenticazione)
   *   - @a user (string), lo username
   *   - @a pwd (string), la password
   * 
   * Parametri GET: \n
   *   - @a action (string), con valore logout (procedura di logout)
   */
  public function Authentication(){

    Loader::import('auth', 'User');
    
    if((isset($_POST['action']) && $_POST['action']=='auth')) {
      $user = cleanVar($_POST, 'user', 'string', '');
      $password = cleanVar($_POST, 'pwd', 'string', '');
      $this->AuthenticationMethod($user, $password) ? $this->loginSuccess() : $this->loginError(_("autenticazione errata"));
    }
    elseif((isset($_GET['action']) && $_GET['action']=='logout')) {
      $this->_session->destroy();
      header("Location: ".$this->_home);
    }
  }
  
  /**
   * Reindirizza a seguito di una autenticazione non valida
   * 
   * @param string $message
   */
  private function loginError($message) {

    $self = $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING']:'');

    exit(error::errorMessage(array('error'=>$message), $self));
  }

  /**
   * Reindirizza a seguito di una autenticazione valida
   * 
   * Reindirizza alla home page o alla pagina indicata nell'HTTP_REFERER.
   */
  private function loginSuccess() {

    $redirect = isset($this->_session->auth_redirect) ? $this->_session->auth_redirect : $this->_home;

    header("Location: ".$redirect);
    exit();
  }

  /**
   * Verifica utente/password
   * 
   * Imposta le variabili di sessione user_id, user_name, e richiama il metodo logAccess()
   * 
   * @see User::getFromUserPwd()
   * @see logAccess()
   * @param string $user
   * @param string $pwd
   * @return boolean
   */
  private function AuthenticationMethod($user, $pwd){

    $registry = registry::instance();

    /*include_once(PLUGIN_DIR.OS."plugin.ldap.php");
    
    $ldap = new \Gino\Plugin\Ldap($user, $pwd);
    if(!$ldap->authentication())
      return false;*/
    
    $user = \Gino\App\Auth\User::getFromUserPwd($user, $pwd);
    if($user) {
      $this->_session->user_id = $user->id;
      $this->_session->user_name = htmlChars($user->firstname.' '.$user->lastname);
      if($registry->sysconf->log_access) {
        $this->logAccess($user->id);
      }
      return true;
    }

    return false;
  }
  
  /**
   * Registra il log dell'accesso all'applicazione
   * 
   * @param integer $userid valore ID dell'utente
   * @return boolean
   */
  private function logAccess($userid) {

    Loader::import('statistics', 'LogAccess');

    \date_default_timezone_set('Europe/Rome');

    $log_access = new \Gino\App\Statistics\LogAccess(null);
    $log_access->user_id = $userid;
    $log_access->date = date("Y-m-d H:i:s");

    return $log_access->updateDbData();

  }

	/**
	 * Raise 403 se l'utente non è amministratore
	 */
	public function requireAdmin() {
		
		$registry = registry::instance();
		if(!$registry->user->is_admin) {
			Error::raise403();
		}
	}
	
	/**
	 * Raise 403 se l'utente non ha almeno uno dei permessi dati
	 */
	public function requirePerm($class, $perm, $instance = 0) {

		$request = HttpRequest::instance();
		if(!$request->user->hasPerm($class, $perm, $instance)) {
			
            if($this->session->user_id) {
                throw Exception403();
            }
			else
			{
				header("Location: ".$this->_home."?evt[auth-login]");
				exit();
			}
		}
	}

}
?>
