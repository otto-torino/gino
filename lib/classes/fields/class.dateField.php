<?php
/**
 * @file class.dateField.php
 * @brief Contiene la classe dateField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo DATE
 * 
 * Tipologie di input associabili: testo in formato data
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class dateField extends field {

	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'date';
		$this->_value_type = 'string';
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
	
	/**
	 * @see field::filterWhereClause()
	 * 
	 * @param string $value
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b operator (string): operatore di confronto della data
	 * @return string
	 */
	public function filterWhereClause($value, $options=array()) {

		$operator = gOpt('operator', $options, null);
		if(is_null($operator)) $operator = '=';
		
		return $this->_table.".".$this->_name." $operator '".$value."'";
	}
	
	/**
	 * Formatta un elemento input di tipo @a date per l'inserimento in database
	 * 
	 * @see field::clean()
	 */
	public function clean($options=null) {
		
		$value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
		$method = isset($options['method']) ? $options['method'] : $_POST;
		
		return dateToDbDate(cleanVar($method, $this->_name, $value_type, null), "/");
	}
}
?>
