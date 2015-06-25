<?php
/**
 * @file class.SlugField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SlugField
 *
 * @copyright 2014-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo SLUG (CHAR, VARCHAR)
 *
 * I campi slug sono utilizzati per l'inserimento della parte caratterizzante di un pretty url,
 * in genere utilizzato al posto dell'id per identificare un oggetto
 *
 * @copyright 2014-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class SlugField extends Field {

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
