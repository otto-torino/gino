<?php
/**
 * @file class.emailField.php
 * @brief Contiene la classe emailField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo EMAIL
 * 
 * Tipologie di input associabili: testo in formato email.
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class emailField extends field {

	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'text';
		$this->_value_type = 'string';
	}
	
	/**
	 * @see field::validate()
	 */
	public function validate($value) {
		
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}
}
?>
