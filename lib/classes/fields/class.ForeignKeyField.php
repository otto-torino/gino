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
	protected $_fkey_table, $_fkey_id, $_fkey_field, $_fkey_where, $_fkey_order;
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
		
		$this->_fkey_table = array_key_exists('fkey_table', $options) ? $options['fkey_table'] : null;
		$this->_fkey_id = array_key_exists('fkey_id', $options) ? $options['fkey_id'] : 'id';
		$this->_fkey_field = array_key_exists('fkey_field', $options) ? $options['fkey_field'] : null;
		$this->_fkey_where = array_key_exists('fkey_where', $options) ? $options['fkey_where'] : '';
		$this->_fkey_order = array_key_exists('fkey_order', $options) ? $options['fkey_order'] : '';
	}
	
	public function __toString() {

		$db = db::instance();

		$field = $this->defineField($db);
		if(!$field) return null;
		
		$value = $db->getFieldFromId($this->_fkey_table, $field, $this->_fkey_id, $this->_value);

		return (string) $value;
	}
	
	public function getForeignKeyTable() {
		
		return $this->_fkey_table;
	}
	
	public function setForeignKeyTable($value) {
		
		$this->_fkey_table = $value;
	}
	
	public function getForeignKeyId() {
		
		return $this->_fkey_id;
	}
	
	public function setForeignKeyId($value) {
		
		$this->_fkey_id = $value;
	}
	
	public function getForeignKeyField() {
		
		return $this->_fkey_field;
	}
	
	public function setForeignKeyField($value) {
		
		$this->_fkey_field = $value;
	}
	
	public function getForeignKeyWhere() {
		
		return $this->_fkey_where;
	}
	
	public function setForeignKeyWhere($value) {
		
		$this->_fkey_where = $value;
	}
	
	public function getForeignKeyOrder() {
		
		return $this->_fkey_order;
	}
	
	public function setForeignKeyOrder($value) {
		
		$this->_fkey_order = $value;
	}
	
	public function getEnum() {
		
		return $this->_enum;
	}
	
	public function setEnum($value) {
		
		$this->_enum = $value;
	}
	
	/**
	 * Formatta il nome del campo da ricercare, tenendo conto di eventuali concatenamenti
	 * 
	 * @return string
	 */
	private function defineField($db=null) {
		
		if(is_array($this->_fkey_field) && count($this->_fkey_field))
		{
			if(sizeof($this->_fkey_field) > 1)
			{
				$array = array();
				foreach($this->_fkey_field AS $value)
				{
					$array[] = $value;
					$array[] = '\' \'';
				}
				array_pop($array);
				
				if(!$db) $db = db::instance();
				
				$fields = $db->concat($array);
			}
			else $fields = $this->_fkey_field[0];
		}
		elseif(is_string($this->_fkey_field) && $this->_fkey_field)
		{
			$fields = $this->_fkey_field;
		}
		else $fields = null;
		
		return $fields;
	}
	
	/**
	 * Imposta la query di selezione dei dati di una chiave esterna
	 * 
	 * @see defineField()
	 * @return string
	 */
	private function foreignKey() {
		
		if(is_null($this->_fkey_table) || is_null($this->_fkey_field))
			return null;
		
		$field = $this->defineField();
		if(!$field) return null;
		
		if(is_array($this->_fkey_where) && count($this->_fkey_where))
		{
			$where = implode(" AND ", $this->_fkey_where);
		}
		elseif(is_string($this->_fkey_where) && $this->_fkey_where)
		{
			$where = $this->_fkey_where;
		}
		else $where = '';
		
		if($where) $where = "WHERE $where";
		if($this->_fkey_order) $order = "ORDER BY ".$this->_fkey_order;
		
		$query = "SELECT {$this->_fkey_id}, $field FROM {$this->_fkey_table} $where $order";
		
		return $query;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		$this->_enum = $this->foreignKey();
		
		return parent::formElement($form, $options);
	}
}
?>
