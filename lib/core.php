<?php

include_once(LIB_DIR.OS."singleton.php");
include_once(CLASSES_DIR.OS."class.registry.php");

include(LIB_DIR.OS."main.php");

$core = new core();

/*
 * headers, get text, languages and static classes include
 */
$main = new Main();

/*
 * ajax requests
 */
include(METHOD_POINTER);

/*
 * print document
 */
$core->renderApp();

class core {
	
	private $_registry, $_base_path;
	
	function __construct() {
		
		// initializing registry variable
		$this->_registry = registry::instance();
		$this->_registry->css = array();
		$this->_registry->js = array();
		$this->_registry->meta = array();
		$this->_registry->head_links = array();
	}

	public function renderApp() {
		
		ob_start();
		$doc = new document();
		$buffer = $doc->render();
		ob_end_flush();
	}
}
?>
