<?php
/**
 * @file class.ManyToManyThroughField.php
 * @brief Contiene la definizione ed implementation della classe Gino.ManyToManyThroughField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo many to many con associazione attraverso un modello che porta informazioni aggiuntive
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyThroughField extends Field {

    /**
     * @brief Costruttore
     *
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     * @return void
     */
    function __construct($options) {

        parent::__construct($options);
        
        $this->_default_widget = 'multicheck';
        $this->_value_type = 'array';
    }
}
