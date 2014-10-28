<?php
/**
 * @file plugin.ldap.php
 * @brief Contiene la classe Ldap
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

require_once("config.ldap.php");

/**
 * @brief Libreria di connessione ai server LDAP
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ###UTILIZZO
 * Modificare il metodo AuthenticationMethod() della classe Access (class.Access.php) in modo da implementare l'autenticazione attraverso ldap. \n
 * Per poter accedere alle funzionalità di gino deve essere tuttavia creato un utente da associare a quello ldap. 
 * Il metodo più semplice è quello di creare un utente con lo username uguale a quello ldap. \n
 * Un esempio di modifica del metodo AuthenticationMethod: 
 * @code
 * private function AuthenticationMethod($user, $pwd){
 *   $registry = registry::instance();
 *   
 *   include_once(PLUGIN_DIR.OS."plugin.ldap.php");
 *   
 *   // per evitare che vengano bloccati gli accessi con gli username scritti nella forma "dominio\username"
 *   if(preg_match("#(\\\)+#", $user))
 *   {
 *     $array = explode('\\', $user);
 *     $last = count($array)-1;
 *     $user = $array[$last];
 *   }
 *   $ldap = new Ldap($user, $pwd);
 *   if(!$ldap->authentication())
 *     return false;
 *   
 *   $user = User::getFromUserPwd($user, $pwd);
 *   if($user) {
 *     ...
 *   }
 *   return false;
 * }
 * @endcode
 */
class Ldap {

	/**
	 * Indirizzo del server
	 * 
	 * @var string
	 */
	private $_ldap_host;
	
	/**
	 * Numero della porta di connessione (ldap 389, ldaps 636)
	 * 
	 * @var integer
	 */
	private $_ldap_port;
	
	/**
	 * Parametri di connessione al server
	 * 
	 * @var string
	 */
	private $_ldap_base_dn;
	
	/**
	 * Ldap link identifier
	 * 
	 * @var string or false
	 */
	private $_ldap_ds;
	
	/**
	 * Username dell'applicazione
	 * 
	 * @var mixed
	 */
	private $_ldap_username;
	
	/**
	 * Password dell'applicazione
	 * 
	 * @var mixed
	 */
	private $_ldap_password;
	
	/**
	 * Contenitore dei log
	 * 
	 * @var string
	 */
	private $_ldap_log;
	
	private $_ldap_domain, $_ldap_protocol_version, $_filter_search, $_justthese_search;
	
	/**
	 * Username di accesso
	 * 
	 * @var mixed
	 */
	private $_user_name;
	
	/**
	 * Password di accesso
	 * 
	 * @var mixed
	 */
	private $_user_password;
	
	/**
	 * Debug (se attivo mostra i log)
	 * 
	 * @var boolean
	 */
	private $_debug;
	
	/**
	 * Riporta se l'utente ha effettuato l'accesso al server Ldap
	 * 
	 * @var boolean
	 */
	private $_auth;
	
	/**
	 * Effettua il test di accesso anonimo (typically read-only access)
	 * 
	 * @var boolean
	 */
	private $_test_anonymous;
	
	private $_test_search;
	
	/**
	 * Costruttore
	 * 
	 * @param string $username username di accesso
	 * @param string $password password dell'utente
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b debug (boolean)
	 * @return void
	 */
	function __construct($username, $password, $options=array()) {
		
		$debug = gOpt('debug', $options, false);
		
		$this->_ldap_host = LDAP_HOST;
		$this->_ldap_port = LDAP_PORT;
		$this->_ldap_base_dn = LDAP_BASE_DN;
		$this->_ldap_search_dn = LDAP_SEARCH_DN;
		
		$this->_ldap_username = LDAP_APP_USERNAME;
		$this->_ldap_password = LDAP_APP_PASSWORD;
		
		$this->_ldap_domain = LDAP_DOMAIN;
		$this->_ldap_protocol_version = LDAP_PROTOCOL_VERSION;
		
		$this->_filter_search = "(".LDAP_FILTER_SEARCH."=$username)";  // es.: "sn=S*"
		$this->_justthese_search = array(LDAP_FILTER_SEARCH);	// ("CN")
		
		$this->_user_name = $username;
		$this->_user_password = $password;
		
		$this->_debug = $debug;
		$this->_ldap_log = '';
		$this->_auth = false;
		
		$this->_test_anonymous = false;
		$this->_test_search = false;
	}
	
	/**
	 * Autenticazione Ldap
	 * 
	 * @see checkValidUsername()
	 * @see printError()
	 * @see connection()
	 * @see binding()
	 * @return boolean
	 */
	public function authentication() {
		
		if(!$this->checkValidUsername())
		{
			$this->_ldap_log = "<p>Lo username è stato inserito in un formato non valido</p>";
			$this->printError();
			return false;
		}
		
		$this->connection();
		$this->binding();
		
		return $this->_auth;
	}
	
	/**
	 * Verifica se il nome utente è in un formato valido
	 * 
	 * Uno username inserito nella forma "dominio\username" ritorna un errore quando viene utilizzato come filtro di ricerca.
	 * 
	 * @return boolean
	 */
	private function checkValidUsername() {
		
		if(preg_match("#(\\\)+#", $this->_user_name))
		{
			return false;
			/*
			// per uniformare lo username togliendo il nome di dominio
			$array = explode('\\', $user);
			$last = count($array)-1;
			$user = $array[$last];
			*/
		}
		else return true;
	}
	
