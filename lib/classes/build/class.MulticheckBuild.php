<?php
/**
 * @file class.MulticheckBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.MulticheckBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo multicheck
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class MulticheckBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_refmodel, $_refmodel_order, $_refmodel_where, $_refmodel_controller;
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

        $this->_refmodel = $options['refmodel'];
        $this->_refmodel_where = $options['refmodel_where'];
        $this->_refmodel_order = $options['refmodel_order'];
        $this->_refmodel_controller = $options['refmodel_controller'];
        $this->_choice = $options['choice'];
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return rappresentazione a stringa dei modelli associati separati da virgola
     */
    public function __toString() {

        $res = array();
        foreach(explode(', ', $this->_model->{$this->_name}) as $id) {
            if($this->_refmodel_controller) {
                $obj = new $this->_refmodel($id, $this->_refmodel_controller);
            }
            else {
                $obj = new $this->_refmodel($id);
            }
            $res[] = (string) $obj;
        }
        return implode(', ', $res);
    }

    /**
     * @brief Getter della proprietà choice
     * @return proprietà choice
     */
    public function getChoice() {
        return $this->_choice;
    }
    
    /**
     * @see Gino.Build::formElement()
     */
    public function formElement($mform, $options=array()) {

        $db = Db::instance();
        if($this->_refmodel_controller) {
            $model = new $this->_refmodel(null, $this->_refmodel_controller);
        }
        else {
            $model = new $this->_refmodel(null);
        }
        
        $choice = array();
        $rows = $db->select('id', $model->getTable(), $this->_refmodel_where, array('order' => $this->_refmodel_order));
        if($rows && count($rows))
        {
        	$selected_part = array();
        	$not_selected_part = array();
        	
        	foreach($rows as $row) {
            	if($this->_refmodel_controller) {
            		$model = new $this->_refmodel($row['id'], $this->_refmodel_controller);
            	}
            	else {
               		$model = new $this->_refmodel($row['id']);
            	}
            	if(is_array($this->_value) and in_array($row['id'], $this->_value)) {
                	$selected_part[$row['id']] = (string) $model;
            	}
            	else {
                	$not_selected_part[$row['id']] = (string) $model;
            	}
        	}
        	
        	$choice = $selected_part + $not_selected_part;
        }

        $options['choice'] = $choice;
        
        $this->_value = explode(',', $this->_model->{$this->_name});
        $this->_name .= "[]";

        return parent::formElement($mform, $options);
    }

    /**
     * @see Gino.Build::clean()
     * @param array $options array associativo di opzioni
     *   - opzioni della funzione Gino.clean_array()
     * @return string
     */
    public function clean($options=null) {
    	
    	parent::clean($options);
    	
    	$options['asforminput'] = false;
    	return clean_array($this->_request_value, $options);
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
    	
    	if(is_array($this->_value) && count($this->_value))
    	{
    		$values = array();
    		
    		if($this->_refmodel_controller) {
    			$model = new $this->_refmodel(null, $this->_refmodel_controller);
    		}
    		else {
    			$model = new $this->_refmodel(null);
    		}
    		
    		$refs = implode(',', $this->_value);
    		if($this->_refmodel_where) {
    			$where = $this->_refmodel_where." AND id IN ($refs)";
    		}
    		else {
    			$where = "id IN ($refs)";
    		}
    		
    		$rows = $db->select('id', $model->getTable(), $where, array('order' => $this->_refmodel_order));
    		if($rows && count($rows))
    		{
    			foreach($rows as $row) {
    				
    				$m2m_id = $row['id'];
    				
    				if($this->_refmodel_controller) {
    					$model = new $this->_refmodel($m2m_id, $this->_refmodel_controller);
    				}
    				else {
    					$model = new $this->_refmodel($m2m_id);
    				}
    				
    				$values[] = $model;
    			}
    		}
    		
    		return implode(', ', $values);
    	}
    	else return null;
    }
}
