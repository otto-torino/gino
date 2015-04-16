<?php
/**
 * @file class.Field.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Field
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\Request;

/**
 * @brief Gestisce la corretta rappresentazione dei campi nella struttura del form
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Field {

    /**
     * @brief Proprietà dei campi
     * Vengono esposte dai relativi metodi __get e __set
     */
    protected $_name,
              $_label,
              $_value,
              $_default,
              $_lenght,
              $_auto_increment,
              $_primary_key,
              $_unique_key,
              $_table;

    /**
     * @brief Istanza del modello cui il campo appartiene
     * @var Gino.Model
     */
    protected $_model;

    /**
     * @brief Indica se il tipo di campo è obbligatorio 
     * @var boolean
     */
    protected $_required;

    /**
     * @brief Tipo di input associato di default a un dato campo
     * @var string
     */
    protected $_default_widget;

    /**
     * @brief Tipo di valore in arrivo dall'input
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
     * @return istanza di Gino.Field
     */
    function __construct($options) {

        $this->_default = null;

        $this->_model = $options['model'];
        $this->_name = array_key_exists('name', $options) ? $options['name'] : '';
        $this->_lenght = array_key_exists('lenght', $options) ? $options['lenght'] : 11;
        $this->_auto_increment = array_key_exists('auto_increment', $options) ? $options['auto_increment'] : FALSE;
        $this->_primary_key = array_key_exists('primary_key', $options) ? $options['primary_key'] : FALSE;
        $this->_unique_key = array_key_exists('unique_key', $options) ? $options['unique_key'] : FALSE;
        $this->_required = array_key_exists('required', $options) ? $options['required'] : FALSE;

        $this->_label = $this->_model->fieldLabel($this->_name);
        $this->_table = $this->_model->getTable();
        $this->_value =& $this->_model->{$this->_name};

        if(array_key_exists('widget', $options)) {
            $this->_default_widget = $options['widget'];
        }
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
     * @param string $name
     * @return void
     */
    public function setName($name) {

        $this->_name = (string) $name;
    }

    /**
     * @brief Getter della proprietà label
     * @return etichetta del campo
     */
    public function getLabel() {

        return $this->_label;
    }

    /**
     * @brief Setter della proprietà label
     * @param string $label
     * @return void
     */
    public function setLabel($label) {

        $this->_label = (string) $label;
    }

    /**
     * @brief Getter della proprietà default
     * @return valore di default del campo
     */
    public function getDefault()
    {
        return $this->_default;
    }

    /**
     * @brief Setter della proprietà default
     * @param mixed $default
     * @return void
     */
    public function setDefault($default)
    {
        $this->_default = $default;
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
     * @brief Getter della proprietà length
     * @return lunghezza del campo
     */
    public function getLenght() {

        return $this->_lenght;
    }

    /**
     * @brief Setter della proprietà length
     * @param int $length
     * @return void
     */
    public function setLenght($length) {

        if(is_int($length)) $this->_lenght = $length;
    }

    /**
     * @brief Getter della proprietà auto_increment
     * @return TRUE se il campo è autoincrement, FALSE altrimenti
     */
    public function getAutoIncrement() {

        return $this->_auto_increment;
    }

    /**
     * @brief Setter della proprietà auto_increment
     * @param bool $value
     * @return void
     */
    public function setAutoIncrement($value) {

        $this->_auto_increment = !!$value;
    }

    /**
     * @brief Getter della proprietà primary_key
     * @return TRUE se il campo è una chiave primaria, FALSE altrimenti
     */
    public function getPrimaryKey() {

        return $this->_primary_key;
    }

    /**
     * @brief Setter della proprietà primary_key
     * @param bool $value
     * @return void
     */
    public function setPrimaryKey($value) {

        $this->_primary_key = !!$value;
    }

    /**
     * @brief Getter della proprietà unique_key
     * @return TRUE se il campo ha chiave unica, FALSE altrimenti
     */
    public function getUniqueKey() {

        return $this->_unique_key;
    }

    /**
     * @brief Setter della proprietà unique_key
     * @param bool $value
     * @return void
     */
    public function setUniqueKey($value) {

        $this->_unique_key = !!$value;
    }

    /**
     * @brief Getter della proprietà required
     * @return TRUE se il campo è obbligatorio, FALSE altrimenti
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
     * @return widget
     */
    public function getWidget() {

        return $this->_default_widget;
    }

    /**
     * @brief Setter della proprietà widget
     * @param string|null $value
     * @return void
     */
    public function setWidget($value) {

        if(is_string($value) or is_null($value)) $this->_default_widget = $value;
    }

    /**
     * @brief Getter della proprietà value_type
     * @return tipo di dato
     */
    public function getValueType() {

        return $this->_value_type;
    }

    /**
     * @brief Setter della proprietà value_type
     * @param string $value tipo di dato
     * @return void
     */
    public function setValueType($value) {

        if(is_string($value)) $this->_value_type = $value;
    }

    /**
     * @brief Definisce la condizione WHERE per il campo
     *
     * @param mixed $value
     * @return where clause
     */
    public function filterWhereClause($value) {

        return $this->_table.".".$this->_name." = '".$value."'";
    }

    /**
     * @brief Definisce l'ordinamento della query
     * 
     * @param string $order_dir
     * @param array $query_where viene passato per reference
     * @param array $query_table viene passato per reference
     * @return order clause
     */
    public function adminListOrder($order_dir, &$query_where, &$query_table) {

        return $this->_table.".".$this->_name." ".$order_dir;
    }

    /**
     * @brief Associazione tipo di widget / tipo di input
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
     * @brief Stampa un elemento del form facendo riferimento al valore della chiave @a widget
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
     * @param \Gino\Form $form istanza di Gino.Form
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
     * @return controllo del campo, html
     */
    public function formElement(\Gino\Form $form, $options) {

        $this->_inputForm = new InputForm($form);

        if(!array_key_exists('required', $options)) {
            $options['required'] = $this->_required;
        }
        else {
            $this->setRequired($options['required']);
        }

        if(!isset($options['widget'])) $options['widget'] = $this->_default_widget;

        if(array_key_exists('value', $options)) $this->setValue($options['value']);

        return $this->formElementWidget($form, $options);
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
     * @brief Ripulisce un input usato come filtro in area amministrativa
     * @param $options
     *   array associativo di opzioni
     *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
     * @return input ripulito
     */
    public function cleanFilter($options)
    {
        $options['asforminput'] = TRUE;
        return $this->clean($options);
    }

    /**
     * @brief Ripulisce un input per l'inserimento in database
     *
     * @see Gino.cleanVar()
     * @param array $options
     *   array associativo di opzioni
     *   - @b value_type (string): tipo di valore
     *   - @b method (array): metodo di recupero degli elementi del form
     *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
     * @return input ripulito
     */
    public function clean($options=null) {

        $request = Request::instance();
        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;
        $escape = gOpt('escape', $options, TRUE);

        return cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));
    }

    /**
     * @brief Valida il valore del campo
     *
     * @description Il metodo deve essere sovrascritto dalle subclasses
     * @param mixed $value
     * @return TRUE
     */
    public function validate($value) {

        return TRUE;
    }
}
