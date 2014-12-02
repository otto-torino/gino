<?php
/**
 * @file class.DateField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DateField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo data
 *
 * Tipologie di input associabili: testo in formato data
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DateField extends Field {

    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     * @return istanza di Gino.DateField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'date';
        $this->_value_type = 'string';
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    function __toString() {

        return (string) $this->_value;
    }

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        return parent::formElement($form, $options);
    }

    /**
     * @brief Definisce la condizione WHERE per il campo
     * @see Gino.Field::filterWhereClause()
     * @param string $value
     * @param array $options
     *   array associativo di opzioni
     *   - @b operator (string): operatore di confronto della data
     * @return where clause
     */
    public function filterWhereClause($value, $options=array()) {

        $operator = gOpt('operator', $options, null);
        if(is_null($operator)) $operator = '=';

        return $this->_table.".".$this->_name." $operator '".$value."'";
    }

    /**
     * @brief Ripulisce un input per l'inserimento in database
     * @see Gino.Field::clean()
     */
    public function clean($options=null) {

        $request = \Gino\Http\Request::instance();
        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;

        return \Gino\dateToDbDate(\Gino\cleanVar($method, $this->_name, $value_type, null), "/");
    }
}
