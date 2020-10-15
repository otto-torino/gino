<?php
/**
 * @file class.Locale.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Locale
 */
namespace Gino;

use Gino\App\Sysconf\Conf;
use Gino\App\Sysconf\sysconf;
use Gino\App\Language\Lang;

/**
 * @brief Libreria per la gestione delle traduzioni che non utilizzano le librerie gettext
 * 
 * ##Meccanismi per la gestione delle traduzioni
 * In gino sono previsti due meccanismi per gestire le traduzioni: \n
 * - utilizzo delle librerie gettext
 * - file di stringhe localizzate
 * 
 * La classe locale si prende carico della gestione dei file di stringhe.
 * 
 * La classe locale viene inclusa nel file class.Core.php e viene istanziata come singleton: \n
 *   - nella classe @a Gino.Controller per le classi applicative (che risiedono nella directory app)
 *   - nelle classi modello delle classi applicative che estendono la classe @a Model
 *   - nelle classi non applicative
 * 
 * ###Esempio di richiamo in una classe modello
 *
 * @code
 * $this->_locale = locale::instance_to_class($this->_controller->getClassName());
 * @endcode
 * 
 * ###Esempio di richiamo in una classe non applicativa
 *
 * @code
 * $locale = locale::instance_to_class(get_class());
 * @endcode
 * 
 * ##Directory dei file
 * La definizione delle directory dei file avviene nel metodo @a pathToFile(). \n
 * Mentre il nome del file è comunque sempre nella forma @a [nome_classe]_lang.php, come ad esempio
 * @code
 * app/user/language/en_US/user_lang.php
 * @endcode
 * la directory dove risiedono questi file non è univoca.
 * 
 * Se esiste la directory app/[nome_classe], il file viene cercato nel percorso:
 * @code
 * app/[nome_classe]/language/[codice_lingua]/
 * @endcode
 * 
 * In caso contrario nel percorso:
 * @code
 * languages/[codice_lingua]/
 * @endcode
 * 
 * ##Richiamare le stringhe
 * Per richiamare una stringa si utilizza il metodo @a get passandogli il nome della chiave che identifica la stringa, ad esempio:
 * @code
 * $this->_locale->get('label_phone')
 * @endcode
 * 
 * I file contenenti le stringhe sono così costruiti:
 * @code
 * // versione inglese
 * return array(
 *   'label_name' => 'Name', 
 *   'label_comments' => 'Enabled comments'
 * );
 * // versione italina
 * return array(
 *   'label_name' => 'Nome', 
 *   'label_comments' => 'Abilita i commenti'
 * );
 * @endcode
 */
class Locale extends Singleton {

    private $_session;
    private $_strings;
    
    /**
     * Nome della classe che istanzia
     * @var string
     */
    private $_class_name;
    
    /**
     * Directory dell'app
     * @var string
     */
    private $_app_dir;
    
    /**
     * Lista dei file delle traduzioni
     * @var array (code_language => path_to_file)
     */
    private $_file_list;

    /**
     * Costruttore
     * 
     * @param string $class_name nome della classe che istanzia
     * @return void
     */
    protected function __construct($class_name) {

        $this->_session = Session::instance();
        $this->_strings = array();
        $this->_class_name = $class_name;
        
        $this->_app_dir = get_app_dir($this->_class_name);

        $path_to_file = $this->pathToFile();

        if(file_exists($path_to_file)) {
            $this->_strings = include($path_to_file);
        }
    }

    /**
     * @brief Percorso file traduzioni della classe o quello di default se non presente
     * 
     * @see self::fileName()
     * @see self::pathToBaseDir()
     * @param string $class_name nome classe
     * @return string
     */
    private function pathToFile() {

        $filename = $this->fileName();

        if(!is_dir($this->_app_dir))
        {
            $path_to_file = SITE_ROOT.OS.'languages'.OS.$this->_session->lng.OS.'LC_MESSAGES'.OS.$filename;
        }
        else
        {
            $path_to_file = $this->pathToBaseDir($this->_session->lng).$filename;
        }

        return $path_to_file;
    }

    /**
     * @brief Recupera la traduzione corrispondente alla chiave data
     * @description Se la traduzione non è presente ritorna la chiave stessa
     * @param string $key chiave
     * @return string
     */
    public function get($key) {
        return array_key_exists($key, $this->_strings) ? $this->_strings[$key] : $key;
    }
    
    /**
     * @brief Imposta la lingua del client
     *
     * @return TRUE
     */
    public static function init() {
    
    	$registry = Registry::instance();
    	$session = Session::instance();
    	
    	$init_language = $session->lng;
    	
    	self::setLanguage();
    	
    	$registry->trd = new translation($session->lng, $session->lngDft);
    	
    	return TRUE;
    }

