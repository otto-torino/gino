<?php
/**
 * @file class.field.php
 * @brief Contiene la classe field
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce la corretta rappresentazione dei campi nella struttura del form
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Field {

	/**
	 * Proprietà dei campi
	 * 
	 * Vengono esposte dai relativi metodi GET e SET
	 */
	protected $_name, $_label, $_value, $_default, $_lenght, $_auto_increment, $_primary_key, $_unique_key, $_table;
	protected $_model;
	
	/**
	 * Indica se il tipo di campo è obbligatorio 
	 * 
	 * @var boolean
	 */
	protected $_required;
	
	/**
	 * Tipo di input associato di default a un dato campo
	 * 
	 * @var string
	 */
	protected $_default_widget;
	
	/**
	 * Tipo di valore in arrivo dall'input
	 * 
	 * @var string
	 */
	protected $_value_type;

	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - @b name (string): nome del campo
	 *   - @b widget (string): widget
	 *   - @b default (mixed): valore di default del campo
	 *   - @b lenght (integer): lunghezza del campo
	 *   - @b auto_increment (boolean): campo auto_increment
	 *   - @b primary_key (boolean): campo chiave primaria
	 *   - @b unique_key (boolean): campo chiave unica
	 *   - @b required (boolean): valore indicatore del campo obbligatorio
	 * @return void
	 */
	function __construct($options) {

        $this->_default = null;

		$this->_model = $options['model'];
		$this->_name = array_key_exists('name', $options) ? $options['name'] : '';
		$this->_lenght = array_key_exists('lenght', $options) ? $options['lenght'] : 11;
		$this->_auto_increment = array_key_exists('auto_increment', $options) ? $options['auto_increment'] : false;
		$this->_primary_key = array_key_exists('primary_key', $options) ? $options['primary_key'] : false;
		$this->_unique_key = array_key_exists('unique_key', $options) ? $options['unique_key'] : false;
		$this->_required = array_key_exists('required', $options) ? $options['required'] : false;

		$this->_label = $this->_model->fieldLabel($this->_name);
		$this->_table = $this->_model->getTable();
		$this->_value =& $this->_model->{$this->_name};
		
		if(array_key_exists('widget', $options)) {
			$this->_default_widget = $options['widget'];
		}
	}
	
	public function __toString() {

		return (string) $this->_value;
	}

	/**
	 * Indica se il campo può essere utilizzato come ordinamento nella lista della sezione amministrativa
	 * 
	 * @return boolean
	 */
	public function canBeOrdered() {

		return true;
	}
	
	public function getName() {
		
		return $this->_name;
	}

  public function setName($name) {
		
		$this->_name = $name;
	}
	
	public function getLabel() {
		
		return $this->_label;
	}
	
	public function setLabel($value) {
		
		$this->_label = $value;
	}

    public function getDefault()
    {
        return $this->_default;
    }

    public function setDefault($value)
    {
        $this->_default = $value;
    }

	public function getValue() {
		
		return $this->_value;
	}
	
	public function setValue($value) {
		
		$this->_value = $value;
	}
	
	public function getLenght() {
		
		return $this->_lenght;
	}
	
	public function setLenght($value) {
		
		if(is_int($value)) $this->_lenght = $value;
	}
	
	public function getAutoIncrement() {
		
		return $this->_auto_increment;
	}
	
	public function setAutoIncrement($value) {
		
		if(is_int($value)) $this->_auto_increment = $value;
	}
	
	public function getPrimaryKey() {
		
		return $this->_primary_key;
	}
	
	public function setPrimaryKey($value) {
		
		if(is_bool($value)) $this->_primary_key = $value;
	}
	
	public function getUniqueKey() {
		
		return $this->_unique_key;
	}
	
	public function setUniqueKey($value) {
		
		if(is_bool($value)) $this->_unique_key = $value;
	}
	
	public function getRequired() {
		
		return $this->_required;
	}
	
	public function setRequired($value) {
		
		if(is_bool($value)) $this->_required = $value;
	}
	
	public function getWidget() {
		
		return $this->_default_widget;
	}
	
	public function setWidget($value) {
		
		if(is_string($value) || is_null($value)) $this->_default_widget = $value;
	}
	
	public function getValueType() {
		
		return $this->_value_type;
	}
	
	public function setValueType($value) {
		
		if(is_string($value)) $this->_value_type = $value;
	}
	
	/**
	 * Definisce la condizione WHERE per il campo
	 * 
	 * @param string $value
	 * @return string
	 */
	public function filterWhereClause($value) {

		return $this->_table.".".$this->_name." = '".$value."'";
	}
	
	/**
	 * Definisce l'ordinamento della query
	 * 
	 * @param string $order_dir
	 * @param array $query_where viene passato per reference
	 * @param array $query_table viene passato per reference
	 * @return string
	 */
	public function adminListOrder($order_dir, &$query_where, &$query_table) {

		return $this->_table.".".$this->_name." ".$order_dir;
	}
	
	/**
	 * Associazione tipo di widget / tipo di input
	 * 
	 * @param object $form
	 * @param array $options
	 *   array associativo comprendente le opzioni degli input form e l'opzione @b widget con i seguenti valori:
	 *   - @a hidden
	 *   - @a constant
	 *   - @a select
	 *   - @a radio
	 *   - @a checkbox
	 *   - @a multicheck
	 *   - @a editor
	 *   - @a textarea
	 *   - @a float
	 *   - @a date
	 *   - @a datetime
	 *   - @a time
	 *   - @a password
	 *   - @a file
	 *   - @a image
	 *   - @a email
	 * @return string
	 */
	private function formElementWidget($form, $options) {

		$inputForm = new inputForm($form);
		
		$buffer = '';

        if(!$this->_model->id and !is_null($this->_default)) {
            $this->_value = $this->_default;
        }

		if($options['widget'] == 'hidden')
		{
			$buffer .= $inputForm->hidden($this->_name, $this->_value, $options);
		}
		elseif($options['widget'] == 'constant')
		{
			$buffer .= $inputForm->hidden($this->_name, $this->_value, $options);
			$buffer .= $inputForm->noinput($this->_label, $this->_view_value, $options);
		}
		elseif($options['widget'] == 'select')
		{
			$enum = array_key_exists('enum', $options) ? $options['enum'] : $this->_enum;
			$buffer .= $inputForm->select($this->_name, $this->_value, $enum, $this->_label, $options);
		}
		elseif($options['widget'] == 'radio')
		{
			$enum = array_key_exists('enum', $options) ? $options['enum'] : $this->_enum;
			$default = array_key_exists('default', $options) ? $options['default'] : $this->_default;
			$buffer .= $inputForm->radio($this->_name, $this->_value, $enum, $default, $this->_label, $options);
		}
		elseif($options['widget'] == 'checkbox')
		{
			$checked = array_key_exists('checked', $options) ? $options['checked'] : false;
			$buffer .= $inputForm->checkbox($this->_name, $checked, $this->_value, $this->_label, $options);
		}
		elseif($options['widget'] == 'multicheck')
		{
			$enum = array_key_exists('enum', $options) ? $options['enum'] : $this->_enum;
			$buffer .= $inputForm->multicheck($this->_name, $this->_value, $enum, $this->_label, $options);
		}
		elseif($options['widget'] == 'editor')
		{
			$buffer .=  $inputForm->editor($this->_name, $this->_value, $this->_label, $options);
		}
		elseif($options['widget'] == 'textarea')
		{
			$buffer .=  $inputForm->textarea($this->_name, $this->_value, $this->_label, $options);
		}
		elseif($options['widget'] == 'float')
		{
			if(!array_key_exists('maxlength', $options))
				$options['maxlength'] = $this->_int_digits+1;
			
			$buffer .= $inputForm->text($this->_name, $this->_value, $this->_label, $options);
		}
		elseif($options['widget'] == 'date')
		{
			$buffer .= $inputForm->date($this->_name, $this->_value, $this->_label, $options);
		}
		elseif($options['widget'] == 'datetime')
		{
			$options['size'] = 20;
			$options['maxlength'] = 19;
			$buffer .= $inputForm->text($this->_name, $this->_value, $this->_label, $options);
		}
		elseif($options['widget'] == 'time')
		{
			$seconds = array_key_exists('seconds', $options) ? $options['seconds'] : false;
			if($seconds)
			{
				$size = 9;
				$maxlength = 8;
			}
			else
			{
				$size = 6;
				$maxlength = 5;
			}
			$value = dbTimeToTime($this->_value, $seconds);
			$options['size'] = $size;
			$options['maxlength'] = $maxlength;
			
			$buffer .= $inputForm->text($this->_name, $value, $this->_label, $options);
		}
		elseif($options['widget'] == 'password')
		{
			$buffer .= $inputForm->password($this->_name, $this->_value, $this->_label, $options);
		}
		elseif($options['widget'] == 'file' || $options['widget'] == 'image')
		{
			$buffer .= $inputForm->file($this->_name, $this->_value, $this->_label, $options);
		}
		elseif($options['widget'] == null)
		{
			$buffer .= '';
		}
		elseif($options['widget'] == 'email')
		{
			$buffer .= $inputForm->email($this->_name, $this->_value, $this->_label, $options);
		}
		else
		{
			$buffer .= $inputForm->text($this->_name, $this->_value, $this->_label, $options);
		}
		
		return $buffer;
	}
	
	/**
	 * Stampa un elemento del form facendo riferimento al valore della chiave @a widget
	 * 
	 * Nella chiamata del form occorre definire la chiave @a widget nell'array degli elementi input. \n
	 * Nel caso in cui la chiave @a widget non sia definita, verrà presa la chiave di default specificata nella classe del tipo di campo. \n
	 * Esempio
	 * @code
	 * array(
	 *   'ctg'=>array('required'=>true), 
	 *   'field_date'=>array('widget'=>'datetime'), 
	 *   'field_text1'=>array(
	 *     'widget'=>'editor', 
	 *     'notes'=>false, 
	 *     'img_preview'=>false, 
	 *     'fck_height'=>100), 
	 *   'field_text2'=>array('widget'=>'editor', 'trnsl'=>false), 
	 *   'field_text3'=>array('maxlength'=>$maxlength_summary, 'id'=>'summary', 'rows'=>6, 'cols'=>55)
	 * )
	 * @endcode
	 * 
	 * @see adminTable::modelForm()
	 * @see formElementWidget()
	 * @param object $form oggetto del form
	 * @param array $options opzioni dell'elemento del form
	 *   - opzioni dei metodi della classe Form
	 *   - @b widget (string): tipo di input
	 *   - @b required (boolean): campo obbligatorio
	 *   - @b value (mixed): valore dell'elemento
	 *   - @b enum (mixed): recupera gli elementi che popolano gli input radio, select, multicheck
	 *     - @a string, query per recuperare gli elementi (select di due campi)
	 *     - @a array, elenco degli elementi (key=>value)
	 *   - @b seconds (boolean): mostra i secondi
	 *   - @b default (mixed): valore di default (input radio)
	 *   - @b checked (boolean): valore selezionato (input checkbox)
	 * @return string
	 */
	public function formElement($form, $options) {
		
		$this->_inputForm = new InputForm($form);
		
		if(!array_key_exists('required', $options))
			$options['required'] = $this->_required;
		else
			$this->setRequired($options['required']);
		
		if(!isset($options['widget'])) $options['widget'] = $this->_default_widget;
		
		if(array_key_exists('value', $options)) $this->setValue($options['value']);
		
		return $this->formElementWidget($form, $options);
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
	 * @return mixed
	 */
	public function clean($options=null) {
		
		$value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
		$method = isset($options['method']) ? $options['method'] : $_POST;
		$escape = gOpt('escape', $options, true);
		
		return cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));
	}
	
	/**
	 * Valida il valore del campo
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value) {
		
		return true;
	}
}
?>
