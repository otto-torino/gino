<?php
/**
 * @file class.Build.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Build
 * 
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\Request;

/**
 * @brief Gestisce i campi del modello
 *
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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

    	// from Gino.Field::getProperties()
    	$this->_name = $options['name'];
    	$this->_label = $options['label'];
    	$this->_default = $options['default'];
    	$this->_lenght = $options['lenght'];
    	$this->_auto_increment = $options['auto_increment'];
    	$this->_primary_key = $options['primary_key'];
    	$this->_unique_key = $options['unique_key'];
    	$this->_required = $options['required'];
    	$this->_widget = $options['widget'];
    	$this->_int_digits = $options['int_digits'];
    	$this->_decimal_digits = $options['decimal_digits'];
    	
    	// from Gino.Model::getProperties()
    	$this->_model = $options['model'];
    	$this->_field_object = $options['field_object'];
    	$this->_table = $options['table'];
    	
    	if(array_key_exists('value', $options)) {
    		$this->_value = $options['value'];
    	}
    	else {
    		$value = $this->_model->{$this->_name};
    		$this->_value = $value;
    		$options['value'] = $value;
    	}
    	
    	// other
    	$this->_view_input = true;
    	
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
     * @return mixed, valore del campo
     */
    public function __toString() {

        return (string) $this->_value;
    }
    
    /**
     * @brief Definisce se il campo ammette l'ordinamento degli elementi negli elenchi amministrativi
     * @return TRUE se puo' essere utilizzato per l'ordinamento, FALSE altrimenti
     */
    public function canBeOrdered() {
    
    	return TRUE;
    }
    
    /**
     * @brief Getter della proprietà name
     * @return string, nome del campo
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
     * @return mixed, valore del campo
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
     * @return string, nome della tabella
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
     * @brief Getter della proprietà view_input
     * @return boolean
     */
    public function getViewInput() {
    
    	return $this->_view_input;
    }
    
    /**
     * @brief Setter della proprietà view_input
     * @param boolean $value
     * @return void
     */
    public function setViewInput($value) {
    
    	$this->_view_input = (bool) $value;
    }
    
    /**
     * @brief Getter della proprietà required
     * @return boolean, TRUE se il campo è obbligatorio, FALSE altrimenti
     */
    public function getRequired() {
    
    	return $this->_required;
    }
    
    /**
     * @brief Setter della proprietà required
     * @param bool $value
     * @return void
     */
    public function setRequired($value) {
    
    	$this->_required = !!$value;
    }
    
    /**
     * @brief Getter della proprietà widget
     * @return string
     */
    public function getWidget() {
    
    	return $this->_widget;
    }
    
    /**
     * @brief Setter della proprietà widget
     * @param string|null $value
     * @return void
     */
    public function setWidget($value) {
    
    	if(is_string($value) or is_null($value)) $this->_widget = $value;
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
     * @param object $mform istanza di Gino.Form o Gino.ModelForm
     * @param array $options opzioni del campo del form
     *   - opzioni dei metodi della classe Form
     *   - opzioni che sovrascrivono le impostazioni del campo/modello
     *     - @b widget (string): tipo di input form; può assumere uno dei seguenti valori
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
     * @return string
     * 
     * Definisce le opzioni: trnsl_id, trnsl_table, form_id, value_input, value_retrieve.
     * 
     * Il valore da utilizzare nel parametro @a value di ogni input varia in base al tipo di Widget; lo schema è il seguente: \n
     *   - CheckboxWidget		$this->_value
     *   - ConstantWidget		$this->_value_input
     *   - DatetimeWidget		$this->_value_retrieve
     *   - DateWidget			$this->_value_retrieve
     *   - EditorWidget			$this->_value_retrieve
     *   - EmailWidget			$this->_value_retrieve
     *   - FileWidget			$this->_value_input
     *   - FloatWidget			$this->_value_retrieve
     *   - HiddenWidget			$this->_value_input
     *   - ImageWidget			$this->_value_input
     *   - MulticheckWidget		$this->_value
     *   - PasswordWidget		$this->_value_retrieve
     *   - RadioWidget			$this->_value_retrieve
     *   - SelectWidget			$this->_value
     *   - TextareaWidget		$this->_value_retrieve
     *   - TextWidget			$this->_value_retrieve
     *   - TimeWidget			$this->_value_retrieve
     *   - UnitWidget			-
     */
    public function formElement($mform, $options=array()) {
    
    	$widget = isset($options['widget']) ? $options['widget'] : $this->_widget;
    	
    	if($widget == null) {
    		return '';
    	}
    	else {
    
    		$wìdget_class = "\Gino\\".ucfirst($widget)."Widget";
    		$widget_obj = new $wìdget_class();
    		
    		if(!array_key_exists('required', $options)) {
    			$options['required'] = $this->_required;
    		}
    		$opt = array_merge($this->properties(), $options);
    		
    		// Translation options
    		$opt['trnsl_id'] = $this->_model->id;
    		$opt['trnsl_table'] = $this->_model->getTable();
    		
    		// Form options
    		$opt['form_id'] = $mform->getFormId();
    		
    		// Define value to be shown in the input form
    		if(!is_null($opt['default']) and $opt['value'] === null) {
    			$opt['value'] = $opt['default'];
    		}
    		
    		$input_value = $widget_obj->inputValue($opt['value'], $opt);
    		$opt['value_input'] = $input_value;
    		$opt['value_retrieve'] = $mform->retvar($opt['name'], $input_value);
    		
    		return $widget_obj->printInputForm($opt);
    	}
    }
    
    /**
     * @brief Stampa un elemento del form dei filtri di ricerca nell'area amministrativa
     * 
     * @param array $options array associativo di opzioni di formElement()
     * @return string
     */
    public function formFilter($options)
    {
    	$options['required'] = FALSE;
    	$options['is_filter'] = TRUE;
    	
    	$mform = new ModelForm($this->_model);
    	return $this->formElement($mform, $options);
    }
    
    /**
     * @brief Definisce la condizione WHERE per il campo
     *
     * @param mixed $value
     * @param array $options array associativo di opzioni specifiche dei tipi di campo
     * @return string, where clause
     */
    public function filterWhereClause($value, $options=array()) {
    
    	return $this->_table.'.'.$this->_name."='".$value."'";
    }
    
    /**
     * @brief Ripulisce un input usato come filtro nell'area amministrativa
     * 
     * @param mixed $request_value valore della variabile in una richiesta HTTP
     * @param $options array associativo di opzioni
     * @return input ripulito
     */
    public function cleanFilter($request_value, $options) {
    	
    	$options['asforminput'] = TRUE;
    	
    	return $this->clean($request_value, $options);
    }
    
    /**
     * @brief Ripulisce un input per l'inserimento del valore in database
     * 
     * @param mixed $request_value valore della variabile in una richiesta HTTP (@see Gino.ModelForm::save()))
     * @param array $options array associativo di opzioni
     *   - opzioni delle funzioni di tipo clean
     *   - @b model_id (integer): valore id del modello	(@see Gino.ModelForm::save())
     * @return mixed, valore ripulito dell'input
     * 
     * Tabella del clean associato al tipo di campo: \n
     *   CLASSE				OPT_BUILD			FUNC
     *   BooleanBuild		-					clean_bool
     *   CharBuild			typeoftext			clean_text (default) | clean_html
     *   DateBuild			-					clean_date (->clean_text)
     *   DatetimeBuild		-					clean_date (->clean_text)
     *   DirectoryBuild		-					clean_text
     *   EmailBuild			-					clean_email (->clean_text)
     *   EnumBuild			-					clean_text | clean_int
     *   FileBuild			-					-- personalizzato
     *   FloatBuild			-					clean_float
     *   ForeignKeyBuild	-					clean_int
     *   ImageBuild			-					-- extend FileBuild
     *   IntegerBuild		-					clean_int
     *   ManyToManyBuild	-					clean_array
     *   ManyToManyThroughBuild					-- passa attraverso Gino.ModelForm::m2mThroughAction()
     *   MulticheckBuild	-					clean_array (asforminput false)
     *   SlugBuild			-					clean_text
     *   TagBuild			-					clean_text
     *   TextBuild			widget,typeoftext	clean_text (default) | clean_html
     *   TimeBuild			-					clean_time
     *   YearBuild			-					clean_int
     */
    public function clean($request_value, $options=array()) {
    	
    	return $request_value;
    }
    
    /**
     * @brief Valore del campo predisposto per l'output html
     * 
     * @return mixed
     */
    public function printValue() {
    
    	return $this->_value;
    }
}
