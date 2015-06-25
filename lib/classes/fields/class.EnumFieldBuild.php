<?php
/**
 * @file class.EnumFieldBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EnumFieldBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\FieldBuild');

/**
 * @brief Gestisce i campi di tipo ENUM
 * 
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EnumFieldBuild extends FieldBuild {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_enum;

    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b enum (array): elenco degli elementi di scelta
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_enum = array_key_exists('enum', $options) ? $options['enum'] : array();
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    public function __toString() {

        $value = (count($this->_enum) && $this->_value != '' && $this->_value != null) ? $this->_enum[$this->_value] : $this->_value;
        return (string) $value;
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
     * @param array $enum
     * @return void
     */
    public function setEnum($value) {

        if($value) $this->_enum = $value;
    }

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {
        return parent::formElement($form, $options);
    }
}
