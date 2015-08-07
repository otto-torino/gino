<?php
/**
 * @file class.EmailBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EmailBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EmailBuild extends Build {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_trnsl;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);
        
        $this->_trnsl = $options['trnsl'];
    }

    /**
     * @see Gino.Build::clean()
     */
    public function clean($options=array()) {

        $request = Request::instance();
        $method = isset($options['method']) ? $options['method'] : $request->POST;
        $value = cleanVar($method, $this->_name, 'string', null);
        
        if(is_null($value)) {
        	return null;
        }
        elseif(is_string($value)) {
        
        	/*$check = \Gino\checkEmail($value, true);
        	if(!$check) {
        		throw new \Exception(_("Formato dell'email non valido"));
        	}
        	return $value;*/
        	
        	$value = \filter_var($value, FILTER_VALIDATE_EMAIL);
        	
        	if($value === false) {
        		throw new \Exception(_("Formato dell'email non valido"));
        	}
        	else {
        		return $value;
        	}
        }
        else {
        	throw new \Exception(_("Valore non valido"));
        }
    }
}
