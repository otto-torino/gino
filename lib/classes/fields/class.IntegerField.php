<?php
/**
 * @file class.IntegerField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.IntegerField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo INTERO
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class IntegerField extends Field {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     * @return istanza di Gino.IntegerField
     */
    function __construct($options) {

        $this->_default_widget = 'text';
        parent::__construct($options);

        if($this->_auto_increment) $this->_widget = 'hidden';
        $this->_value_type = 'int';
    }
    
    /**
     * @see Gino.Field::getFormatValue()
     * @return null or integer
     */
    public function getFormatValue($value) {
    	 
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_int($value)) {
    		return $value;
    	}
    	elseif(is_string($value)) {
    		return (int) $value;
    	}
    	else throw new \Exception(_("Valore non valido"));
    }
    
    /**
     * @see Gino.Field::setFormatValue()
     * @return null or integer
     */
    public function setFormatValue($value) {
    
    	if(is_null($value)) {
    		return null;
    	}
    	else {
    		return (int) $value;
    	}
    }
}
