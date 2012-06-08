<?php
/**
 * @file main.php
 * @brief Imposta le caratteristiche principali dell'applicazione
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * Include il file che gestisce la connessione al database
 */
include_once(CLASSES_DIR.OS."class.db.php");

/**
 * Include la classe Link
 */
include_once(CLASSES_DIR.OS."class.link.php");

/**
 * Include la classe pub
 */
include_once(CLASSES_DIR.OS."class.pub.php");

/**
 * @brief Imposta l'header, le lingue di riferimento, ed effettua il detection del mobile
 * 
 * Inoltre:
 *   - effettua quelle operazioni che devono essere portate a termine prima di proseguire con la costruzione della pagina (vedi l'autenticazione)
 *   - include il file lib/include.php
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Main{

	private $_db, $session;
	private $_auth;
	private $_multi_language;
	private $_tbl_language;

	/**
	 * Richiama i metodi che impostano le caratteristiche principali dell'applicazione
	 * @return void
	 */
	function __construct(){

		$this->_db = db::instance();
		$this->session = session::instance();
		
		include_once(LIB_DIR.OS."include.php");

		$this->_multi_language = pub::getMultiLanguage();
		$this->_tbl_language = 'language';
		$this->setLanguage();
		$this->setGettext();
		$this->setHeaders();

		/* mobile detection */
		$avoid_mobile = preg_match("#(&|\?)avoid_mobile=(\d)#", $_SERVER['REQUEST_URI'], $matches)
			? (int) $matches[2]
			: null;

		if($avoid_mobile) {
			unset($this->session->L_mobile);
			$this->session->L_avoid_mobile = 1;
		}
		elseif($avoid_mobile === 0) {
			unset($this->session->L_avoid_mobile);
		}

		if(!(isset($this->session->L_avoid_mobile) && $this->session->L_avoid_mobile)) {
			$this->detectMobile();
		}

		$this->checkAuthenticationActions();
	}

	private function detectMobile() {
		
		$detect = new Mobile_Detect();

		if($detect->isMobile()) {
			
			$this->session->L_mobile = 1;
		}
	}
	
	private function setHeaders() {

		if(isset($_REQUEST['logout'])) {
			header('cache-control: no-cache,no-store,must-revalidate'); // HTTP 1.1.
			header('pragma: no-cache'); // HTTP 1.0.
			header('expires: 0');
		}
	}

	/**
	 * Verifica il processo di autenticazione (o logout)
	 * 
	 * @see access::authentication()
	 * @return void
	 */
	private function checkAuthenticationActions() {
		
		$access = new access();
		$access->authentication();
	}

	private function setGettext(){

		if(isset($this->session->lng))
		{
			$language = explode('_', $this->session->lng);
			$lang = $language[0];
			
			if(!ini_get('safe_mode'))
				putenv("LC_ALL=".$this->session->lng);
			
			setlocale(LC_ALL, $this->session->lng.'.utf8');
		}
		else $lang = '';
		
		define('LANG', $lang);	// class document
		
		if(!extension_loaded('gettext'))
		{
			function _($str){
				return $str;
			}
		}
		else	// Gettext Functions
		{
			$domain='messages';
			bindtextdomain($domain, "./languages");
			bind_textdomain_codeset($domain, 'UTF-8');
			textdomain($domain);	// choose domain
		}
	}

	private function setLanguage(){

		/* default */
		if($this->_multi_language == 'yes')
		{
			if(!$this->session->lngDft)
			{
				$query = "SELECT code FROM ".$this->_tbl_language." WHERE main='yes'";
				$result = mysql_query($query);
				if(mysql_num_rows($result) > 0)
				{
					while ($row = mysql_fetch_assoc($result)) {
						$this->session->lngDft = $row['code'];
					}
				}
			}

			// language
			if(!$this->session->lng)
			{
				// Language User Agent
				$user_language = $this->userLanguage();
				$this->session->lng = $user_language ? $user_language : '';
			}

			if(isset($_GET["lng"]))
			{
				$this->session->lng = $_GET["lng"];
			}
			elseif($this->session->lng == '')
			{
				$this->session->lng = $this->session->lngDft;
			}
		}
		else
		{
			$this->session->lng = pub::getDftLanguage();
			$this->session->lngDft = pub::getDftLanguage();
		}
	}

	/**
	 * Lingua dello User Agent
	 * 
	 * Ritorna FALSE se non trova la lingua
	 * 
	 * @see get_languages()
	 * @return string
	 */
	private function userLanguage(){

		$code = $this->get_languages('data');

		if(is_array($code[0]) AND sizeof($code[0]) > 0)
		{
			$full_code = $code[0][0];
			//$primary_code = $code[0][1];

			if(!empty($full_code))
			{
				$array = explode('-', $full_code);
				$lang = $array[0];

				if(sizeof($array) == 2)
				{
					$country = strtoupper($array[1]);
					$language = $lang.'_'.$country;

					$query = "SELECT code FROM ".$this->_tbl_language." WHERE code='$language'";
					$result = mysql_query($query);
					if(mysql_num_rows($result) > 0) return $language;
				}
				elseif(sizeof($array) == 1)
				{
					$query = "SELECT code, main FROM ".$this->_tbl_language." WHERE active='yes'";
					$result = mysql_query($query);
					if(mysql_num_rows($result) > 0)
					{
						while ($row = mysql_fetch_assoc($result)) {

							$lang_from_db = explode('_', $row['code']);
							if($lang == $lang_from_db) return $row['code'];
						}
					}
				}
			}
			return false;
		}
	}

	/**
	
	*/
	
	/**
	 * Lingua
	 * 
	 * @see detectCodes()
	 * @param string $feature accetta i valori @a data e @a header
	 * @return array
	 * 
	 * @todo sviluppare il codice relativo al valore @a header
	 * 
	 * 'header' - sets header values, for redirects etc. No data is returned
	 * 'data' - for language data handling, ie for stats, etc.
	 * 
	 * Returns an array of the following 4 item array for each language the os supports:
	 * 1. full language abbreviation, like en-ca
	 * 2. primary language, like en
	 * 3. full language string, like English (Canada)
	 * 4. primary language string, like English
	 * 
	 * Esempio
	 * @code
	 * $_SERVER["HTTP_ACCEPT_LANGUAGE"]:
	 * en-gb,en;q=0.5 [Firefox]
	 * it-it,it;q=0.8,en-us;q=0.5,en;q=0.3 [Firefox]
	 * it [IE7]
	 * @endcode
	 */
	private function get_languages($feature)
	{
		$a_languages = $this->detectCodes();
		$found = false;
		$user_languages = array();

		if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
		{
			$languages = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
			$languages = str_replace( ' ', '', $languages );
			$languages = explode( ",", $languages );

			foreach($languages as $language_list)
			{
				$temp_array = array();
				// slice out the part before ; on first step, the part before - on second, place into array
				$temp_array[0] = substr($language_list, 0, strcspn($language_list, ';'));	// full language
				// strcspn — Find length of initial segment not matching mask (in this case ';')
				$temp_array[1] = substr($language_list, 0, 2);	// cut out primary language
				$user_languages[] = $temp_array;
			}

			//start going through each one
			for($i = 0, $limit=count($user_languages); $i < $limit; $i++)
			{
				foreach($a_languages as $key => $value)
				{
					if($key == $user_languages[$i][0])
					{
						// complete language, like english (canada)
						$user_languages[$i][2] = $value;
						// extract working language, like english
						$user_languages[$i][3] = substr($value, 0, strcspn( $value, ' (' ));
					}
				}
			}
		}
		else
		{
			$user_languages[0] = array('','','','');
		}

		if ($feature == 'data') return $user_languages;
		// this is just a sample, replace target language and file names with your own.
		elseif ($feature == 'header')
		{
			$found = false;
			
			switch($user_languages[0][1])// get default primary language, the first one in array that is
			{
				case 'en':
					$location = 'english.php';
					$found = true;
					break;
				case 'fr':
					$location = 'french.php';
					$found = true;
					break;
				case 'it':
					$location = 'italian.php';
					$found = true;
					break;
				case 'sp':
					$location = 'spanish.php';
					$found = true;
					break;
				default:
					break;
			}

			if($found)
			{
				header("Location: $location");
			}
			else
			{
				header("Location: default.php");
			}
		}
	}

	/**
	 * Elenco dei codici lingua associati alle nazioni
	 * 
	 * @return array codice_stato=>nome_stato
	 */
	public function detectCodes(){

		return array(
		'af' => 'Afrikaans',
		'sq' => 'Albanian',
		'ar-dz' => 'Arabic (Algeria)',
		'ar-bh' => 'Arabic (Bahrain)',
		'ar-eg' => 'Arabic (Egypt)',
		'ar-iq' => 'Arabic (Iraq)',
		'ar-jo' => 'Arabic (Jordan)',
		'ar-kw' => 'Arabic (Kuwait)',
		'ar-lb' => 'Arabic (Lebanon)',
		'ar-ly' => 'Arabic (libya)',
		'ar-ma' => 'Arabic (Morocco)',
		'ar-om' => 'Arabic (Oman)',
		'ar-qa' => 'Arabic (Qatar)',
		'ar-sa' => 'Arabic (Saudi Arabia)',
		'ar-sy' => 'Arabic (Syria)',
		'ar-tn' => 'Arabic (Tunisia)',
		'ar-ae' => 'Arabic (U.A.E.)',
		'ar-ye' => 'Arabic (Yemen)',
		'ar' => 'Arabic',
		'hy' => 'Armenian',
		'as' => 'Assamese',
		'az' => 'Azeri',
		'eu' => 'Basque',
		'be' => 'Belarusian',
		'bn' => 'Bengali',
		'bg' => 'Bulgarian',
		'ca' => 'Catalan',
		'zh-cn' => 'Chinese (China)',
		'zh-hk' => 'Chinese (Hong Kong SAR)',
		'zh-mo' => 'Chinese (Macau SAR)',
		'zh-sg' => 'Chinese (Singapore)',
		'zh-tw' => 'Chinese (Taiwan)',
		'zh' => 'Chinese',
		'hr' => 'Croatian',
		'cs' => 'Czech',
		'da' => 'Danish',
		'div' => 'Divehi',
		'nl-be' => 'Dutch (Belgium)',
		'nl' => 'Dutch (Netherlands)',
		'en-au' => 'English (Australia)',
		'en-bz' => 'English (Belize)',
		'en-ca' => 'English (Canada)',
		'en-ie' => 'English (Ireland)',
		'en-jm' => 'English (Jamaica)',
		'en-nz' => 'English (New Zealand)',
		'en-ph' => 'English (Philippines)',
		'en-za' => 'English (South Africa)',
		'en-tt' => 'English (Trinidad)',
		'en-gb' => 'English (United Kingdom)',
		'en-us' => 'English (United States)',
		'en-zw' => 'English (Zimbabwe)',
		'en' => 'English',
		'us' => 'English (United States)',
		'et' => 'Estonian',
		'fo' => 'Faeroese',
		'fa' => 'Farsi',
		'fi' => 'Finnish',
		'fr-be' => 'French (Belgium)',
		'fr-ca' => 'French (Canada)',
		'fr-lu' => 'French (Luxembourg)',
		'fr-mc' => 'French (Monaco)',
		'fr-ch' => 'French (Switzerland)',
		'fr' => 'French (France)',
		'mk' => 'FYRO Macedonian',
		'gd' => 'Gaelic',
		'ka' => 'Georgian',
		'de-at' => 'German (Austria)',
		'de-li' => 'German (Liechtenstein)',
		'de-lu' => 'German (Luxembourg)',
		'de-ch' => 'German (Switzerland)',
		'de' => 'German (Germany)',
		'el' => 'Greek',
		'gu' => 'Gujarati',
		'he' => 'Hebrew',
		'hi' => 'Hindi',
		'hu' => 'Hungarian',
		'is' => 'Icelandic',
		'id' => 'Indonesian',
		'it-ch' => 'Italian (Switzerland)',
		'it' => 'Italian (Italy)',
		'ja' => 'Japanese',
		'kn' => 'Kannada',
		'kk' => 'Kazakh',
		'kok' => 'Konkani',
		'ko' => 'Korean',
		'kz' => 'Kyrgyz',
		'lv' => 'Latvian',
		'lt' => 'Lithuanian',
		'ms' => 'Malay',
		'ml' => 'Malayalam',
		'mt' => 'Maltese',
		'mr' => 'Marathi',
		'mn' => 'Mongolian (Cyrillic)',
		'ne' => 'Nepali (India)',
		'nb-no' => 'Norwegian (Bokmal)',
		'nn-no' => 'Norwegian (Nynorsk)',
		'no' => 'Norwegian (Bokmal)',
		'or' => 'Oriya',
		'pl' => 'Polish',
		'pt-br' => 'Portuguese (Brazil)',
		'pt' => 'Portuguese (Portugal)',
		'pa' => 'Punjabi',
		'rm' => 'Rhaeto-Romanic',
		'ro-md' => 'Romanian (Moldova)',
		'ro' => 'Romanian',
		'ru-md' => 'Russian (Moldova)',
		'ru' => 'Russian',
		'sa' => 'Sanskrit',
		'sr' => 'Serbian',
		'sk' => 'Slovak',
		'ls' => 'Slovenian',
		'sb' => 'Sorbian',
		'es-ar' => 'Spanish (Argentina)',
		'es-bo' => 'Spanish (Bolivia)',
		'es-cl' => 'Spanish (Chile)',
		'es-co' => 'Spanish (Colombia)',
		'es-cr' => 'Spanish (Costa Rica)',
		'es-do' => 'Spanish (Dominican Republic)',
		'es-ec' => 'Spanish (Ecuador)',
		'es-sv' => 'Spanish (El Salvador)',
		'es-gt' => 'Spanish (Guatemala)',
		'es-hn' => 'Spanish (Honduras)',
		'es-mx' => 'Spanish (Mexico)',
		'es-ni' => 'Spanish (Nicaragua)',
		'es-pa' => 'Spanish (Panama)',
		'es-py' => 'Spanish (Paraguay)',
		'es-pe' => 'Spanish (Peru)',
		'es-pr' => 'Spanish (Puerto Rico)',
		'es-us' => 'Spanish (United States)',
		'es-uy' => 'Spanish (Uruguay)',
		'es-ve' => 'Spanish (Venezuela)',
		'es' => 'Spanish (Traditional Sort)',
		'sx' => 'Sutu',
		'sw' => 'Swahili',
		'sv-fi' => 'Swedish (Finland)',
		'sv' => 'Swedish',
		'syr' => 'Syriac',
		'ta' => 'Tamil',
		'tt' => 'Tatar',
		'te' => 'Telugu',
		'th' => 'Thai',
		'ts' => 'Tsonga',
		'tn' => 'Tswana',
		'tr' => 'Turkish',
		'uk' => 'Ukrainian',
		'ur' => 'Urdu',
		'uz' => 'Uzbek',
		'vi' => 'Vietnamese',
		'xh' => 'Xhosa',
		'yi' => 'Yiddish',
		'zu' => 'Zulu' );
	}
}

