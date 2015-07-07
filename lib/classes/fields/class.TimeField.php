<?php
/**
 * @file class.TimeField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TimeField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo TIME
 *
 * Tipologie di input associabili: testo in formato ora. \n
 * L'orario può essere mostrato con o senza i secondi utilizzando la chiave @a seconds.
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TimeField extends Field {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     * @return istanza di Gino.TimeField
     */
    function __construct($options) {

        $this->_default_widget = 'time';
        parent::__construct($options);
        
        $this->_value_type = 'string';
    }
}
