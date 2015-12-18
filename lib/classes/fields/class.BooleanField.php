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
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_choice;
	
    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b choice (array): elenco degli elementi di scelta
     * @return istanza di Gino.BooleanField
     */
    function __construct($options) {

        $this->_default_widget = 'radio';
        parent::__construct($options);
        
        $this->setLenght(1);
        $this->_choice = array_key_exists('choice', $options) ? $options['choice'] : array(1 => _('si'), 0 => _('no'));
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    	
    	$prop = parent::getProperties();
    	
    	$prop['choice'] = $this->_choice;
    	
    	return $prop;
    }

    /**
     * @see Gino.Field::valueFromDb()
     * @return null or boolean
     */
    public function valueFromDb($value) {
    	
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_int($value) or is_string($value)) {
    		return (bool) $value;
    	}
    	elseif(is_bool($value)) {
    		return $value;
    	}
    	else {
    		throw new \Exception(sprintf(_("Valore non valido del campo \"%s\""), $this->_name));
    	}
    }
    
    /**
     * @see Gino.Field::valueToDb()
     * @return null or integer[1|0]
     */
    public function valueToDb($value) {
    
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
