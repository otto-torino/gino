<?php
/**
 * @file class.yearField.php
 * @brief Contiene la classe yearField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo ANNO
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class yearField extends integerField {

	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - opzioni generali definite come proprietà nella classe integerField()
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'text';
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		$options['maxlength'] = 4;
		
		return parent::formElement($form, $options);
	}
}
?>
