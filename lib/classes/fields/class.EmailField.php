<?php
/**
 * @file class.EmailField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EmailField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\Request;

/**
 * @brief Campo di tipo EMAIL
 *
 * Tipologie di input associabili: testo in formato email.
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EmailField extends Field {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     * @return istanza di Gino.EmailField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'email';
        $this->_value_type = 'string';
    }

    /**
     * @brief Ripulisce un input per l'inserimento in database
     * @see Gino.Field::clean()
     */
    public function clean($options=array()) {

        $request = Request::instance();
        $method = isset($options['method']) ? $options['method'] : $request->POST;
        return \filter_var(cleanVar($method, $this->_name, 'string', null), FILTER_VALIDATE_EMAIL);
    }

    /**
     * @brief Valida il valore del campo
     * @see Gino.Field::validate()
     * @param string $value
     * @return True o errore
     */
    public function validate($value) {

        if($value) {
            $result = checkEmail($value, true);
            if(!$result) {
                $result['error'] = _('formato dell\'email non valido');
            }
            return $result;
        }

        return TRUE;
    }
}
