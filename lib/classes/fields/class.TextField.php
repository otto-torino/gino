<?php
/**
 * @file class.TextField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TextField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo TEXT
 *
 * Tipologie di input associabili: textarea, testo, editor html
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TextField extends Field {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_trnsl;

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b trnsl (boolean): campo con traduzioni
     * @return istanza di Gino.TextField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'textarea';
        $this->_value_type = 'string';

        $this->_trnsl = isset($options['trnsl']) ? $options['trnsl'] : TRUE;
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
     * @brief Indica se il campo può essere utilizzato come ordinamento nella lista della sezione amministrativa
     * @see Gino.Field::canBeOrdered()
     * @return FALSE
     */
    public function canBeOrdered() {

        return FALSE;
    }

    /**
     * @brief Definisce la condizione WHERE per il campo
     * @see Gino.Field::filterWhereClause()
     */
    public function filterWhereClause($value) {

        $value = str_replace("'", "''", $value);

        if(preg_match("#^\"([^\"]*)\"$#", $value, $matches))
            $condition = "='".$matches[1]."'";
        elseif(preg_match("#^\"([^\"]*)$#", $value, $matches))
            $condition = " LIKE '".$matches[1]."%'";
        else
            $condition = " LIKE '%".$value."%'";

        return $this->_table.".".$this->_name.$condition;
    }

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
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
     * @brief Ripulisce un input per l'inserimento in database
     * @see Gino.Field::clean()
     * @return valore ripulito
     */
    public function clean($options=null) {

        $request = Request::instance();
        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;
        $escape = gOpt('escape', $options, TRUE);
        $widget = gOpt('widget', $options, null);

        if($widget == 'editor')
            return cleanVarEditor($method, $this->_name, '');
        else
            return cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));
    }
}
