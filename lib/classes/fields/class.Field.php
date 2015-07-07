<?php
/**
 * @file class.Field.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Field
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\Request;

/**
 * @brief Gestisce le caratteristiche del tipo di campo (colonne)
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Field {

    /**
     * @brief Proprietà dei campi
     * Vengono esposte dai relativi metodi __get e __set
     */
	protected $_name, $_default, $_lenght, $_auto_increment, $_primary_key, $_unique_key, $_table, $int_digits, $decimal_digits;

    /**
     * @brief Indica se il tipo di campo è obbligatorio 
     * @var boolean
     */
    protected $_required;
    
    /**
     * @brief Tipo di widget associato al campo
     * @var string
     */
    protected $_widget;

    /**
     * @brief Tipo di input associato di default a un dato campo
     * @description viene impostato nelle singole classi Field
     * @var string
     */
    protected $_default_widget;

    /**
     * @brief Tipo di valore in arrivo dall'input
     * @description viene impostato nelle singole classi Field
     * @var string
     */
    protected $_value_type;

    /**
     * Costruttore
     * 
     * @param array $options array associativo di opzioni del campo del database
     *   - @b name (string): nome del campo
     *   - @b label (string): label del campo
     *   - @b default (mixed): valore di default del campo
     *   - @b max_lenght (integer): lunghezza massima del campo
     *   - @b auto_increment (boolean): campo auto_increment
     *   - @b primary_key (boolean): campo chiave primaria
     *   - @b unique_key (boolean): campo chiave unica
     *   - @b required (boolean): valore indicatore del campo obbligatorio
     *   - @b int_digits (integer): numero di cifre intere di un campo float
     *   - @b decimal_digits (integer): numero di cifre decimali di un campo float
     *   - @b table (string): nome della tabella del modello
     */
    function __construct($options) {

        $this->_default = null;
        
        $this->_name = array_key_exists('name', $options) ? $options['name'] : '';
        $this->_label = array_key_exists('label', $options) ? $options['label'] : $this->_name;
        $this->_lenght = array_key_exists('max_lenght', $options) ? $options['max_lenght'] : 11;
        $this->_auto_increment = array_key_exists('auto_increment', $options) ? $options['auto_increment'] : FALSE;
        $this->_primary_key = array_key_exists('primary_key', $options) ? $options['primary_key'] : FALSE;
        $this->_unique_key = array_key_exists('unique_key', $options) ? $options['unique_key'] : FALSE;
        $this->_required = array_key_exists('required', $options) ? $options['required'] : FALSE;
        $this->_widget = array_key_exists('widget', $options) ? $options['widget'] : $this->_default_widget;
        $this->_int_digits = array_key_exists('int_digits', $options) ? $options['int_digits'] : 0;
        $this->_decimal_digits = array_key_exists('decimal_digits', $options) ? $options['decimal_digits'] : 0;
        $this->_table = array_key_exists('table', $options) ? $options['table'] : '';
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    public function __toString() {

        return (string) $this->_name;
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
     * @brief Getter della proprietà int_digits (cifre intere)
     * @return integer
     */
    public function getIntDigits() {
    
    	return $this->_int_digits;
    }
    
    /**
     * @brief Setter della proprietà int_digits
     * @param int $value
     * @return void
     */
    public function setIntDigits($value) {
    
    	if(is_int($value)) $this->_int_digits = $value;
    }
    
    /**
     * @brief Getter della proprietà decimal_digits (cifre decimali)
     * @return integer decimal digits
     */
    public function getDecimalDigits() {
    
    	return $this->_decimal_digits;
    }
    
    /**
     * @brief Setter della proprietà decimal_digits
     * @param int $value
     * @return void
     */
    public function setDecimalDigits($value) {
    
    	if(is_int($value)) $this->_decimal_digits = $value;
    }

    /**
     * Proprietà defnite per il campo
     * 
     * @return array
     */
    public function getProperties() {
    	
    	return array(
    		'name' => $this->_name,
    		'label' => $this->_label,
    		'default' => $this->_default,
    		'lenght' => $this->_lenght,
    		'auto_increment' => $this->_auto_increment,
    		'primary_key' => $this->_primary_key,
    		'unique_key' => $this->_unique_key,
    		'table' => $this->_table,
    		'required' => $this->_required,
    		'widget' => $this->_widget,
    		'value_type' => $this->_value_type,
    		'int_digits' => $this->_int_digits,
    		'decimal_digits' => $this->_decimal_digits,
    	);
    }
    
    /**
     * @brief Valore del campo recuperato dal record della tabella
     * 
     * @param mixed $value
     * @return mixed
     */
    public function getValue($value) {
    	
    	return $value;
    }

    /**
     * @brief Imposta il valore da salvare nel campo della tabella
     *
     * @param mixed $value
     * @return mixed
     */
    public function setValue($value) {

        if(is_null($value))
        	return null;
        else
    		return (string) $value;
    }
}
