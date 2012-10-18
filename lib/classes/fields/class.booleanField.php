<?php
/**
 * @file class.booleanField.php
 * @brief Contiene la classe booleanField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo BOOLEAN
 * 
 * Tipologie di input associabili: radio
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class booleanField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_enum, $_default;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b enum (array): elenco degli elementi di scelta
	 *   - @b default (mixed): valore di default (input radio)
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'radio';
		$this->_value_type = 'int';
		
		$this->_enum = array_key_exists('enum', $options) ? $options['enum'] : array();
		$this->_default = array_key_exists('default', $options) ? $options['default'] : '';
	}
	
	public function __toString() {

		$value = count($this->_enum) ? $this->_enum[$this->_value] : $this->_value;
		return (string) $value;
	}
	
	public function getEnum() {
		
		return $this->_enum;
	}
	
	public function setEnum($value) {
		
		if($value) $this->_enum = $value;
	}
	
	public function getDefault() {
		
		return $this->_default;
	}
	
	public function setDefault($value) {
		
		if($value) $this->_default = $value;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		return parent::formElement($form, $options);
	}
}
?>
