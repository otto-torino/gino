<?php
/**
 * @file class.BooleanFieldBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.BooleanFieldBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\FieldBuild');

/**
 * @brief Gestisce i campi di tipo BOOLEAN
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class BooleanFieldBuild extends FieldBuild {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_enum;

    /**
     * Costruttore
     *
     * @see Gino.FieldBuild::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe FieldBuild()
     *   - @b enum (array): elenco degli elementi di scelta
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_enum = array_key_exists('enum', $options) ? $options['enum'] : array();
    }

    /**
     * @brief Getter della proprietà enum
     * @return proprietà enum
     */
    public function getEnum() {

        return $this->_enum;
    }

    /**
     * @brief Setter della proprietà enum
     * @param array $value
     * @return void
     */
    public function setEnum($value) {

        if($value) $this->_enum = $value;
    }
    
    /**
     * @see Gino.FieldBuild::retrieveValue()
     */
    public function retrieveValue() {
    
    	if(is_null($this->_value)) {
    		return null;
   		}
    	elseif(is_bool($this->_value)) {
    		$value = (int) $this->_value;
    		return $this->_enum[$value];
    	}
    	else {
    		return null;
    	}
    }
}
