<?php
/**
 * @file class.Locale.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Locale
 * 
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use Gino\App\Sysconf\Conf;
use Gino\App\Sysconf\sysconf;
use \Gino\App\Language\Lang;

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
 * 
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Locale extends Singleton {

    private $_session;
    private $_strings;

    /**
     * Costruttore
     * 
     * @param string $class_name nome della classe
     * @return void
     */
    protected function __construct($class_name) {

        $this->_session = Session::instance();
        $this->_strings = array();

        $path_to_file = $this->pathToFile($class_name);

        if(file_exists($path_to_file))
        {
            $this->_strings = include($path_to_file);
        }
    }

    /**
     * @brief Percorso file traduzioni della classe o quello di default se non presente
     * @param string $class_name nome classe
     * @return percorso
     */
    private function pathToFile($class_name) {

        $filename = $class_name.'_lang.php';

        if(!file_exists(APP_DIR.OS.$class_name))
        {
            $path_to_file = SITE_ROOT.OS.'languages'.OS.$this->_session->lng.OS.$filename;
        }
        else
        {
            $path_to_file = APP_DIR.OS.$class_name.OS.'language'.OS.$this->_session->lng.OS.$filename;
        }

        return $path_to_file;
    }

    /**
     * @brief Recupera la traduzione corrispondente alla chiave data
     * @param string $key chiave
     * @return traduzione o la chiave stessa se la traduzione non è presente
     */
    public function get($key) {
        return array_key_exists($key, $this->_strings) ? $this->_strings[$key] : $key;
    }
    /**
     * @brief Setta la lingua del client
     * @return TRUE
     */
    public static function init() {

        $registry = Registry::instance();
        $session = Session::instance();

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

            if(isset($_GET["lng"]))
            {
                $session->lng = $_GET["lng"];
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
     * @return lingua user agent o FALSE se non trovata
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

    */

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
}
