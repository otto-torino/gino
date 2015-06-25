<?php
/**
 * @file class.ForeignKeyField.php
 * @brief Contiene la definizione ed implementazione delal classe Gino.ForeignKeyField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo chiave esterna
 *
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: select, radio
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ForeignKeyField extends Field {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     * @return istanza di Gino.ForeignKeyField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'select';
        $this->_value_type = 'int';
    }

    /**
     * @see Gino.Field::setValue()
     * @return object
     */
    public function setValue($value) {
    	 
    	return (int) $value;
    }
}
