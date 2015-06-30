<?php
/**
 * @file class.TimeBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TimeBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce i campi di tipo TIME
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TimeBuild extends Build {

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
     * @return valore del campo
     */
    function __toString() {

        return (string) $this->_value;
    }

    /**
     * @brief Widget html per il form
     *
     * @see Gino.Field::formElement()
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni dell'elemento del form
     *   - opzioni dei metodi input() e cinput() della classe Form
     *   - @b seconds (boolean): mostra i secondi
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        return parent::formElement($form, $options);
    }

    /**
     * Formatta un elemento input di tipo @a time per l'inserimento in database
     * @see Gino.Field::clean()
     */
    public function clean($options=null) {

        $request = Request::instance();
        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;

        return \Gino\timeToDbTime(\Gino\cleanVar($method, $this->_name, $value_type, null));
    }
}
