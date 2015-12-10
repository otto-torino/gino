<?php
/**
 * @file class.Widget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Widget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Definisce quale tipo di input associare a ciascun widget
 * 
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Widget {

	/**
	 * @brief Nome del campo
	 * @var string
	 */
    protected $_name;
    
    /**
     * @brief Label del campo
     * @var mixed
     */
    protected $_label;
    
    /**
     * @brief Valore del record
     * @var mixed
     */
    protected $_value;
    
    /**
     * @brief Valore di default
     * @var mixed
     */
    protected $_default;
    
    /**
     * @brief Valore da visualizzare nell'input form
     * @description Il valore viene passato al metodo inputValue() (@see Gino.Build::formElement()).
     * @var mixed
     */
	protected $_value_input;
	
	/**
	 * @brief Valore da visualizzare nell'input form
	 * @description Questo valore può corrispondere al valore recuperato dal salvataggio in sessione degli input form (@see Gino.Build::formElement()).
	 * @var mixed
	 */
	protected $_value_retrieve;
    
    /**
     * Costruttore
     */
    function __construct() {
    	
    }
    
    /**
     * @brief Definisce il formato del valore del campo da visualizzare nell'input form
     * 
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function inputValue($value, $options=array()) {
    	
    	return htmlInput($value);
    }
    
    /**
     * @brief Stampa l'input form associato al widget
     * @description In particolare imposta le proprietà principali del widget. È poi il singolo widget che stampa l'input form richiamando prima le proprietà.
     * 
     * @param array $options array associativo di opzioni del campo/modello
     * @return string
     */
    public function printInputForm($options) {
    
    	$this->_name = $options['name'];
    	$this->_label = $options['label'];
    	$this->_value = $options['value'];
    	$this->_default = $options['default'];
    	$this->_value_input = $options['value_input'];
    	$this->_value_retrieve = $options['value_retrieve'];
    }
}
