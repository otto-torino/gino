<?php
/**
 * @file class.ManyToManyThroughBuild.php
 * @brief Contiene la definizione ed implementation della classe Gino.ManyToManyThroughBuild
 *
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo many to many con associazione attraverso un modello che porta informazioni aggiuntive
 *
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyThroughBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_controller, $_m2m, $_m2m_controller, $_remove_fields;
    
    protected $_model_table_id;

    /**
     * @brief Costruttore
     *
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     *   - opzioni definite come proprietà specifiche del tipo di campo
     * @return void
     * 
     * Ridefinisce il valore della proprietò $_value come array dei valori id dei record associati.
     */
    function __construct($options) {

    	parent::__construct($options);
    	
        $this->_m2m = $options['m2m'];
        $this->_m2m_controller = $options['m2m_controller'];
        $this->_controller = $options['controller'];
        $this->_remove_fields = $options['remove_fields'];
        
        $this->_model_table_id = strtolower(get_name_class($this->_model)).'_id';
        
        $this->setValue($this->getValues());
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string, lista rappresentazioni a stringa dei modelli correlati separati da virgola
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
	 * {@inheritDoc}
	 * @see \Gino\Build::canBeOrdered()
	 */
	public function canBeOrdered() {
	    
	    return false;
	}
    
    /**
     * Valori dei record di associazione
     * 
     * @return array
     */
    public function getValues() {
    	
    	$values = array();
    	
		$db = Db::instance();
		$rows = $db->select('*', $this->getM2mTable(), $this->_model_table_id."='".$this->_model->id."'", array('order' => 'id ASC'));
		foreach($rows as $row) {
			$class = $this->_m2m;
			if($this->_m2m_controller) {
				$m2m_obj = new $class($row['id'], $this->_m2m_controller);
			}
			else {
				$m2m_obj = new $class($row['id']);
			}
			$values[] = $m2m_obj->id;
		}
		
		return $values;
    }

    /**
     * @brief Restituisce la classe del m2m
     * 
     * @see Gino.Model::m2mtObject()
     * @return string, nome della classe del modello m2m
     */
    public function getM2m() {
        return $this->_m2m;
    }

    /**
     * @brief Restituisce il controller del modello cui appartiene il campo
     * @return Gino.Controller controller
     */
    public function getController() {
        return $this->_controller;
    }

    /**
     * @brief Restituisce la tabella dati della classe m2m
     * @return string, nome tabella
     */
    public function getM2mTable() {
        
		if($this->_m2m_controller) {
			$obj = new $this->_m2m(null, $this->_m2m_controller);
        }
        else {
            $obj = new $this->_m2m(null);
        }

        return $obj->getTable();
    }

    /**
     * @brief Restituisce il nome del campo che immagazzina l'id del modello che ha la relazione m2m
     * @return string, nome del campo
     */
    public function getModelTableId() {
        return $this->_model_table_id;
    }

    /**
     * @see Gino.Build::formElement()
     */
    public function formElement($mform, $options=array()) {

    	$widget = isset($options['widget']) ? $options['widget'] : $this->_widget;
    	
    	if($widget != 'unit') {
    		return parent::formElement($mform, $options);
    	}
    	
    	$m2m_model = $this->_m2m_controller ? new $this->_m2m(null, $this->_m2m_controller) : new $this->_m2m(null);
    	
    	$inputs = array();
    	
    	if(is_array($this->_value))
    	{
    		foreach($this->_value as $id) {
    			
    			$m2m = $this->_model->m2mtObject($this->_name, $id);
    			$inputs[] = $m2m;
    		}
    	}
    	
    	$options['controller'] = $this->_controller;
    	$options['m2m_model'] = $m2m_model;
    	$options['inputs'] = $inputs;
    	$options['remove_fields'] = $this->_remove_fields;
    	
    	return parent::formElement($mform, $options);
    }

    /**
     * @see Gino.Build::filterWhereClause()
     */
    public function filterWhereClause($value, $options=array()) {
        
    	return null;
    }
}
