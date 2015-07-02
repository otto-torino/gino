<?php
/**
 * @file class.ManyToManyInlineBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ManyToManyInlineBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo many to many gestito senza tabella di join
 * 
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyInlineBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_m2m, $_m2m_order, $_m2m_where, $_m2m_controller;
    
    protected $_choice;

    /**
     * Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     * @return void
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_m2m = $options['m2m'];
        $this->_m2m_where = $options['m2m_where'];
        $this->_m2m_order = $options['m2m_order'];
        $this->_m2m_controller = $options['m2m_controller'];
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
     * @brief Gettere della proprietà choice (opzioni di scelta)
     * @return array
     */
    public function getChoice() {

        return $this->_choice;
    }

    /**
     * @see Gino.Build::formElement()
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
        $choice = array();
        foreach($rows as $row) {
            if($this->_m2m_controller) {
                $obj = new $this->_m2m($row['id'], $this->_m2m_controller);
            }
            else {
                $obj = new $this->_m2m($row['id']);
            }
            $choice[$obj->id] = (string) $obj;
        }

        $this->_value = explode(',', $this->_model->{$this->_name});
        $this->_choice = $choice;
        $this->_name .= "[]";

        return parent::formElement($form, $options);
    }

    /**
     * @see Gino.Build::clean()
     * 
     * @param array $options array associativo di opzioni del parent con l'aggiunta di
     *   - @b asforminput (boolean)
     */
    public function clean($options=null) {

        $value = parent::clean($options);

        if(gOpt('asforminput', $options, false)) {
            return $value;
        }

        if($value) $value = implode(',', $value);
        return $value;
    }

    /**
     * @see Gino.Build::filterWhereClause()
     * 
     * @param array $value
     */
    public function filterWhereClause($value) {

        $parts = array();
        foreach($value as $v) {
            $parts[] = $this->_table.".".$this->_name." REGEXP '[[:<:]]".$v."[[:>:]]'";
        }

        return "(".implode(' OR ', $parts).")";
    }
}
