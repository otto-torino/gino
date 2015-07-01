<?php
/**
 * @file class.CharBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.CharBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo stringa (CHAR, VARCHAR)
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class CharBuild extends Build {

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
     * @brief Getter della proprietà trnsl (indica se il campo necessita di traduzione o no)
     * @return proprietà trnsl
     */
    public function getTrnsl() {

        return $this->_trnsl;
    }

    /**
     * @brief Setter della proprietà trnsl
     * @param bool $value
     * @return void
     */
    public function setTrnsl($value) {

        if(is_bool($value)) $this->_trnsl = $value;
    }

    /**
     * @see Gino.Build::filterWhereClause()
     */
    public function filterWhereClause($value) {

        $value = str_replace("'", "''", $value);

        if(preg_match("#^\"([^\"]*)\"$#", $value, $matches)) {
            $condition = "='".$matches[1]."'";
        }
        elseif(preg_match("#^\"([^\"]*)$#", $value, $matches)) {
            $condition = " LIKE '".$matches[1]."%'";
        }
        else {
            $condition = " LIKE '%".$value."%'";
        }

        return $this->_table.".".$this->_name.$condition;
    }

    /**
     * @see Gino.Build::formElement()
     */
    public function formElement(\Gino\Form $form, $options) {

        if(!isset($options['trnsl'])) $options['trnsl'] = $this->_trnsl;
        if(!isset($options['field'])) $options['field'] = $this->_name;

        return parent::formElement($form, $options);
    }
}
