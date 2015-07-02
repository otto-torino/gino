<?php
/**
 * @file class.TextBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TextBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

use \Gino\Http\Request;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Campo di tipo TEXT
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TextBuild extends Build {

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
     * @see Gino.Build::canBeOrdered()
     * @return FALSE
     */
    public function canBeOrdered() {

        return FALSE;
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

        if(isset($options['is_filter']) and $options['is_filter']) {
            $options['widget'] = 'input';
        }

        return parent::formElement($form, $options);
    }

    /**
     * @see Gino.Build::clean()
     */
    public function clean($options=null) {

        $request = Request::instance();
        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;
        $escape = gOpt('escape', $options, TRUE);
        $widget = gOpt('widget', $options, null);

        if($widget == 'editor') {
            return cleanVarEditor($method, $this->_name, '');
        }
        else {
            return cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));
        }
    }
}