    /**
     * @brief Inizializza le librerie getttext
     *
     * @see lib/global.php
     * @return TRUE
     */
    public static function initGettext() {

        $session = Session::instance();

        if(isset($session->lng))
        {
            $language = explode('_', $session->lng);
            $lang = $language[0];

            if(!ini_get('safe_mode'))
                putenv("LC_ALL=".$session->lng);

            setlocale(LC_ALL, $session->lng.'.utf8');
        }
        else $lang = '';

        define('LANG', $lang);

        return true;
    }

    /**
     * @brief Setta la lingua di navigazione e di default in sessione
     * 
     * @see Gino.App.Language.Lang::getMainLang()
     * @return void
     */
    private static function setLanguage(){

        $registry = Registry::instance();
        $session = Session::instance();
        $db = $registry->db;

        Loader::import('language', 'Lang');
        $dft_language = \Gino\App\Language\Lang::getMainLang();
        
        $session->lngDft = $dft_language;

        /* default */
        if($registry->sysconf->multi_language)
        {
            // language
            if(!$session->lng)
            {
                // Language User Agent
                $user_language = self::userLanguage();
                $session->lng = $user_language ? $user_language : '';
            }

            if(preg_match("#[?&]lng=(\w\w_\w\w)(&.*)?$#", $_SERVER['REQUEST_URI'], $matches))
            {
                $session->lng = $matches[1];
            }
            elseif($session->lng == '')
            {
                $session->lng = $session->lngDft;
            }
        }
        else
        {
            $session->lng = $dft_language;
        }
    }

