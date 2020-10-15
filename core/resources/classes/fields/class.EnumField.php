<?php
/**
 * @file class.EnumField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EnumField
 *
 * @copyright 2005-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo enumerazione
 * 
 * Tipologie di input associabili: radio, select
 *
 * @copyright 2005-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EnumField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_choice, $_value_type;
	
    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b choice (array): elenco degli elementi di scelta
     *     - @b value_type (string): tipo di valore del campo, accetta i valori
     *       - @a int, campo numerico (default)
     *       - @a string, campo alfanumerico
     */
    function __construct($options) {

        $this->_default_widget = 'radio';
        parent::__construct($options);
        
        $this->_choice = array_key_exists('choice', $options) ? $options['choice'] : array();
        $this->_value_type = array_key_exists('value_type', $options) ? $options['value_type'] : 'int';
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    	 
    	$prop = parent::getProperties();
    	 
    	$prop['choice'] = $this->_choice;
    	$prop['value_type'] = $this->_value_type;
    	 
    	return $prop;
    }

    /**
     * @see Gino.Field::valueFromDb()
     * @return null, string or integer
     */
    public function valueFromDb($value) {
    
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_array($value)) {
    		throw new \Exception(sprintf(_("Valore non valido del campo \"%s\""), $this->_name));
    	}
    	else {
    		return $value;
    	}
    }
}
