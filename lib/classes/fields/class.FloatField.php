<?php
/**
 * @file class.FloatField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FloatField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo decimale (FLOAT, DOUBLE, DECIMAL)
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FloatField extends Field {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'float';
        $this->_value_type = 'float';
    }
}
