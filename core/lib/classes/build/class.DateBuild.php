<?php
/**
 * @file class.DateBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DateBuild
 */
namespace Gino;

/**
 * @brief Gestisce i campi di tipo data
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
     * @return string
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
     * 
     * @param array $options array associativo di opzioni
     *   - opzioni della funzione Gino.clean_date()
     * @return string
     */
    public function clean($request_value, $options=null) {

       return clean_date($request_value, $options);
    }
}
