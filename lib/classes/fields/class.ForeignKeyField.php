<?php
/**
 * @file class.ForeignKeyField.php
 * @brief Contiene la definizione ed implementazione delal classe Gino.ForeignKeyField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo chiave esterna
 *
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: select, radio
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ForeignKeyField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_foreign, $_foreign_where, $_foreign_order;
	protected $_add_related, $_add_related_url;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - opzioni specifiche del tipo di campo
     *     - @b foreign (string): nome della classe della chiave esterna
     *     - @b foreign_where (mixed): condizioni della query
     *       - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
     *       - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
     *     - @b foreign_order (string): ordinamento dei valori (es. name ASC); default 'id'
     *     - @b foreign_controller (object): oggetto del controller della classe della chiave esterna
     *     - @b add_related (boolean)
     *     - @b add_related_url (string)
     * @return istanza di Gino.ForeignKeyField
     */
    function __construct($options) {

        $this->_default_widget = 'select';
        parent::__construct($options);
        
        $this->_value_type = 'int';
        
        $this->_foreign = $options['foreign'];
        $this->_foreign_where = array_key_exists('foreign_where', $options) ? $options['foreign_where'] : null;
        $this->_foreign_order = array_key_exists('foreign_order', $options) ? $options['foreign_order'] : 'id';
        $this->_foreign_controller = array_key_exists('foreign_controller', $options) ? $options['foreign_controller'] : null;
        $this->_add_related = array_key_exists('add_related', $options) ? $options['add_related'] : false;
        $this->_add_related_url = array_key_exists('add_related_url', $options) ? $options['add_related_url'] : '';
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    
    	$prop = parent::getProperties();
    	
    	$prop['foreign'] = $this->_foreign;
    	$prop['foreign_where'] = $this->_foreign_where;
    	$prop['foreign_order'] = $this->_foreign_order;
    	$prop['foreign_controller'] = $this->_foreign_controller;
    	$prop['add_related'] = $this->_add_related;
    	$prop['add_related_url'] = $this->_add_related_url;
    	
    	return $prop;
    }

    /**
     * @see Gino.Field::valueToDb()
     * @return object
     */
    public function valueToDb($value) {
    	 
    	return (int) $value;
    }
}
