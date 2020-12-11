<?php
/**
 * @file class.EnumBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EnumBuild
 *
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo enumerazione
 * 
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EnumBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_choice, $_value_type;

    /**
     * Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);
        
        $this->_choice = $options['choice'];
        $this->_value_type = $options['value_type'];
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    public function __toString() {

        $value = (count($this->_choice) && $this->_value != '' && $this->_value != null) ? $this->_choice[$this->_value] : $this->_value;
        return (string) $value;
    }

    /**
     * @brief Getter della proprietà choice
     * @return proprietà choice
     */
    public function getChoice() {

        return $this->_choice;
    }

    /**
     * @brief Setter della proprietà choice
     * @param array $value
     * @return void
     */
    public function setChoice($value) {

        if($value) $this->_choice = $value;
    }
    
    /**
     * @see Gino.Build::clean()
     * 
     * @param array $options array associativo di opzioni
     *   - opzioni delle funzioni Gino.clean_text(), Gino.clean_int()
     * @return string or integer
     */
    public function clean($request_value, $options=null) {
    	
    	if($this->_value_type == 'int') {
    		return clean_int($request_value);
    	}
    	elseif($this->_value_type == 'string') {
    		return clean_text($request_value, $options);
    	}
    	else {
    		return null;
    	}
    }
    
    /**
     * @see Gino.Build::printValue()
     * @return object
     */
    public function printValue() {
    	
    	if(is_null($this->_value)) {
    		return null;
    	}
    	elseif(is_int($this->_value) or is_string($this->_value)) {
    		if(array_key_exists($this->_value, $this->_choice)) {
    			return $this->_choice[$this->_value];
    		}
    		else {
    			return null;
    		}
    	}
    	else {
    		return null;
    	}
    }
}
