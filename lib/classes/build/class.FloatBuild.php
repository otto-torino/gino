<?php
/**
 * @file class.FloatBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FloatField
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce campi di tipo decimale (FLOAT, DOUBLE, DECIMAL)
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FloatBuild extends Build {

    /**
     * @brief Costruttore
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
     * @return valore campo
     */
    public function __toString() {

        return (string) dbNumberToNumber($this->_value, $this->_decimal_digits);
    }

    /**
     * @see Gino.Build::formElement()
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

        $request = \Gino\Http\Request::instance();
        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;

        return cleanVar($method, $this->_name, $value_type, null);
    }
}