	/**
	 * Stampa le informazioni sulle procedure di connessione
	 * 
	 * Se il debug è attivo stampa tutti i log.
	 * 
	 * @param string $string se presente ritorna il log fino al momento della chiamata
	 */
	private function printError($string=null) {
		
		$text = '';
		
		if($this->_debug && $string)
		{
			$text .= $this->_ldap_log;
			$text .= $string;
			
			return $string;
		}
		elseif($this->_debug)
		{
			$text .= $this->_ldap_log;
			echo $text;
		}
		else return null;
	}
	
	/**
	 * Connessione Ldap
	 * 
	 * @return void or string (log)
	 */
	private function connection() {
		
		ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
		
		$this->_ldap_log = "<h3>LDAP query test</h3>";
		$this->_ldap_log .= "<p>Connecting to ".$this->_ldap_host;
		if($this->_ldap_port)
			$this->_ldap_log .= ":".$this->_ldap_port;
	
		$this->_ldap_log .= "...</p>";
		
		$ds = ldap_connect($this->_ldap_host, $this->_ldap_port)
    		or die($this->printError("Could not connect to ".$this->_ldap_host));
		
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

		$this->_ldap_log .= "<p>connect result is ".$ds."</p>";
		
		$this->_ldap_ds = $ds;
		
		$this->printError();
	}
	
	private function binding() {
		
		if(!$this->_ldap_ds) exit($this->printError($this->printError("<h4>Unable to connect to LDAP server</h4>")));
		
		$this->_ldap_log = "Binding ..."; 
		
		if($this->_test_anonymous)
		{
			$ldapbind = ldap_bind($this->_ldap_ds);
			
			if($ldapbind){
				$this->_ldap_log .= "LDAP bind anonymous successful...";
			} else {
				$this->_ldap_log .= "LDAP bind anonymous failed...";
				$this->_ldap_log .= "<br />msg:'".ldap_error($this->_ldap_ds)."'</br>";
			}
		}
		else
		{
			$this->_ldap_log .= "<p>dn: ".$this->_ldap_base_dn." ...</p>";
			
			$ldapbind = ldap_bind($this->_ldap_ds, $this->_ldap_base_dn, $this->_ldap_password);
			
			if($ldapbind)
			{
				$this->_ldap_log .= "<p>LDAP bind successful!</p>";
				
				$this->search();
			}
			else
			{
				$this->_ldap_log .= "<p>LDAP bind failed...</p>";
				$this->_ldap_log .=  "<p>msg:'".ldap_error($this->_ldap_ds)."'</p>";
			}
		}
		
		$this->_ldap_log .= "<p>Closing connection</p>";
		ldap_close($this->_ldap_ds);
		
		$this->printError();
	}
	
	/**
	 * Ricerca Ldap
	 * 
	 * @return boolean (authentication) or string (log)
	 * 
	 * Esempio di output di ldap_get_entries
	 * @code
	 * Array
	 * (
	 *   [count] => 1
	 *   [0] => Array
	 *   (
	 *     [cn] => Array
	 *     (
	 *       [count] => 1
	 *       [0] => A006471
	 *     )
	 * 
	 *     [0] => cn
	 *     [count] => 1
	 *     [dn] => CN=A006471,CN=Administration,CN=fgadam,DC=fg,DC=local
	 *   )
	 * )
	 * @endcode
	 */
	private function search() {
		
		$search = ldap_search($this->_ldap_ds, $this->_ldap_search_dn, $this->_filter_search, $this->_justthese_search)
			or die($this->printError("Error in search query: ".ldap_error($this->_ldap_ds)));

		$number_returned = ldap_count_entries($this->_ldap_ds, $search);
		$data = ldap_get_entries($this->_ldap_ds, $search);

		// SHOW ALL DATA
		$this->_ldap_log .= "<h1>Dump all data ($number_returned)</h1>";
		$this->_ldap_log .= "<pre>";
		$this->_ldap_log .= print_r($data);
		$this->_ldap_log .= "</pre>";

		// Dati dell'utente
		$this->_ldap_log .= "<h2>User data</h2>";
		
		if($number_returned == 0)
		{
			$this->_ldap_log .= "<p>not valid...</p>";
		}
		elseif($number_returned == 1)
		{
			$d_cn = $data[0]["cn"][0];
			$this->_ldap_log .= "<p>cn entry is: ".$d_cn."</p>";
			
			$d_dn = $data[0]["dn"];
			$this->_ldap_log .= "<p>dn entry is: ".$d_dn."</p>";
			
			$ldap_user_bind = ldap_bind($this->_ldap_ds, $d_dn, $this->_user_password);
			if($ldap_user_bind){
				$this->_ldap_log .= "<p>LDAP User bind successful!</p>";
				
				$this->_auth = true;
			}
			else
			{
				$this->_ldap_log .= "<p>LDAP User bind failed...</p>";
			}
		}
		else $this->_ldap_log .= "<p>result is > 1</p>";
		
		return null;
	}
}
?>
