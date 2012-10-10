<?php
/**
 * @file class.constantField.php
 * @brief Contiene la classe constantField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo nascosto che mostra anche il valore corrispondente senza input
 * 
 * Tipologie di input associabili: campo nascosto
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class constantField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_view_value, $_const_table, $_const_id, $_const_field;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b const_table (string): nome della tabella dei dati
	 *   - @b const_id (string): nome del campo della chiave nel SELECT (default: id)
	 *   - @b const_field (mixed): nome del campo o dei campi dei valori nel SELECT
	 *     - @a string, nome del campo
	 *     - @a array, nomi dei campi da concatenare, es. array('firstname', 'lastname')
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'constant';
		$this->_value_type = 'int';
		
		$this->_const_table = array_key_exists('const_table', $options) ? $options['const_table'] : null;
		$this->_const_id = array_key_exists('const_id', $options) ? $options['const_id'] : 'id';
		$this->_const_field = array_key_exists('const_field', $options) ? $options['const_field'] : null;
	}
	
	public function __toString() {

		$db = db::instance();

		$value = $db->getFieldFromId($this->_const_table, $this->_const_field, $this->_const_id, $this->_value);

		return (string) $value;
	}
	
	public function getViewValue() {
		
		return $this->_view_value;
	}
	
	public function setViewValue($value) {
		
		$this->_view_value = $value;
	}
	
	public function getConstantTable() {
		
		return $this->_const_table;
	}
	
	public function setConstantTable($value) {
		
		$this->_const_table = $value;
	}
	
	public function getConstantId() {
		
		return $this->_const_id;
	}
	
	public function setConstantId($value) {
		
		$this->_const_id = $value;
	}
	
	public function getConstantField() {
		
		return $this->_const_field;
	}
	
	public function setConstantField($value) {
		
		$this->_const_field = $value;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		$this->_view_value = $this ? $this : $this->_value;
		
		return parent::formElement($form, $options);
	}
}
?>
