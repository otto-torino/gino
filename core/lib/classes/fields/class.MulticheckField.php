<?php
/**
 * @file class.MulticheckField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.MulticheckField
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo multicheck
 *
 * I valori da associare al campo risiedono in un'altra tabella e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: multicheck
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class MulticheckField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_refmodel, $_refmodel_where, $_refmodel_order, $_refmodel_controller;
	protected $_choice;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b refmodel (object): classe del modello correlato (nome completo di namespace)
     *     - @b refmodel_where (mixed): condizioni della query per filtrare i possibili modelli da associare
     *       - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
     *       - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
     *     - @b refmodel_order (string): ordinamento dei valori (es. name ASC)
     *     - @b refmodel_controller (\Gino\Controller): classe Controller del modello refmodel, essenziale se il modello appartiene ad un modulo istanziabile
     *     - @b choice (array): elenco degli elementi di scelta
     */
    function __construct($options) {

        $this->_default_widget = 'multicheck';
        parent::__construct($options);
        
        $this->_refmodel = $options['refmodel'];
        $this->_refmodel_where = array_key_exists('refmodel_where', $options) ? $options['refmodel_where'] : null;
        $this->_refmodel_order = array_key_exists('refmodel_order', $options) ? $options['refmodel_order'] : 'id';
        $this->_refmodel_controller = array_key_exists('refmodel_controller', $options) ? $options['refmodel_controller'] : null;
        $this->_choice = array_key_exists('choice', $options) ? $options['choice'] : array();
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    
    	$prop = parent::getProperties();
    	
    	$prop['refmodel'] = $this->_refmodel;
    	$prop['refmodel_where'] = $this->_refmodel_where;
    	$prop['refmodel_order'] = $this->_refmodel_order;
    	$prop['refmodel_controller'] = $this->_refmodel_controller;
    	$prop['choice'] = $this->_choice;
    	
    	return $prop;
    }
}
