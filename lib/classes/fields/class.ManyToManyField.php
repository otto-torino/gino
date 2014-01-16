<?php
/**
 * @file class.manyToManyField.php
 * @brief Contiene la classe manyToManyField
 *
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

loader::import('class/fields', 'Field');

/**
 * @brief Campo di tipo many to many (estensione)
 * 
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: multicheck
 *
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_m2m, $_m2m_order, $_m2m_where;
	protected $_join_table, $_join_table_id, $_join_table_m2m_id;
	protected $_enum;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b m2m (string): nome della classe del many to many
	 *   - @b m2m_where (mixed): condizioni della query
	 *     - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
	 *     - @a array, es. array("cond1='$cond1'", "cond2='$cond2'") 
	 *   - @b m2m_order (string): ordinamento dei valori (es. name ASC)
	 *   - @b m2m_controller (object): oggetto del controller della classe del many to many
	 *   - @b join_table (string): nome della tabella di join
	 * @return void
	 * 
	 * Convenzioni: \n
	 *   - il nome del campo id del modello nella tabella è: [nome_classe_del_modello]_id
	 *   - il nome del campo id del many to many nella tabella di join è: [nome_classe_del_m2m]_id
	 * 
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'multicheck';
		$this->_value_type = 'array';
		
		$this->_m2m = $options['m2m'];
		$this->_m2m_where = array_key_exists('m2m_where', $options) ? $options['m2m_where'] : null;
		$this->_m2m_order = array_key_exists('m2m_order', $options) ? $options['m2m_order'] : 'id';
		$this->_m2m_controller = array_key_exists('m2m_controller', $options) ? $options['m2m_controller'] : null;
		$this->_join_table = $options['join_table'];

		$this->_join_table_id = strtolower(get_class($this->_model)).'_id';
		$this->_join_table_m2m_id = strtolower($this->_m2m).'_id';
	}
	
	public function __toString() {

    $res = array();
    foreach($this->_model->{$this->_name} as $id) {
      if($this->_m2m_controller) {
        $obj = new $this->_m2m($id, $this->_m2m_controller);
      }
      else {
        $obj = new $this->_m2m($id);
      }
      $res[] = (string) $obj;
    }
    return implode(', ', $res);
  }
	
  public function getJoinTable() {
    return $this->_join_table;
  }

  public function getJoinTableId() {
    return $this->_join_table_id;
  }

  public function getJoinTableM2mId() {
    return $this->_join_table_m2m_id;
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
    $m2m = new $this->_m2m(null);
    $rows = $db->select('id', $m2m->getTable(), $this->_m2m_where, array('order' => $this->_m2m_order));
    $enum = array();
    foreach($rows as $row) {
      $m2m = new $this->_m2m($row['id']);
      $enum[$m2m->id] = htmlChars((string) $m2m);
    }
		
    $this->_value = $this->_model->{$this->_name};
		$this->_enum = $enum;
		$this->_name .= "[]";

		return parent::formElement($form, $options);
	}

	/**
	 * Formatta un elemento input per l'inserimento in database
	 * 
	 * @see cleanVar()
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b value_type (string): tipo di valore
	 *   - @b method (array): metodo di recupero degli elementi del form
	 *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
	 *   - @b asforminput (boolean)
	 * @return mixed
	 */
	public function clean($options=null) {
		
		$value_type = $this->_value_type;
		$method = isset($options['method']) ? $options['method'] : $_POST;
		$escape = gOpt('escape', $options, true);
		
		$value = cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));

		return $value;
	}

	/**
	 * Definisce la condizione WHERE per il campo
	 * 
	 * @param string $value
	 * @return string
	 */
	public function filterWhereClause($value) {

		$parts = array();
		foreach($value as $v) {
			$parts[] = $this->_table.".".$this->_name." REGEXP '[[:<:]]".$v."[[:>:]]'";
		}

		return "(".implode(' OR ', $parts).")";
	}
}
?>
