<?php
/**
 * @file class.ManyToManyField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ManyToManyField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo many to many
 *
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: multicheck.
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_m2m, $_m2m_order, $_m2m_where;
	protected $_join_table;
	protected $_add_related, $_add_related_url;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b add_related (bool): includere o meno un bottone che permetta l'inserimento di nuovi modelli correlati nello stesso contesto
     *     - @b add_related_url (string): path alla vista per inserimento modello correlato
     *     - @b m2m (object): classe del modello correlato (nome completo di namespace)
     *     - @b m2m_where (mixed): condizioni della query per filtrare i possibili modelli da associare
     *       - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
     *       - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
     *     - @b m2m_order (string): ordinamento dei valori (es. name ASC)
     *     - @b m2m_controller (\Gino\Controller): classe Controller del modello m2m, essenziale se il modello appartiene ad un modulo istanziabile
     *     - @b join_table (string): nome tabella di join
     */
    function __construct($options) {

        $this->_default_widget = 'multicheck';
        parent::__construct($options);
        
        $this->_add_related = array_key_exists('add_related', $options) ? $options['add_related'] : false;
        $this->_add_related_url = array_key_exists('add_related_url', $options) ? $options['add_related_url'] : '';
        
        $this->_m2m = $options['m2m'];
        $this->_m2m_where = array_key_exists('m2m_where', $options) ? $options['m2m_where'] : null;
        $this->_m2m_order = array_key_exists('m2m_order', $options) ? $options['m2m_order'] : 'id';
        $this->_m2m_controller = array_key_exists('m2m_controller', $options) ? $options['m2m_controller'] : null;
        $this->_join_table = $options['join_table'];
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    
    	$prop = parent::getProperties();
    	
    	$prop['add_related'] = $this->_add_related;
    	$prop['add_related_url'] = $this->_add_related_url;
    	$prop['m2m'] = $this->_m2m;
    	$prop['m2m_where'] = $this->_m2m_where;
    	$prop['m2m_order'] = $this->_m2m_order;
    	$prop['m2m_controller'] = $this->_m2m_controller;
    	$prop['join_table'] = $this->_join_table;
    	
    	return $prop;
    }
    
    /**
     * @see Gino.Field::valueFromDb()
     * @param integer $value valore id del record
     * @return null or array (valori id dei record di associazione)
     */
    public function valueFromDb($value) {
    	
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_array($value)) {
    		return $value;
    	}
    	else {
    		throw new \Exception(sprintf(("Valore non valido del campo \"%s\""), $this->_name));
    	}
    }
    
	/**
	 * @see Gino.Field::valueToDb()
	 * @return null or array
	 */
    public function valueToDb($value) {
    
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_array($value)) {
    		return $value;
    	}
    	else {
    		return null;
    	}
    }
}
