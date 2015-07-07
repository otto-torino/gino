<?php
/**
 * @file class.EnumField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EnumField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo ENUM
 * 
 * Tipologie di input associabili: radio, select
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EnumField extends Field {

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
     */
    function __construct($options) {

        $this->_default_widget = 'radio';
        parent::__construct($options);
        
        $this->_value_type = 'string';
        $this->_choice = array_key_exists('choice', $options) ? $options['choice'] : array();
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
     * @see Gino.Field::getValue()
     */
    public function getValue($value) {
    
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_array($value)) {
    		throw new \Exception(_("Valore non valido"));
    	}
    	else {
    		return $value;
    	}
    }
}
