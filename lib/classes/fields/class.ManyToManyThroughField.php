
<?php
/**
 * @file class.manyToManyThroughField.php
 * @brief Contiene la classe manyToManyThroughField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

loader::import('class/fields', 'Field');

/**
 * @brief Campo di tipo many to many con associazione attraverso un modello (estensione)
 * 
 * Tipologie di input associabili: multicheck
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyThroughField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_m2m, $_m2m_controller, $_controller;
  protected $_model_table_id;
	protected $_enum;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b controller (object): controller
	 *   - @b m2m (string): classe attraverso la quale si esprime la relazione molti a molti
	 *   - @b m2m_controller (object): oggetto controller da passare evenualmente al costruttore della classe m2m
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'multicheck';
		$this->_value_type = 'array';

    $this->_controller = $options['controller'];
		$this->_m2m = $options['m2m'];
		$this->_m2m_controller = array_key_exists('m2m_controller', $options) ? $options['m2m_controller'] : null;

    $this->_model_table_id = strtolower(get_class($this->_model)).'_id';
	}
	
  /**
   * Rappresentazione a stringa della relazione m2m
   */
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

  /**
   * Restituisce la classe del m2m
   * @return classe del modello m2m
   */
  public function getM2m() {
    return $this->_m2m;
  }

  /**
   * Restituisce il controller della classe m2m
   * @return controller della classe m2m
   */
  public function getController() {
    return $this->_controller;
  }

  /**
   * Restituisce la tabella dati della classe m2m
   * @return nome tabella
   */
  public function getTable() {
    if($this->_m2m_controller) {
     $obj = new $this->_m2m(null, $this->_m2m_controller);
    }
    else {
      $obj = new $this->_m2m(null);
    }

    return $obj->getTable();
  }

  /**
   * Restituisce il nome del campo che immagazzina l'id del modello che ha la relazione m2m
   * @return nome del campo
   */
  public function getModelTableId() {
    return $this->_model_table_id;
  }

	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {

    $model = $this->_m2m_controller ? new $this->_m2m(null, $this->_m2m_controller) : new $this->_m2m(null);

    $admin_table = Loader::load('AdminTable', array($this->_controller, array()));

    $buffer = "<div id=\"m2mthrough-fieldset_".$this->_name."\">";
    foreach($this->_model->{$this->_name} as $id) {
      $m2m = $this->_model->m2mtObject($this->_name, $id);
      $buffer .= "<fieldset>";
      $buffer .= "<legend><span data-clone-ctrl=\"minus\" class=\"link fa fa-minus-circle\"></span> ".ucfirst($model->getModelLabel())."</legend>";
      $buffer .= "<div>";
      $buffer .= $admin_table->modelForm($m2m, array('only_inputs' => true), array());
      $buffer .= "</div>";
      $buffer .= "</fieldset>";
    }
    $buffer .= "<fieldset>";
    $buffer .= "<legend><span data-clone-ctrl=\"plus\" class=\"link fa fa-plus-circle\"></span> ".ucfirst($model->getModelLabel())."</legend>";
    $buffer .= "<div class=\"hidden\" data-clone=\"1\">";
    $buffer .= $admin_table->modelForm($model, array('only_inputs' => true, 'inputs_prefix' => 'm2mtdie_'), array());
    $buffer .= "</div>";
    $buffer .= "</fieldset>";
    $buffer .= "</div>";

    $buffer .= "<script>";
    $buffer .= "gino.m2mthrough('m2mthrough-fieldset_".$this->_name."', '".$this->_name."')";
    $buffer .= "</script>";

    return $buffer;

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
   * @todo check for a possible implementation
	 * @param string $value
	 * @return null
	 */
	public function filterWhereClause($value) {

    return null;
	}
}
?>