    /**
     * @brief Lingua dello User Agent
     * 
     * @see get_languages()
     * @return mixed, lingua user agent o FALSE se non trovata
     */
    private static function userLanguage(){

        $db = db::instance();
        $code = self::get_languages();

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

                      $langs = Lang::objects(array(
                          null,
                          'where' => "language_code='$lang' AND country_code='$country'"
                      ));
                      if(count($langs)) {
                        return $langs[0]->code();
                      }
                }
                elseif(sizeof($array) == 1)
                {
                    $records = $db->select('language_code, country_code', TBL_LANGUAGE, "active='1'");
                    if(count($records))
                    {
                        foreach($records AS $r)
                        {
                            if($lang == array($r['language_code'], $r['country_code'])) return implode('_', array($r['language_code'], $r['country_code']));
                        }
                    }
                }
            }
            return FALSE;
        }

        return FALSE;
    }

    /**
     * @brief Recupera le caratteristiche di tutti i locale
     * 
     * Ritorna un array con i seguenti 4 elementi per ogni lingua che l'OS supporta:
     * 1. abbreviazione completa, es. en-ca
     * 2. codice lingua primario, es. en
     * 3. stringa completa lingua, es. English (Canada)
     * 4. stringa lingua primaria, es. English
     *
     * Esempio
     * @code
     * $_SERVER["HTTP_ACCEPT_LANGUAGE"]:
     * en-gb,en;q=0.5 [Firefox]
     * it-it,it;q=0.8,en-us;q=0.5,en;q=0.3 [Firefox]
     * it [IE7]
     * @endcode
     *
     * @see detectCodes()
     * @return array
     */
    private static function get_languages()
    {
        $a_languages = self::detectCodes();
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
                $temp_array[0] = substr($language_list, 0, strcspn($language_list, ';'));    // full language
                // strcspn — Find length of initial segment not matching mask (in this case ';')
                $temp_array[1] = substr($language_list, 0, 2);    // cut out primary language
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

        return $user_languages;
    }

    /**
     * @brief Elenco dei codici lingua associati alle nazioni
     * @return array codice_stato=>nome_stato
     */
    private static function detectCodes(){

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
    
    /**
     * @brief Percorso assoluto della directory dei file delle traduzioni
     *
     * @param string $code codice della lingua
     * @return string
     */
    private function pathToBaseDir($code=null) {
    
        $dir = $this->_app_dir.OS.'language'.OS;
    	
    	if($code) $dir .= $code.OS;
    
    	return $dir;
    }
    
    /**
     * @brief Nome del file delle traduzioni
     *
     * @return string
     */
    private function fileName() {
    
    	$name = $this->_class_name."_lang.php";
    	return $name;
    }
    
    /**
     * Imposta alcune proprietà
     * 
     * @see self::listLocaleFile()
     * @param object $controller
     * @return void
     */
    private function setProperties($controller) {
    	
    	$this->listLocaleFile();
    	
    	$class = get_class($controller);
    	$module_id = $controller->getInstance();
    	
    	if($module_id) {
    		Loader::import('module', 'ModuleInstance');
    		$module = new \Gino\App\Module\ModuleInstance($module_id);
    	}
    	else {
    		Loader::import('sysClass', 'ModuleApp');
    		$module = \Gino\App\SysClass\ModuleApp::getFromName($this->_class_name);
    	}
    	
    	$registry = Registry::instance();
    	$method = $module_id ? 'manageDoc' : 'manage'.ucfirst($this->_class_name);
    	$this->_mdlLink = $registry->router->link($module->name, $method, array(), array('block' => 'locale'));
    }
    
    /**
     * @brief Interfaccia per la gestione dei file delle traduzioni dei moduli
     *
     * @see self::setProperties()
     * @see self::moduleList()
     * @see self::formModuleFile()
     * @see self::actionModuleFile()
     * @param object $controller controller
     * @return string
     */
    public function manageLocale($controller) {
    
    	$request = \Gino\Http\Request::instance();
    	$action = cleanVar($request->GET, 'action', 'string', '');
    
    	$this->setProperties($controller);
    	
    	if($action == 'modify') {
    		$buffer = $this->formModuleFile($request);
    	}
    	elseif($action == 'save') {
    		return $this->actionModuleFile($request);
    	}
    	elseif($action == 'insert') {
    		$buffer = $this->formCreateFile($request);
    	}
    	elseif($action == 'create') {
    		return $this->actionCreateFile($request);
    	}
    	else {
    		$buffer = "<p class=\"backoffice-info\">"._("In questa sezione si possono modificare le stringhe presenti nel codice modificando direttamente i file delle traduzioni.")."</p>";
    		$buffer .= $this->moduleList();
    	}
    
    	$view = new View();
    	$view->setViewTpl('section');
    	$dict = array(
    		'title' => _("Traduzioni"),
    		'class' => 'admin',
    		'content' => $buffer
    	);
    	 
    	return $view->render($dict);
    }
    
    /**
     * @brief Lista dei file delle traduzioni
     * 
     * @see self::pathToBaseDir()
     * @return void
     */
    private function listLocaleFile() {
    
    	$this->_file_list = array();
    
    	$dir = $this->pathToBaseDir();
    	 
    	if(is_dir($dir))
    	{
    		if(substr($dir, -1) != '/') $dir .= OS;
    		 
    		if($dh = opendir($dir))
    		{
    			while(($file = readdir($dh)) !== FALSE)
    			{
    				if($file == "." || $file == "..") continue;
    
    				if(is_dir($dir.$file))	// directory col codice lingua
    				{
    					if($dh_sub = opendir($dir.$file))
    					{
    						while(($file_sub = readdir($dh_sub)) !== FALSE)
    						{
    							if($file_sub == "." || $file_sub == "..") continue;
    
    							if(is_file($dir.$file.OS.$file_sub) && \Gino\extensionFile($file_sub) == 'php')
    							{
    								$this->_file_list[$file] = $dir.$file.OS.$file_sub;
    							}
    						}
    						closedir($dh_sub);
    					}
    				}
    			}
    			closedir($dh);
    		}
    	}
    }
    
    /**
     * @brief Tabella con l'elenco dei file delle traduzioni del modulo
     *
     * @description Utilizza la libraria javascript CodeMirror
     * @return string
     */
    private function moduleList() {
    
    	$title = _("File delle traduzioni");
    
    	$num_items = count($this->_file_list);
    	if($num_items) {
    		$link_insert = "<a href=\"$this->_mdlLink&action=insert\">".\Gino\icon('insert')."</a>";
    		
    		$buffer = "<h2>".$title." $link_insert</h2>";
    		$view_table = new View(null, 'table');
    		$view_table->assign('class', 'table table-striped table-hover');
    		$tbl_rows = array();
    		$tb_rows[] = array(
    			'text' => 'File',
    			'header' => true,
    			'colspan' => 3
    		);
    		foreach($this->_file_list as $k=>$v) {
    			$language = $k;
    			$path_to_file = $v;
    			$filename = basename($path_to_file);
    			 
    			$link_modify = "<a href=\"$this->_mdlLink&key=$language&action=modify\">".\Gino\icon('modify')."</a>";
    			$tbl_rows[] = array(
    				$language,
    				$filename,
    				$link_modify
    			);
    		}
    		$view_table->assign('rows', $tbl_rows);
    		$buffer .= $view_table->render();
    	}
    	else {
    		return '';
    	}
    
    	return $buffer;
    }
    
    /**
     * @brief Form di modifica file
     * @param \Gino\Http\Request oggetto Gino.Http.Request
     * @return string
     */
    private function formModuleFile($request) {
    
    	$key = cleanVar($request->GET, 'key', 'string', '');
    	
    	$codemirror = \Gino\Loader::load('CodeMirror', array(['type' => 'view']));
    	 
    	$path_to_file = $this->pathToBaseDir($key).$this->fileName();
    	 
    	$title = sprintf(_("Modifica il file delle traduzioni \"%s\""), $key);
    
    	$gform = Loader::load('Form', array(array('form_id'=>'gform')));
    	$gform->load('dataform');
    	
    	$buffer = $gform->open($this->_mdlLink."&action=save&key=$key", '', '');
    
    	$contents = file_get_contents($path_to_file);
    	
    	$buffer .= $codemirror->inputText('file_content', $contents, ['classField' => null]);
        
    	$buffer .= "<div class=\"form-group\">";
    	$buffer .= \Gino\Input::submit('submit_action', _("salva"));
    	$buffer .= \Gino\Input::submit('cancel_action', _("annulla"), ['onclick' => "location.href='$this->_mdlLink'"]);
    	$buffer .= "</div>";
    	
    	$buffer .= $gform->close();
    	
    	$buffer .= $codemirror->renderScript();
    
    	$view = new View(null, 'section');
    	$dict = array(
    		'title' => $title,
    		'class' => null,
    		'content' => $buffer
    	);
    
    	return $view->render($dict);
    }
    
    /**
     * @brief Processa il form di modifica di un file
     * @param \Gino\Http\Request oggetto Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    private function actionModuleFile($request) {
    
    	$key = cleanVar($request->GET, 'key', 'string', '');
    
    	$filename = $this->fileName();
    
    	$file_content = filter_input(INPUT_POST, 'file_content');
    	$fo = fopen($this->pathToBaseDir($key).$filename, 'wb');
    	fwrite($fo, $file_content);
    	fclose($fo);
    
    	return new \Gino\Http\Redirect($this->_mdlLink);
    }
    
    /**
     * @brief Form di creazione di un file delle traduzioni
     * @param \Gino\Http\Request oggetto Gino.Http.Request
     * @return string
     */
    private function formCreateFile($request) {
    
    	$gform = Loader::load('Form', array(array('form_id'=>'gform')));
    	$gform->load('dataform');
    
    	$title = _("Crea un nuovo file");
    	$subtitle = _("Scegliere per quale lingua creare il file delle traduzioni");
    
    	$buffer = $gform->open($this->_mdlLink."&action=create", '', 'lang');
    	
    	$db = Db::instance();
    	
    	$concat = $db->concat(array('language_code', "'_'", 'country_code'));
    	$codes = array_keys($this->_file_list);
    	$where = "$concat NOT IN ('".implode("','", $codes)."')";
    	$query = $db->query($concat." AS lang, language", Lang::$table, $where, array('order'=>'language ASC'));
    	
    	$buffer .= \Gino\Input::select_label('lang', null, $query, _("Lingua"), array('required'=>true));
    	
    	$submit = \Gino\Input::submit('submit_action', _("crea"));
    	$buffer .= \Gino\Input::placeholderRow(null, $submit);
    	
    	$buffer .= $gform->close();
    	
    	$view = new View(null, 'section');
    	$dict = array(
    			'title' => $title,
    			'subtitle' => $subtitle, 
    			'class' => null,
    			'content' => $buffer
    	);
    
    	return $view->render($dict);
    }
    
    /**
     * @brief Processa il form di creazione di un file
     * @param \Gino\Http\Request oggetto Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    private function actionCreateFile($request) {
    
    	$lang = cleanVar($request->POST, 'lang', 'string', '');
    	
    	$gform = Loader::load('Form', array());
    	$gform->saveSession('dataform');
    	$req_error = $gform->checkRequired();
    	
    	if($req_error > 0)
    		return Error::errorMessage(array('error'=>1), $this->_mdlLink);
    	
    	// Lingua di default
    	$default = $this->_session->lngDft;	// Lang::getMainLang()
    	
    	$path_to_file = $this->pathToBaseDir($default).$this->fileName();
    	$path_to_new_lang = $this->pathToBaseDir($lang);
    	$path_to_new_file = $path_to_new_lang.$this->fileName();
    	
    	if(is_file($path_to_file))
    	{
    		if(!is_dir($path_to_new_lang))
    			mkdir($path_to_new_lang);
    		
    		if(!is_file($path_to_new_file))
    			copy($path_to_file, $path_to_new_file);
    		else
    			return Error::errorMessage(array('error'=>sprintf(_("Il file %s è già presente"), $lang.OS.$this->fileName())), $this->_mdlLink);
    	}
    	else
    	{
    		return Error::errorMessage(array('error'=>_("Non è presente il file della lingua principale")), $this->_mdlLink);
    	}
    
    	return new \Gino\Http\Redirect($this->_mdlLink);
    }
}
