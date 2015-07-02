<?php
/**
 * @file class.ManyToManyBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ManyToManyField
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo many to many
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_m2m, $_m2m_order, $_m2m_where;
    protected $_join_table, $_join_table_id, $_join_table_m2m_id;
    protected $_add_related, $_add_related_url;
    
    protected $_choice;

    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_add_related = $options['add_related'];
        $this->_add_related_url = $options['add_related_url'];
		$this->_m2m = $options['m2m'];
        $this->_m2m_where = $options['m2m_where'];
        $this->_m2m_order = $options['m2m_order'];
        $this->_m2m_controller = $options['m2m_controller'];
        $this->_join_table = $options['join_table'];
        $this->_self = $options['self'];
        $this->_join_table_id = $options['join_table_id'];
        $this->_join_table_m2m_id = $options['join_table_m2m_id'];
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return rappresentazione a stringa dei modelli associati separati da virgola
     */
    public function __toString() {

        $res = array();
        foreach($this->_model->{$this->_name} as $id) {
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
     * @brief Getter della proprietà jon_table
     * @return proprietà join_table
     */
    public function getJoinTable() {
        return $this->_join_table;
    }

    /**
     * @brief Getter della proprietà join_table_id (nome della chiave esterna nella tabella di join del modello che ha il campo)
     * @retuirn proprietà join_table_id
     */
    public function getJoinTableId() {
        return $this->_join_table_id;
    }

    /**
     * @brief Getter della proprietà join_table_m2m_id (nome della chiave esterna nella tabella di join del modello m2m)
     * @retuirn proprietà join_table_m2m_id
     */
    public function getJoinTableM2mId() {
        return $this->_join_table_m2m_id;
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
        $selected_part = array();
        $not_selected_part = array();
        $this->_value = $this->_model->{$this->_name};
        foreach($rows as $row) {
            if($this->_m2m_controller) {
                $m2m = new $this->_m2m($row['id'], $this->_m2m_controller);
            }
            else {
                $m2m = new $this->_m2m($row['id']);
            }
            if(is_array($this->_value) and in_array($row['id'], $this->_value)) {
                $selected_part[$row['id']] = (string) $m2m;
            }
            else {
                $not_selected_part[$row['id']] = (string) $m2m;
            }
        }

        $choice = $selected_part + $not_selected_part;

        $this->_choice = $choice;
        $this->_name .= "[]";

        if($this->_add_related) {
            $options['add_related'] = array(
                'title' => _('inserisci').' '.$m2m->getModelLabel(),
                'id' => 'add_'.$this->_name,
                'url' => $this->_add_related_url
            );
        }

        return parent::formElement($form, $options);
    }

    /**
     * @see Gino.Build::clean()
     */
    public function clean($options=null) {

        $value = parent::clean($options);

        return is_null($value) ? array() : $value;
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
    
    /**
     * @see Gino.Build::retrieveValue()
     */
    public function retrieveValue() {
    	
    	$db = \Gino\Db::instance();
    	
    	$rows = $db->select('*', $this->_join_table, $this->_join_table_id."='".$this->_value."'");
    	$values = array();
    	$m2m_class = $this->_m2m;
    	
    	foreach($rows as $row) {
    		
    		$m2m_id = $row[$this->_join_table_m2m_id];
    		
    		$values[] = new $m2m_class($m2m_id);
    	}
    	return $values;
    }
}