/**
 * @brief Rende disponibile il metodo HttpCall()
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EvtHandler extends Main{

	/**
	 * Costruisce il costruttore della classe Main
	 */
	function __construct(){

		Main::__construct();
	}

	/**
	 * Costruisce l'indirizzo di un redirect e lo effettua
	 * @param string $file indirizzo del redirect, corrispondente al nome del file base dell'applicazione (ad es. la proprietà @a _home)
	 * @param string $EVT parte dell'indirizzo formata da nome istanza/classe e nome metodo (nel formato @a nomeistanza-nomemetodo)
	 * @param string $params parametri aggiuntivi della request (ad es. var1=val1&var2=val2)
	 * @return void
	 */
	protected function HttpCall($file, $EVT, $params){

		if(!empty($params))
		{
			if(!empty($EVT)) $sign = '&'; else $sign = '?';

			$params = $sign.$params;
		}

		if(!empty($EVT)) $event = "?evt[$EVT]";
		else $event = '';

		header("Location:http://".$_SERVER['HTTP_HOST'].$file.$event.$params);
		exit();
	}
}

/**
 * @brief Rende disponibili i metodi per gestire l'accesso alle funzionalità di una classe
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class AbstractEvtClass extends pub{

	/**
	 * Ruolo di accesso base alle funzionalità
	 */
	protected $_access_base;
	
	/**
	 * Ruolo di accesso
	 */
	protected $_access_2;
	
	/**
	 * Ruolo di accesso
	 */
	protected $_access_3;
	
	/**
	 * Elenco dei gruppi di un utente in riferimento a una data classe
	 */
	protected $_user_group;
	
	/**
	 * Elenco dei gruppi della classe
	 */
	protected $_list_group;
	
	/**
	 * Valore ID dell'istanza
	 */
	protected $_instance;
	
	/**
	 * Nome dell'istanza
	 */
	protected $_instanceName;

	/**
	 * Costruttore
	 */
	function __construct(){

		parent::__construct();

	}
	
	/**
	 * Definisce le proprietà di accesso ai metodi pubblici di una classe/applicazione
	 * 
	 * Il metodo viene richiamato all'interno del costruttore della classe e rende disponibili alla classe le proprietà protette della classe AbstractEvtClass:
	 * 
	 *   - @b $_access_base (integer): ruolo di accesso base alle funzionalità
	 *   - @b $_access_2 (integer): ruolo di accesso 
	 *   - @b $_access_3 (integer): ruolo di accesso
	 *   - @b $_user_group (array): elenco dei gruppi di un utente in riferimento a una data classe
	 *   - @b $_list_group (array): elenco dei gruppi della classe
	 */
	protected function setAccess(){
		
		$this->_access_base = $this->_access->classRole($this->_className, $this->_instance, 'user');
		$this->_access_2 = $this->_access->classRole($this->_className, $this->_instance, 'user2');
		$this->_access_3 = $this->_access->classRole($this->_className, $this->_instance, 'user3');
		
		$this->_user_group = $this->_access->userGroup($this->_className, $this->_instance);
		$this->_list_group = $this->_access->listGroup($this->_className, $this->_instance);
	}
	
	/**
	 * Verifica se un utente possiede un ruolo tale da permettergli di accedere a una pagina
	 * 
	 * Questo metodo viene incluso in testa al metodo della classe che deve costruire la pagina, e viene utilizzato per la visualizzazione di contenuti. 
	 * 
	 * @param integer $role ruolo dell'utente autorizzato a visualizzare la pagina di contenuti definita dal metodo che lo include
	 * @return void
	 */
	protected function accessType($role){
	
		$this->_access->AccessVerifyRoleID($role);
	}
	
	/**
	 * Verifica se un utente appartiene a un gruppo della classe per il quale è abilitato l'accesso a una determinata funzionalità amministrativa
	 * 
	 * Questo metodo viene incluso in testa al metodo della classe e viene utilizzato per la visualizzazione di pagine amministrative e per l'esecuzione di operazioni amministrative.
	 * 
	 * @param mixed $permission gruppi ai quali è concesso l'accesso a una determinata funzione
	 * @return void
	 */
	protected function accessGroup($permission){
	
		$this->_access->AccessVerifyGroup($this->_className, $this->_instance, $this->_user_group, $permission);
	}
	
	/**
	 * Metodo ereditato dalle classi che estendono la classe AbstractEvtClass (le classi sulle quali si basano i moduli)
	 * 
	 * In questo modo queste classi non devono riscrivere il metodo se non devono definire dei permessi di visualizzazione aggiuntivi a quello base.
	 * 
	 * @return array
	 */
	public static function permission(){

		$access_2 = $access_3 = '';
		return array($access_2, $access_3);
	}
}
?>
