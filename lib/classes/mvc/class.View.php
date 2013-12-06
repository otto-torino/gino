<?php
/**
 * @file class.view.php
 * Contiene la definizione ed implementazione della classe view.
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestisce le viste, impostando il template e ritornando l'output
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class view {
	
	/**
	 * oggetto che contiene il context della view 
	 */
	protected $_data;
	
	/**
	 * istanze del registry 
	 */
	protected $_registry;

	/**
	 * percorso alla cartella contenente le view specifiche del modulo
	 */
	protected $_view_folder;

	/**
	 * percorso alla cartella che contine le view di sistema
	 */
	protected $_dft_view_folder;

	/**
	 * path alla view correntemente in uso
	 */
	protected $_view_tpl;

	/**
	 * Costruttore
	 *
	 * @return istanza di view
	 */
	function __construct($view_folder=null, $tpl = null) {

		$this->_data = new stdClass();
		$this->_registry = registry::instance();
		$this->_view_folder = $view_folder;
		$this->_dft_view_folder = VIEWS_DIR;

    if($tpl) {
      $this->setViewTpl($tpl);
    }
	}

	/**
	 * @brief Setta il template della view
	 * 
	 * Cerca il template nella directory che contiene le view specifiche e se non lo trova prosegue la ricerca nella directory delle view di sistema 
	 * 
	 * @param string $view_name il nome della view
	 * @param array $opts 
	 *   an associative array of options
	 *   - @b css: the stylesheet to charge with the template
	 * @return void
	 */
	public function setViewTpl($view_name, $opts=null) {

		if(!is_null($this->_view_folder) && is_readable($this->_view_folder.OS.$view_name.".php")) {
			$this->_view_tpl = $this->_view_folder.OS.$view_name.".php";
		}
		elseif(is_readable($this->_dft_view_folder.OS.$view_name.".php")) {
			$this->_view_tpl = $this->_dft_view_folder.OS.$view_name.".php";
		}
		else exit(Error::syserrorMessage('view', 'setViewTpl', sprintf(_("Impossibile caricare la vista %s"), $view_name), __LINE__));
	}

	/**
	 * Assegna dati a nomi di variabile 
	 * 
	 * Prepara il context disponibile nella vista.
	 * 
	 * @param string $name il nome della variabile da utilizzare nella vista 
	 * @param mixed $value il valore della variabile
	 * @return void
	 */
	public function assign($name, $value) {
		$this->_data->$name = $value;
	}

	/**
	 * Ritorna l'output generato dalla vista 
	 * @param array data dizionario delle variabili da passare
	 * @return l'output della vista
	 */
	public function render($data = null) {

		$buffer = '';

    if($data) {
      foreach($data as $k => $v) $$k = $v;
    }
    else {
		  foreach($this->_data as $k=>$v) $$k=$v;
    }

		ob_start();
		include($this->_view_tpl);
		$buffer .= ob_get_contents();
		ob_clean();

		return $buffer;
	}
}

?>
