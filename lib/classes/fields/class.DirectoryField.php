<?php
/**
 * @file class.DirectoryField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DirectoryField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo DIRECTORY
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DirectoryField extends Field {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_path, $_prefix, $_default_name;

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
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
}
