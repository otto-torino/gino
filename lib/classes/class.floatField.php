<?php
/**
 * @file class.floatField.php
 * @brief Contiene la classe floatField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo decimale (FLOAT, DOUBLE, DECIMAL)
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class floatField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_int_digits, $_decimal_digits;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b int_digits (integer)
	 *   - @b decimal_digits (integer)
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'float';
		
		$this->_int_digits = array_key_exists('int_digits', $options) ? $options['int_digits'] : 0;
		$this->_decimal_digits = array_key_exists('decimal_digits', $options) ? $options['decimal_digits'] : 0;
	}
	
	public function getIntDigits() {
		
		return $this->_int_digits;
	}
	
	public function setIntDigits($value) {
		
		if(is_int($value)) $this->_int_digits = $value;
	}
	
	public function getDecimalDigits() {
		
		return $this->_decimal_digits;
	}
	
	public function setDecimalDigits($value) {
		
		if(is_int($value)) $this->_decimal_digits = $value;
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
