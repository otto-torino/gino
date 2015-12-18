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
 * 
 * ##Tabella delle associazioni predefinite del tipo di campo con il tipo input
 *
 * <table>
 * <tr><th>Classe</th><th>Tipo di campo (database)</th><th>Widget predefinito</th></tr>
 * <tr><td>BooleanField()</td><td>TINYINT</td><td>text</td></tr>
 * <tr><td>CharField()</td><td>CHAR, VARCHAR</td><td>text</td></tr>
 * <tr><td>DateField()</td><td>DATE</td><td>date</td></tr>
 * <tr><td>DatetimeField()</td><td>DATETIME</td><td>null</td></tr>
 * <tr><td>DirectoryField()</td><td>CHAR, VARCHAR</td><td>text</td></tr>
 * <tr><td>EnumField()</td><td>ENUM</td><td>radio</td></tr>
 * <tr><td>FileField()</td><td>CHAR, VARCHAR</td><td>file</td></tr>
 * <tr><td>FloatField()</td><td>FLOAT, DOUBLE, DECIMAL</td><td>float</td></tr>
 * <tr><td>foreignKeyField()</td><td>SMALLINT, INT, MEDIUMINT</td><td>select</td></tr>
 * <tr><td>ImageField()</td><td>CHAR, VARCHAR</td><td>image</td></tr>
 * <tr><td>IntegerField()</td><td>SMALLINT, INT, MEDIUMINT</td><td>text</td></tr>
 * <tr><td>ManyToManyField()</td><td>-</td><td>multicheck</td></tr>
 * <tr><td>ManyToManyThroughField()</td><td>-</td><td>unit</td></tr>
 * <tr><td>MulticheckField()</td><td>CHAR, VARCHAR</td><td>multicheck</td></tr>
 * <tr><td>SlugField()</td><td>CHAR, VARCHAR</td><td>text</td></tr>
 * <tr><td>TagField()</td><td>CHAR, VARCHAR</td><td>-</td></tr>
 * <tr><td>TextField()</td><td>TEXT</td><td>textarea</td></tr>
 * <tr><td>TimeField()</td><td>TIME</td><td>time</td></tr>
 * <tr><td>YearField()</td><td>YEAR</td><td>text</td></tr>
 * </table>
 */
class Field {

	/**
	 * @brief Oggetto request
	 * @var object
	 */
    protected $_request;
    
    /**
     * @brief Proprietà dei campi
     * Vengono esposte dai relativi metodi __get e __set
     */
	protected $_name, $_default, $_lenght, $_auto_increment, $_primary_key, $_unique_key, $int_digits, $decimal_digits;

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
     */
    function __construct($options) {

    	$this->_request = \Gino\Http\Request::instance();
    	
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
    		'required' => $this->_required,
    		'widget' => $this->_widget,
    		'int_digits' => $this->_int_digits,
    		'decimal_digits' => $this->_decimal_digits,
    	);
    }
    
    /**
     * @brief Valore del campo in una richiesta HTTP (@see Gino.ModelForm::save()))
     * 
     * @param string $field_name nome del campo
     * @return mixed
     */
    public function retrieveValue($field_name) {
    	
    	if(array_key_exists($field_name, $this->_request->{$this->_request->method})) {
    		$value = $this->_request->{$this->_request->method}[$field_name];
    	}
    	else {
    		$value = null;
    	}
    	return $value;
    }
    
    /**
     * @brief Valore del campo del modello nel suo formato specifico
     * @description Questo metodo viene richiamato nei metodi: @see Gino.Model::getProperties(), @see Gino.Model::fetchColumns().
     * 
     * @param mixed $value valore del campo
     * @return null or string
     */
    public function valueFromDb($value) {
    	
    	if(is_null($value)) {
			return null;
		}
		elseif(is_string($value)) {
    		return $value;
    	}
    	else {
    		throw new \Exception(sprintf(_("Valore non valido del campo \"%s\""), $this->_name));
    	}
    }

    /**
     * @brief Imposta il valore recuperato dal form e ripulito con Gino.Build::clean()
     * @description Il valore viene utilizzato per la definizione della query e la gestione dei ManyToMany.
     * @see Gino.Model::__set()
     * @see Gino.Model::save()
     * 
     * @param mixed $value valore da salvare
     * @return null or string
     */
	public function valueToDb($value) {

		if(is_null($value)) {
			return null;
		}
		else {
			return (string) $value;
		}
	}
}
