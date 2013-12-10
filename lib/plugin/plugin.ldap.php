<?php
/**
 * @file plugin.ldap.php
 * @brief Contiene la classe Ldap
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria di connessione ai server LDAP
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * 
 * Modificare il metodo Authentication della classe Access (class.Access.php)
 * @code
		private function AuthenticationMethod($user, $pwd){

			$registry = registry::instance();
			
			include_once(PLUGIN_DIR.OS."plugin.ldap.php");
			
			$ldap = new Ldap($user, $pwd);
			if(!$ldap->authentication())
				return false;
			
			$user = User::getFromUserPwd($user, $pwd);
			if($user) {
				$this->session->user_id = $user->id;
				$this->session->user_name = htmlChars($user->firstname.' '.$user->lastname);
				if($registry->sysconf->log_access) {
					$this->logAccess($user->id);
				}
				return true;
			}
			return false;
		}
 * @endcode
 * 
 * FIAT
 * @code
 * $ldap = ldap_connect('ldaps://151.92.204.211', 636);
 * $ldap_bind_res = ldap_bind($ldap, 'A006471', 'OMG2013!');
 * var_dump($ldap_bind_res);
 *
 * $ldap_search = ldap_search($ldap, 'CN=ProxyUsers, CN=fgadam,dc=fg,dc=local', 'CN=F29372A');
 * var_dump($ldap_search);
 *
 * $ldap_search_data = ldap_get_entries($ldap, $ldap_search);
 * $ldap_bind_res2 = ldap_bind($ldap, $ldap_search_data[0]['dn'], '************');
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
	 * Contenitore dei log
	 * 
	 * @var string
	 */
	private $_ldap_log;
	
	private $_user_name;
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
	
	/**
	 * Costruttore
	 * 
	 * @param string $username username di accesso
	 * @param string $password password dell'utente
	 * @param array $params parametri di connessione al server Ldap
	 *   - @b host (string)
	 *   - @b port (integer) ...
	 * @return void
	 */
	function __construct($username, $password, $params=array()) {
		
		$this->_ldap_host = 'ldaps://vldap.fg.local';
		$this->_ldap_port = '636';
		$this->_ldap_base_dn = 'A006471';	// 'CN=Administration,CN=fgadam,dc=fg,dc=local';
		$this->_ldap_search_dn = 'CN=ProxyUsers,CN=fgadam,dc=fg,dc=local';
		
		$this->_ldap_username = 'A006471';
		$this->_ldap_password = 'OMG2013!';
		
		$this->_ldap_info = '';	// es.: ou=Person,dc=example,dc=it
		$this->_ldap_domain = 'FGCORP';	// es.: @example.it (per la costruzione degli indirizzi email, account+dominio)
		$this->_ldap_protocol_version = '';
		
		$this->_filter_search = "(CN=$username)";  // es.: "sn=S*"
		$this->_justthese_search = array("CN");
		
		$this->_user_name = $username;
		$this->_user_password = $password;
		
		$this->_debug = false;
		$this->_ldap_log = '';
		$this->_auth = false;
		
		$this->_test_anonymous = false;
		$this->_test_search = false;
	}
	
	/**
	 * Autenticazione Ldap
	 * 
	 * @return boolean
	 */
	public function authentication() {
		
		$this->connection();
		$this->binding();
		
		return $this->_auth;
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
	 * 
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
