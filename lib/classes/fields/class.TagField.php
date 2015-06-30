<?php
/**
 * @file class.TagField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TagField
 *
 * @copyright 2014-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe per la gestione di campi per inserimento tag
 *
 * @copyright 2014-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TagField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_model_controller_class, $_model_controller_instance;
	
    /**
     * @brief Costruttore
     * 
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b model_controller_class: nome classe del controller
     *     - @b model_controller_instance: id istanza del controller
     */
    function __construct($options) {
        
    	parent::__construct($options);
    	
    	$this->_model_controller_class = $options['model_controller_class'];
    	$this->_model_controller_instance = $options['model_controller_instance'];
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    	 
    	$prop = parent::getProperties();
    	 
    	$prop['model_controller_class'] = $this->_model_controller_class;
    	$prop['model_controller_instance'] = $this->_model_controller_instance;
    	 
    	return $prop;
    }

    /**
     * @see Gino.Field::getValue()
     * @return null or string
     */
    public function getValue($value) {
    	 
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_string($value)) {
    		return $value;
    	}
    	else throw new \Exception(_("Valore non valido"));
    }
}
