<?php
/**
 * @file class.EnumBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EnumBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo ENUM
 * 
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EnumBuild extends Build {

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
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    public function __toString() {

        $value = (count($this->_choice) && $this->_value != '' && $this->_value != null) ? $this->_choice[$this->_value] : $this->_value;
        return (string) $value;
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
     * @param array $enum
     * @return void
     */
    public function setChoice($value) {

        if($value) $this->_choice = $value;
    }

    /**
     * @see Gino.Build::formElement()
     */
    public function formElement(\Gino\Form $form, $options) {
    	
        return parent::formElement($form, $options);
    }
}
