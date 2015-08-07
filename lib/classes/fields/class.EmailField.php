<?php
/**
 * @file class.EmailField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EmailField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\Request;

/**
 * @brief Campo di tipo EMAIL
 *
 * Tipologie di input associabili: testo in formato email.
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EmailField extends Field {

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
     * @return istanza di Gino.EmailField
     */
    function __construct($options) {

        $this->_default_widget = 'email';
        parent::__construct($options);

        $this->_value_type = 'string';
        $this->_trnsl = false;
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
     * @see Gino.Field::valueFromDb()
     * @return null or string
     */
    public function valueFromDb($value) {
    	
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_string($value)) {
    		return $value;
    	}
    	else throw new \Exception(_("Valore non valido"));
    }
}
