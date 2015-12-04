<?php
/**
 * @file class.DateBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DateBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce i campi di tipo data
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DateBuild extends Build {

    /**
     * Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    function __toString() {

        return (string) $this->_value;
    }

    /**
     * @see Gino.Build::filterWhereClause()
     * 
     * @param array $options
     *   array associativo di opzioni
     *   - @b operator (string): operatore di confronto della data
     */
    public function filterWhereClause($value, $options=array()) {

        $operator = gOpt('operator', $options, null);
        if(is_null($operator)) $operator = '=';

        return $this->_table.".".$this->_name." $operator '".$value."'";
    }

    /**
     * @see Gino.Build::clean()
     * @param array $options array associativo di opzioni
     *   - opzioni della funzione Gino.clean_date()
     * @return string
     */
    public function clean($options=null) {

       parent::clean($options);
       return clean_date($this->_request_value, $options);
    }
}
