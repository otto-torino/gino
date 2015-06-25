<?php
/**
 * @file class.BooleanField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.BooleanField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Rappresenta campi di tipo BOOLEAN
 *
 * Tipologie di input associabili: radio
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class BooleanField extends Field {

    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     * @return istanza di Gino.BooleanField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'radio';
        $this->_value_type = 'int';
    }

    /**
     * @see Gino.Field::getValue()
     * @return null or boolean
     */
    public function getValue($value) {
    	 
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_int($value) or is_string($value)) {
    		return (bool) $value;
    	}
    	elseif(is_bool($value)) {
    		return $value;
    	}
    	else throw new \Exception(_("Valore non valido"));
    }
    
    /**
     * @see Gino.Field::setValue()
     * @return null or integer[1|0]
     */
    public function setValue($value) {
    
    	if(is_null($value)) {
    		return null;
    	}
    	elseif($value) {
    		return 1;
    	}
    	else {
    		return 0;
    	}
    }
}
