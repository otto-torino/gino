<?php
/**
 * @file class.fileField.php
 * @brief Contiene la classe fileField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo FILE (estensione)
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class fileField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_extension, $_path_abs, $_path_add;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b extensions (array): estensioni lecite di file
	 *   - @b path (string): percorso assoluto fino a prima del valore del record ID
	 *   - @b add_path (string): parte del percorso assoluto dal parametro @a path fino a prima del file
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'file';
		
		$this->_extensions = isset($options['extension']) ? $options['extension'] : array();
		$this->_path_abs = isset($options['path']) ? $options['path'] : '';
		$this->_path_add = isset($options['add_path']) ? $options['add_path'] : '';
	}
	
	public function getExtensions() {
		
		return $this->_extensions;
	}
	
	public function setExtensions($value) {
		
		$this->_extensions = $value;
	}
	
	public function getPath() {
		
		return $this->_path_abs;
	}
	
	public function setPath($value) {
		
		$this->_path_abs = $value;
	}
	
	public function getAddPath() {
		
		return $this->_path_add;
	}
	
	public function setAddPath($value) {
		
		$this->_path_add = $value;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		if(!isset($options['extensions'])) $options['extensions'] = $this->_extensions;
		
		return parent::formElement($form, $options);
	}
}
?>
