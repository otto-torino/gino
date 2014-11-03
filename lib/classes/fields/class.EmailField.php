<?php
/**
 * @file class.emailField.php
 * @brief Contiene la classe emailField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo EMAIL
 * 
 * Tipologie di input associabili: testo in formato email.
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class emailField extends field {

	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'email';
		$this->_value_type = 'string';
	}
	
	/**
	 * @see Field::clean()
	 */
	public function clean($options=array()) {
		
		$method = isset($options['method']) ? $options['method'] : $_POST;
		return filter_var(cleanVar($method, $this->_name, 'string', null), FILTER_VALIDATE_EMAIL);
	}

	/**
	 * @see Field::validate()
	 */
	public function validate($value) {
		
        if($value) {
            $result = checkEmail($value, true);
            if(!$result) {
                $result['error'] = _('formato dell\'email non valido');
            }
            return $result;
        }

        return true;
	}
}
?>
