<?php
/**
 * @file class.ManyToManyInlineField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ManyToManyInlineField
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo many to many gestito senza tabella di join
 * 
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: multicheck
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyInlineField extends Field {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_m2m, $_m2m_order, $_m2m_where, $_m2m_controller;
    protected $_enum;

    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b m2m (object): classe del modello correlato (nome completo di namespace)
     *   - @b m2m_where (mixed): condizioni della query per filtrare i possibili modelli da associare
     *     - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
     *     - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
     *   - @b m2m_order (string): ordinamento dei valori (es. name ASC)
     *   - @b m2m_controller (\Gino\Controller): classe Controller del modello m2m, essenziale se il modello appartiene ad un modulo istanziabile
     * @return void
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'multicheck';
        $this->_value_type = 'array';

        $this->_m2m = $options['m2m'];
        $this->_m2m_where = array_key_exists('m2m_where', $options) ? $options['m2m_where'] : null;
        $this->_m2m_order = array_key_exists('m2m_order', $options) ? $options['m2m_order'] : 'id';
        $this->_m2m_controller = array_key_exists('m2m_controller', $options) ? $options['m2m_controller'] : null;
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return rappresentazione a stringa dei modelli associati separati da virgola
     */
    public function __toString() {

        $res = array();
        foreach(explode(', ', $this->_model->{$this->_name}) as $id) {
            if($this->_m2m_controller) {
                $obj = new $this->_m2m($id, $this->_m2m_controller);
            }
            else {
                $obj = new $this->_m2m($id);
            }
            $res[] = (string) $obj;
        }
        return implode(', ', $res);
    }

    /**
     * @brief Gettere della proprietà enum (opzioni di scelta)
     * @return proprietà enum
     */
    public function getEnum() {

        return $this->_enum;
    }

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        $db = db::instance();

        if($this->_m2m_controller) {
            $m2m = new $this->_m2m(null, $this->_m2m_controller);
        }
        else {
            $m2m = new $this->_m2m(null);
        }
        $rows = $db->select('id', $m2m->getTable(), $this->_m2m_where, array('order' => $this->_m2m_order));
        $enum = array();
        foreach($rows as $row) {
            if($this->_m2m_controller) {
                $obj = new $this->_m2m($row['id'], $this->_m2m_controller);
            }
            else {
                $obj = new $this->_m2m($row['id']);
            }
            $enum[$obj->id] = (string) $obj;
        }

        $this->_value = explode(',', $this->_model->{$this->_name});
        $this->_enum = $enum;
        $this->_name .= "[]";

        return parent::formElement($form, $options);
    }

    /**
     * @brief Formatta un elemento input per l'inserimento in database
     * @see Gino.Field::clean()
     * @see cleanVar()
     * @param array $options
     *   array associativo di opzioni
     *   - @b value_type (string): tipo di valore
     *   - @b method (array): metodo di recupero degli elementi del form
     *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
     *   - @b asforminput (boolean)
     * @return valore ripulito
     */
    public function clean($options=null) {

        $value_type = $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $_POST;
        $escape = gOpt('escape', $options, true);

        $value = cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));

        if(gOpt('asforminput', $options, false)) {
            return $value;
        }

        if($value) $value = implode(',', $value);
        return $value;
    }

    /**
     * @brief Definisce la condizione WHERE per il campo
     * @see Gino.Field::filterWhereClause()
     */
    public function filterWhereClause($value) {

        $parts = array();
        foreach($value as $v) {
            $parts[] = $this->_table.".".$this->_name." REGEXP '[[:<:]]".$v."[[:>:]]'";
        }

        return "(".implode(' OR ', $parts).")";
    }
}
