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
    protected $_m2m, $_m2m_order, $_m2m_where, $_m2m_controller, $_join_table, $_add_related, $_add_related_url;
    
    protected $_join_table_id, $_join_table_m2m_id;

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
        
		$this->_join_table_id = strtolower(get_name_class($this->_model)).'_id';
        $this->_join_table_m2m_id = strtolower(get_name_class($this->_m2m)).'_id';
        
        $this->setValue($this->getJoinM2mValues());
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
     * @brief Getter della proprietà join_table
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
     * Valori dei record della tabella m2m associati al modello
     * 
     * @return array (id values)
     */
    private function getJoinM2mValues() {
    	
    	$db = Db::instance();
    	
    	$where = $this->_join_table_id."='".$this->_model->id."'";
    	
    	$items = array();
    	$rows = $db->select($this->_join_table_m2m_id, $this->_join_table, $where, array('debug'=>false));
    	if($rows && count($rows)) {
    		foreach ($rows AS $row)
    		{
    			$items[] = $row[$this->_join_table_m2m_id];
    		}
    	}
    	return $items;
    }

    /**
     * @see Gino.Build::formElement()
     */
    public function formElement($mform, $options=array()) {

        $db = Db::instance();
        if($this->_m2m_controller) {
            $m2m = new $this->_m2m(null, $this->_m2m_controller);
        }
        else {
            $m2m = new $this->_m2m(null);
        }
        
        $choice = array();
        $rows = $db->select('id', $m2m->getTable(), $this->_m2m_where, array('order' => $this->_m2m_order));
        if($rows && count($rows))
        {
        	$selected_part = array();
        	$not_selected_part = array();
        	
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
        }

        $options['choice'] = $choice;
        
        $this->_name .= "[]";

        $is_filter = array_key_exists('is_filter', $options) ? $options['is_filter'] : false;
        
        if($this->_add_related && !$is_filter) {
            $options['add_related'] = array(
                'title' => _('inserisci').' '.$m2m->getModelLabel(),
                'id' => 'add_'.$this->_name,
                'url' => $this->_add_related_url
            );
        }

        return parent::formElement($mform, $options);
    }

    /**
     * @see Gino.Build::clean()
     * 
     * @param array $options array associativo di opzioni
     *   - opzioni della funzione Gino.clean_array()
     * @return array
     */
    public function clean($request_value, $options=null) {
    	
    	return clean_array($request_value, $options);
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
     * @see Gino.Build::printValue()
     */
    public function printValue() {
    	
    	$db = \Gino\Db::instance();
    	
    	if(is_array($this->_value) && count($this->_value))
    	{
    		$values = array();
    		
    		$refs = implode(',', $this->_value);
    		$rows = $db->select('*', $this->_join_table, $this->_join_table_id."='".$this->_model->id."' AND ".$this->_join_table_m2m_id." IN ($refs)");
    		if($rows && count($rows))
    		{
    			foreach($rows as $row) {
    				
    				$m2m_id = $row[$this->_join_table_m2m_id];
    				
    				if($this->_m2m_controller) {
    					$m2m = new $this->_m2m($m2m_id, $this->_m2m_controller);
    				}
    				else {
    					$m2m = new $this->_m2m($m2m_id);
    				}
    				
    				$values[] = $m2m;
    			}
    		}
    		
    		return implode(', ', $values);
    	}
    	else return null;
    }
}
