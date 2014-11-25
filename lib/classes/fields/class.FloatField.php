<?php
/**
 * @file class.FloatField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FloatField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo decimale (FLOAT, DOUBLE, DECIMAL)
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FloatField extends field {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_int_digits, $_decimal_digits;

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b int_digits (integer) numero totale delle cifre
     *   - @b decimal_digits (integer) numero delle cifre decimali
     * @return istanza di Gino.FloatField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'float';
        $this->_value_type = 'float';

        $this->_int_digits = array_key_exists('int_digits', $options) ? $options['int_digits'] : 0;
        $this->_decimal_digits = array_key_exists('decimal_digits', $options) ? $options['decimal_digits'] : 0;
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore campo
     */
    public function __toString() {

        return (string) dbNumberToNumber($this->_value, $this->_decimal_digits);
    }

    /**
     * @brief Getter della proprietà int_digits (numero di cifre intere)
     * @return proprietà int_digits
     */
    public function getIntDigits() {

        return $this->_int_digits;
    }

    /**
     * @brief Setter della proprietà int_digits
     * @param int $value
     * @return void
     */
    public function setIntDigits($value) {

        if(is_int($value)) $this->_int_digits = $value;
    }

    /**
     * @brief Getter della proprietà decimal_digits (numero di cifre decimali)
     * @return proprietà decimal_digits
     */
    public function getDecimalDigits() {

        return $this->_decimal_digits;
    }

    /**
     * @brief Setter della proprietà decimal_digits
     * @param int $value
     * @return void
     */
    public function setDecimalDigits($value) {

        if(is_int($value)) $this->_decimal_digits = $value;
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
     * @brief Ripulisce un input per l'inserimento in database
     *
     * Formatta un elemento input di tipo @a float per l'inserimento in database
     * @see Gino.Field::clean()
     */
    public function clean($options=null) {

        $request = Request::instance();
        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;

        return cleanVar($method, $this->_name, $value_type, null);
    }
}
