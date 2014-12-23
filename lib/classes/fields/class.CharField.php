<?php
/**
 * @file class.CharField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.CharField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo CHAR (CHAR, VARCHAR)
 *
 * Tipologie di input associabili: testo, campo nascosto, textarea
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class CharField extends Field {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_trnsl;

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b trnsl (boolean): campo con traduzioni
     * @return istanza di Gino.CharField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'text';
        $this->_value_type = 'string';

        $this->_trnsl = isset($options['trnsl']) ? $options['trnsl'] : TRUE;
    }

    /**
     * @brief Getter della proprietà trnsl (indica se il campo necessita di traduzione o no)
     * @return proprietà trnsl
     */
    public function getTrnsl() {

        return $this->_trnsl;
    }

    /**
     * @brief Setter della proprietà trnsl
     * @param bool $value
     * @return void
     */
    public function setTrnsl($value) {

        if(is_bool($value)) $this->_trnsl = $value;
    }

    /**
     * @brief Definisce la condizione WHERE per il campo
     * @see Gino.Field::filterWhereClause()
     */
    public function filterWhereClause($value) {

        $value = str_replace("'", "''", $value);

        if(preg_match("#^\"([^\"]*)\"$#", $value, $matches))
            $condition = "='".$matches[1]."'";
        elseif(preg_match("#^\"([^\"]*)$#", $value, $matches))
            $condition = " LIKE '".$matches[1]."%'";
        else
            $condition = " LIKE '%".$value."%'";

        return $this->_table.".".$this->_name.$condition;
    }

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        if(!isset($options['trnsl'])) $options['trnsl'] = $this->_trnsl;
        if(!isset($options['field'])) $options['field'] = $this->_name;

        return parent::formElement($form, $options);
    }
}
