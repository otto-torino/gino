<?php
/**
 * @file class.TextField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TextField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

use \Gino\Http\Request;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo TEXT
 *
 * Tipologie di input associabili: textarea, testo, editor html
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TextField extends Field {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_trnsl;

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b trnsl (boolean): campo con traduzioni
     * @return istanza di Gino.TextField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'textarea';
        $this->_value_type = 'string';
        
        $this->_trnsl = isset($options['trnsl']) ? $options['trnsl'] : TRUE;
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    
    	$prop = parent::getProperties();
    
    	$prop['trnsl'] = $this->_trnsl;
    
    	return $prop;
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
