<?php
/**
 * @file core.php
 * @brief File che genera il documento
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * Include la classe registry
 */
include_once(CLASSES_DIR.OS."class.registry.php");

/**
 * Include le classi Main, EvtHandler, AbstractEvtClass
 */
include(LIB_DIR.OS."main.php");

$core = new core();

// headers, get text, languages and static classes include
$main = new Main();

/**
 * Include il file definito nella variabile METHOD_POINTER (richieste ajax)
 */
include(METHOD_POINTER);

// print document
$core->renderApp();

/**
 * @brief Renderizza la pagina richiesta
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class core {
	
	private $_registry, $_base_path;
	
	/**
	 * Inizializza le variabili di registro
	 */
	function __construct() {
		
		$this->_registry = registry::instance();
		$this->_registry->css = array();
		$this->_registry->js = array();
		$this->_registry->meta = array();
		$this->_registry->head_links = array();
	}

	/**
	 * Effettua il render della pagina e invia l'output buffering
	 */
	public function renderApp() {
		
		ob_start();
		$doc = new document();
		$buffer = $doc->render();
		ob_end_flush();
	}
}
?>
