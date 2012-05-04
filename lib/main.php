<?php

include_once(CLASSES_DIR.OS."class.db.php");
include_once(CLASSES_DIR.OS."class.link.php");
include_once(CLASSES_DIR.OS."class.pub.php");

class Main{

	private $_db, $session;
	private $_auth;
	private $_multi_language;
	private $_tbl_language;

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

	private function checkAuthenticationActions() {
		
		// check for authentication or logout
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
	Script is currently set to accept 2 parameters, triggered by $feature value.
	for example, get_languages( 'data' ):
	1. 'header' - sets header values, for redirects etc. No data is returned
	2. 'data' - for language data handling, ie for stats, etc.
		Returns an array of the following 4 item array for each language the os supports:
		1. full language abbreviation, like en-ca
		2. primary language, like en
		3. full language string, like English (Canada)
		4. primary language string, like English
		
	Example $_SERVER["HTTP_ACCEPT_LANGUAGE"]:
	en-gb,en;q=0.5 [Firefox]
	it-it,it;q=0.8,en-us;q=0.5,en;q=0.3 [Firefox]
	it [IE7]
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

class EvtHandler extends Main{

	function __construct(){

		Main::__construct();
	}

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

class AbstractEvtClass extends pub{

	protected $_access_base, $_access_2, $_access_3, $_user_group, $_list_group;
	protected $_instance, $_instanceName;

	function __construct(){

		parent::__construct();

	}
		
	protected function setAccess(){
		
		$this->_access_base = $this->_access->classRole($this->_className, $this->_instance, 'user');
		$this->_access_2 = $this->_access->classRole($this->_className, $this->_instance, 'user2');
		$this->_access_3 = $this->_access->classRole($this->_className, $this->_instance, 'user3');
		
		$this->_user_group = $this->_access->userGroup($this->_className, $this->_instance);
		$this->_list_group = $this->_access->listGroup($this->_className, $this->_instance);
	}
	
	protected function accessType($role){
	
		$this->_access->AccessVerifyRoleID($role);
	}
	
	protected function accessGroup($permission){
	
		$this->_access->AccessVerifyGroup($this->_className, $this->_instance, $this->_user_group, $permission);
	}
	
	public static function permission(){

		$access_2 = $access_3 = '';
		return array($access_2, $access_3);
	}
}
?>
