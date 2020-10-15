<?php
/**
 * @file class.ForeignKeyBuild.php
 * @brief Contiene la definizione ed implementazione delal classe Gino.ForeignKeyBuild
 */
namespace Gino;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Campo di tipo chiave esterna
 *
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: select, radio, hidden
 */
class ForeignKeyBuild extends Build {

	/**
	 * @brief Proprietà dei campi
	 */
	protected $_foreign, $_foreign_where, $_foreign_order, $_foreign_controller, $_add_related, $_add_related_url;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);
        
        $this->_foreign = $options['foreign'];
        $this->_foreign_where = $options['foreign_where'];
        $this->_foreign_order = $options['foreign_order'];
        $this->_foreign_controller = $options['foreign_controller'];
        $this->_add_related = $options['add_related'];
        $this->_add_related_url = $options['add_related_url'];
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string, valore campo
     */
    public function __toString() {

    	if($this->_foreign_controller) {
        	$obj = new $this->_foreign($this->_model->{$this->_name}, $this->_foreign_controller);
        }
        else {
        	$obj = new $this->_foreign($this->_model->{$this->_name});
        }
        return (string) $obj;
    }

    /**
     * @see Gino.Build::formElement()
     */
    public function formElement($mform, $options=array()) {

        // Campi \Gino\ForeignKeyField di modelli gestiti come \Gino\ManyToManyThroughField
        $widget = isset($options['widget']) ? $options['widget'] : $this->_widget;
        
        if($widget == 'hidden') {
            return parent::formElement($mform, $options);
        }
        
        $db = db::instance();
    	
    	if($this->_foreign_controller) {
            $foreign = new $this->_foreign(null, $this->_foreign_controller);
        }
        else {
            $foreign = new $this->_foreign(null);
        }
        $rows = $db->select('id', $foreign->getTable(), $this->_foreign_where, array('order' => $this->_foreign_order));
        $choice = array();
        foreach($rows as $row) {
            if($this->_foreign_controller) {
                $f = new $this->_foreign($row['id'], $this->_foreign_controller);
            }
            else {
                $f = new $this->_foreign($row['id']);
            }
            $choice[$f->id] = (string) $f;
        }

        $options['choice'] = $choice;

        if($this->_add_related && (!isset($options['is_filter']) or !$options['is_filter'])) {
            $options['add_related'] = array(
                'title' => _('inserisci').' '.$foreign->getModelLabel(),
                'id' => 'add_'.$this->_name,
                'url' => $this->_add_related_url
            );
        }

        return parent::formElement($mform, $options);
    }
    
    /**
     * @see Gino.Build::printValue()
     * @return object
     */
    public function printValue() {
    	 
    	if(is_object($this->_value)) {
    		if($this->_foreign_controller) {
    			return new $this->_foreign((int) $this->_value->id, $this->_foreign_controller);
    		}
    		else {
    			return new $this->_foreign((int) $this->_value->id);
    		}
    	}
    	elseif(is_null($this->_value)) {
    		return null;
    	}
    	else {
    		if($this->_foreign_controller) {
    			return new $this->_foreign((int) $this->_value, $this->_foreign_controller);
    		}
    		else {
    			return new $this->_foreign((int) $this->_value);
    		}
    	}
    }
    
    /**
     * @see Gino.Build::clean()
     * @return integer
     */
    public function clean($request_value, $options=null) {
    	
    	return clean_int($request_value);
    }
}
