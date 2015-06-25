<?php
/**
 * @file class.FileField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FileField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo FILE
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FileField extends Field {

    /**
     * @brief Costruttore
     * 
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'file';
        $this->_value_type = null;
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
}
