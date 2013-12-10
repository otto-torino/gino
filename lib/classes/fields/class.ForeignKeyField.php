<?php
/**
 * @file class.foreignKeyField.php
 * @brief Contiene la classe foreignKeyField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

loader::import('class/fields', 'Field');

/**
 * @brief Campo di tipo chiave esterna (estensione)
 * 
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: select, radio
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ForeignKeyField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_foreign, $_foreign_where, $_foreign_order;
	protected $_enum;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b fkey_table (string): nome della tabella dei dati
	 *   - @b fkey_id (string): nome del campo della chiave nel SELECT (default: id)
	 *   - @b fkey_field (mixed): nome del campo o dei campi dei valori nel SELECT
	 *     - @a string, nome del campo
	 *     - @a array, nomi dei campi da concatenare, es. array('firstname', 'lastname')
	 *   - @b fkey_where (mixed): condizioni della query
	 *     - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
	 *     - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
	 *   - @b fkey_order (string): ordinamento dei valori (es. name ASC)
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'select';
		$this->_value_type = 'int';

    $this->_foreign = $options['foreign'];
		$this->_foreign_where = array_key_exists('foreign_where', $options) ? $options['foreign_where'] : null;
    $this->_foreign_order = array_key_exists('foreign_order', $options) ? $options['foreign_order'] : 'id';
    $this->_foreign_controller = array_key_exists('foreign_controller', $options) ? $options['foreign_controller'] : null;
	}
	
	public function __toString() {

    if($this->_foreign_controller) {
      $obj = new $this->_foreign($this->_model->{$this->_name}, $this->_foreign_controller);
    }
    else {
      $obj = new $this->_foreign($this->_model->{$this->_name});
    }

    return (string) $obj;

	}
	
	public function getEnum() {
		
		return $this->_enum;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {

    $db = db::instance();
    $foreign = new $this->_foreign(null);
    $rows = $db->select('id', $foreign->getTable(), $this->_foreign_where, array('order' => $this->_foreign_order));
    $enum = array();
    foreach($rows as $row) {
      $f = new $this->_foreign($row['id']);
      $enum[$f->id] = (string) $f;
    }
		
		$this->_enum = $enum;
		
		return parent::formElement($form, $options);
	}
}
?>
