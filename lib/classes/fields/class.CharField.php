<?php
/**
 * @file class.CharField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.CharField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campi di tipo stringa (CHAR, VARCHAR)
 *
 * Tipologie di input associabili: testo, campo nascosto, textarea
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class CharField extends Field {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b trnsl (boolean): campo con traduzioni
     * @return istanza di Gino.CharField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'text';
        $this->_value_type = 'string';
    }
    
    /**
     * @see Gino.Field::getValue()
     * @return null or string
     */
	public function getValue($value) {
    	
		if(is_null($value)) {
			return null;
		}
		elseif(is_string($value)) {
    		return $value;
    	}
    	else throw new \Exception(_("Valore non valido"));
    }
    
    /**
     * @see Gino.Field::setValue()
     * @return null or string
     */
    public function setValue($value) {
    
    	if(is_null($value)) {
    		return null;
    	}
    	else {
    		return (string) $value;
    	}
    }
}
