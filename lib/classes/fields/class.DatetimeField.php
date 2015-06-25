<?php
/**
 * @file class.DatetimeField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DatetimeField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo DATETIME
 *
 * Tipologie di input associabili: nessun input, testo nascosto, testo in formato datetime (YYYY-MM-DD HH:MM:SS). \n
 * 
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DatetimeField extends Field {

    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'datetime';
        $this->_value_type = 'string';
    }

    /**
     * @see Gino.Field::getValue()
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
