<?php
/**
 * @file class.Build.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Build
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\Request;

/**
 * @brief Gestisce i campi del modello
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Build {

    /**
     * @brief Proprietà dei campi
     */
    protected $_name,
    	$_label,
    	$_default,
    	$_lenght,
    	$_auto_increment,
    	$_primary_key,
    	$_unique_key,
    	$_required,
    	$_widget,
    	$_value_type,
    	$_int_digits,
    	$_decimal_digits;

    /**
     * @brief Istanza del modello cui il campo appartiene
     * @var Gino.Model
     */
    protected $_model;
    
    /**
     * Oggetto Field
     * @var object
     */
    protected $_field_object;
    
    /**
     * @brief Nome della tabella del modello
     * @var string
     */
    protected $_table;
    
    /**
     * Visualizzazione dell'input field nel form
     * @var boolean
     */
    protected $_view_input;
    
    /**
     * @brief Valore del campo
     * @var mixed
     */
    protected $_value;
    
    /**
     * Contiene tutte le opzioni di un campo del modello
     * @var array
     */
    private $_options;
    
    /**
     * Costruttore
     * 
     * @param array $options array associativo di opzioni del campo di una tabella
     *   opzioni delle colonne (caratteristiche del tipo di campo)
     *   opzioni del modello
     *     - @b model (object):
     *     - @b value (mixed):
     */
    function __construct($options) {

    	$this->_name = $options['name'];
    	$this->_label = $options['label'];
    	$this->_default = $options['default'];
    	$this->_lenght = $options['lenght'];
    	$this->_auto_increment = $options['auto_increment'];
    	$this->_primary_key = $options['primary_key'];
    	$this->_unique_key = $options['unique_key'];
    	$this->_required = $options['required'];
    	$this->_widget = $options['widget'];
    	$this->_value_type = $options['value_type'];
    	$this->_int_digits = $options['int_digits'];
    	$this->_decimal_digits = $options['decimal_digits'];
    	
    	$this->_model = $options['model'];
    	$this->_field_object = $options['field_object'];
    	$this->_table = $options['table'];
    	$this->_view_input = true;
		
    	if(array_key_exists('value', $options)) {
    		$this->_value = $options['value'];
    	}
    	else {
    		$value = $this->_model->{$this->_name};
    		$this->_value = $value;
    		$options['value'] = $value;
    	}
    	
    	$this->_options = $options;
    }
    
    /**
     * Elenco delle proprietà generali e specifiche di un campo
     * 
     * @return array
     */
    private function properties() {
    	
    	$properties = array();
    	
    	foreach($this->_options AS $key => $value)
    	{
    		$prop_name = '_'.$key;
    		$properties[$key] = $this->$prop_name;
    	}
    	
    	return $properties;
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    public function __toString() {

        return (string) $this->_value;
    }
    
    /**
     * @brief Indica se il campo può essere utilizzato come ordinamento nella lista della sezione amministrativa
     * @return TRUE se puo' essere utilizzato per l'ordinamento, FALSE altrimenti
     */
    public function canBeOrdered() {
    
    	return TRUE;
    }
    
    /**
     * @brief Getter della proprietà name
     * @return nome del campo
     */
    public function getName() {
    
    	return $this->_name;
    }
    
    /**
     * @brief Setter della proprietà name
     * @param mixed $value
     * @return void
     */
    public function setName($value) {
    
    	$this->_name = $value;
    }
    
    /**
     * @brief Getter della proprietà value
     * @return valore del campo
     */
    public function getValue() {

        return $this->_value;
    }

    /**
     * @brief Setter della proprietà value
     * @param mixed $value
     * @return void
     */
    public function setValue($value) {

        $this->_value = $value;
    }
    
    /**
     * @brief Getter della proprietà table
     * @return nome della tabella
     */
    public function getTable() {
    
    	return $this->_table;
    }
    
    /**
     * @brief Setter della proprietà table
     * @param string $value
     * @return void
     */
    public function setTable($value) {
    
    	$this->_table = $value;
    }
    
    /**
     * @brief Getter della proprietà value
     * @return valore del campo
     */
    public function getViewInput() {
    
    	return $this->_view_input;
    }
    
    /**
     * @brief Setter della proprietà value
     * @param boolean $value
     * @return void
     */
    public function setViewInput($value) {
    
    	$this->_view_input = (bool) $value;
    }
    
    /**
     * @brief Stampa un elemento del form facendo riferimento al valore della chiave @a widget
     *
     * Nella chiamata del form occorre definire la chiave @a widget nell'array degli elementi input. \n
     * Nel caso in cui la chiave @a widget non sia definita, verrà presa la chiave di default specificata nelle proprietà del modello. \n
     * Esempio
     * @code
     * array(
     *   'ctg'=>array('required'=>true),
     *   'field_text1'=>array(
     *     'widget'=>'editor',
     *     'notes'=>false,
     *     'img_preview'=>false,
     *     'fck_height'=>100),
     *   'field_text2'=>array('maxlength'=>$maxlength_summary, 'id'=>'summary', 'rows'=>6, 'cols'=>55)
     * )
     * @endcode
     *
     * @see Gino.Widget::printInputForm()
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni dell'elemento del form
     *   - opzioni dei metodi della classe Form
     *   - opzioni che sovrascrivono le impostazioni del campo/modello
     *     - @b widget (string): tipo di input form; può assumenre uno dei seguenti valori
     *       - @a hidden
     *       - @a constant
     *       - @a select
     *       - @a radio
     *       - @a checkbox
     *       - @a multicheck
     *       - @a editor
     *       - @a textarea
     *       - @a float
     *       - @a date
     *       - @a datetime
     *       - @a time
     *       - @a password
     *       - @a file
     *       - @a image
     *       - @a email
     *       - @a unit
     *     - @b required (boolean): campo obbligatorio
     * @return controllo del campo, html
     */
    public function formElement(\Gino\Form $form, $options) {
    
    	$widget = isset($options['widget']) ? $options['widget'] : $this->_widget;
    	
    	if($widget == null) {
    		return '';
    	}
    	else {
    		
    		if(!array_key_exists('required', $options)) {
    			$options['required'] = $this->_required;
    		}
    		$opt = array_merge($this->properties(), $options);
    		
    		$wìdget_class = "\Gino\\".ucfirst($widget)."Widget";
    		
    		$obj = new $wìdget_class();
    		return $obj->printInputForm($form, $opt);
    	}
    }
    
    /**
     * @brief Stampa un elemento del form di filtri area amministrativa
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options
     *   - @b default (mixed): valore di default
     * @return controllo del campo, html
     */
    public function formFilter(\Gino\Form $form, $options)
    {
    	$options['required'] = FALSE;
    	$options['is_filter'] = TRUE;
    	
    	return $this->formElement($form, $options);
    }
    
    /**
     * @brief Definisce la condizione WHERE per il campo
     *
     * @param mixed $value
     * @param array $options array associativo di opzioni specifiche dei tipi di campo
     * @return where clause
     */
    public function filterWhereClause($value, $options=array()) {
    
    	return $this->_table.'.'.$this->_name."='".$value."'";
    }
    
    /**
     * @brief Ripulisce un input usato come filtro in area amministrativa
     * @param $options
     *   array associativo di opzioni
     *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
     * @return input ripulito
     */
    public function cleanFilter($options) {
    	
    	$options['asforminput'] = TRUE;
    	return $this->clean($options);
    }
    
    /**
     * @brief Ripulisce un input per l'inserimento del valore in database
     * 
     * @see Gino.cleanVar()
     * @param array $options
     *   array associativo di opzioni
     *   - @b value_type (string): tipo di valore
     *   - @b method (array): metodo di recupero degli elementi del form
     *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
     * @param integer $id valore id del record
     * @return valore ripulito dell'input
     */
    public function clean($options=null, $id=null) {
    	
    	$request = Request::instance();
    	$value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
    	$method = isset($options['method']) ? $options['method'] : $request->POST;
    	$escape = gOpt('escape', $options, TRUE);
    	
    	return cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));
    }
    
    /**
     * @brief Valore del campo predisposto per l'output html
     *
     * @param mixed $value
     * @return mixed
     */
    public function retrieveValue() {
    
    	return $this->_value;
    }
}
