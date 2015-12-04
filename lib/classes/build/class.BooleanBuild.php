<?php
/**
 * @file class.BooleanBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.BooleanBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo BOOLEAN
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class BooleanBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_choice;

    /**
     * Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);
        
        $this->_choice = $options['choice'];
    }

    /**
     * @brief Getter della proprietà enum
     * @return proprietà enum
     */
    public function getChoice() {

        return $this->_choice;
    }

    /**
     * @brief Setter della proprietà enum
     * @param array $value
     * @return void
     */
    public function setChoice($value) {

        if($value) $this->_choice = $value;
    }
    
    /**
     * @see Gino.Build::retrieveValue()
     */
    public function retrieveValue() {
    
    	if(is_null($this->_value)) {
    		return null;
   		}
    	elseif(is_bool($this->_value)) {
    		$value = (int) $this->_value;
    		return $this->_choice[$value];
    	}
    	else {
    		return null;
    	}
    }
    
    /**
     * @see Gino.Build::clean()
     * @return boolean
     */
    public function clean($options=null) {
    	
    	parent::clean($options);
    	return clean_bool($this->_request_value);
    }
}
