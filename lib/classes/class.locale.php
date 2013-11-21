<?php
/**
 * @file class.locale.php
 * @brief Contiene la classe locale
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria per la gestione delle traduzioni che non utilizzano le librerie gettext
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * In gino sono previsti due meccanismi per gestire le traduzioni: \n
 * - utilizzo delle librerie gettext
 * - file di stringhe localizzate
 * 
 * La classe locale si prende carico della gestione dei file di stringhe.
 * 
 * La classe locale viene inclusa nel file main.php e viene istanziata come singleton: \n
 * - nella classe AbstractEvtClass (main.php) per le classi applicative
 * - nelle classi modello delle classi applicative che estendono la classe propertyObject
 * - nelle classi non applicative
 * 
 * La classe AbstractEvtClass viene estesa dalle classi applicative che risiedono nella directory app e che in questo modo ereditano l'istanza @a locale.
 * Le classi modello delle classi applicative si trovano anche loro nella directory app, e ognuna di loro instanzia la classe locale:
 * @code
 * $this->_locale = locale::instance_to_class($this->_controller->getClassName());
 * @endcode
 * 
 * I file delle traduzioni richiamati utilizzando questa procedura dovranno risiedere nelle directory:
 * @code
 * app/[nome_classe]/language/[codice_lingua]/
 * @endcode
 * mentre il nome del file dovrà essere sempre nella forma: @a nomeclasse_lang.php \n
 * ad esempio avremo:
 * @code
 * app/user/language/en_US/user_lang.php
 * @endcode
 * 
 * La classe locale può venire anche istanziata nelle classi non applicative, in questo modo:
 * @code
 * $locale = locale::instance_to_class(get_class());
 * @endcode
 * 
 * I file delle traduzioni in questo caso dovranno risiedere nelle directory:
 * @code
 * languages/[codice_lingua]/
 * @endcode
 * 
 * #### Richiamare le stringhe
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
 */
class locale extends singleton {

	private $session;
	private $_strings;

	/**
	 * Costruttore
	 * 
	 * @param string $class nome della classe
	 * @return void
	 */
	protected function __construct($class) {
		
		$this->session = session::instance();
		
		$path_to_file = $this->pathToFile($class);
		
		if(file_exists($path_to_file))
		{
			$this->_strings = include($path_to_file);
		}
	}
	
	private function pathToFile($class) {
		
		$filename = $class.'_lang.php';
		
		if(!file_exists(APP_DIR.OS.$class))
		{
			$path_to_file = SITE_ROOT.OS.'languages'.OS.$this->session->lng.OS.$filename;
			
		}
		else
		{
			$path_to_file = APP_DIR.OS.$class.OS.'language'.OS.$this->session->lng.OS.$filename;
		}
		return $path_to_file;
	}
	
	/**
	 * Recupera il valore della stringa nella lingua di sessione
	 * 
	 * @return string
	 */
	public function get($key) {

		if(array_key_exists($key, $this->_strings))
		{
			return $this->_strings[$key];
		}
		else
		{
			return $key;
		}
	}
}
?>
