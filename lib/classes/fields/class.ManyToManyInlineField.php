<?php
/**
 * @file class.ManyToManyInlineField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ManyToManyInlineField
 *
 * @copyright 2013-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * @copyright 2013-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyInlineField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_m2m, $_m2m_order, $_m2m_where, $_m2m_controller;
	
    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b m2m (object): classe del modello correlato (nome completo di namespace)
     *     - @b m2m_where (mixed): condizioni della query per filtrare i possibili modelli da associare
     *       - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
     *       - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
     *     - @b m2m_order (string): ordinamento dei valori (es. name ASC)
     *     - @b m2m_controller (\Gino\Controller): classe Controller del modello m2m, essenziale se il modello appartiene ad un modulo istanziabile
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
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    
    	$prop = parent::getProperties();
    
    	$prop['m2m'] = $this->_m2m;
    	$prop['m2m_where'] = $this->_m2m_where;
    	$prop['m2m_order'] = $this->_m2m_order;
    	$prop['m2m_controller'] = $this->_m2m_controller;
    
    	return $prop;
    }
}
