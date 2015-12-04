<?php
/**
 * @file class.YearBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.YearBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce i campi di tipo ANNO
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class YearBuild extends IntegerBuild {

    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build() e IntegerBuild()
     */
    function __construct($options) {

        parent::__construct($options);
    }
    
    /**
     * @see Gino.IntegerBuild::formElement()
     */
    public function formElement($mform, $options=array()) {
    
    	$options['maxlength'] = 4;
    
    	return parent::formElement($mform, $options);
    }
    
    /**
     * @see Gino.Build::clean()
     * @param array $options array associativo di opzioni
     * @return integer
     */
    public function clean($options=null) {
    	
    	parent::clean($options);
    	return clean_int($this->_request_value);
    }
}
