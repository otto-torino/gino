<?php
/**
 * @file class.Widget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Widget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce i campi nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Widget {

    protected $_name, $_label, $_value, $_default;
    
    /**
     * Oggetto Gino.Form
     * @var object
     */
    protected $_form;
    
    /**
     * Costruttore
     */
    function __construct() {
    	
    }
    
    /**
     * @brief Stampa l'input form associato al widget
     * 
     * @param object $form oggetto Gino.Form
     * @param array $options array associativo di opzioni del campo/modello
     * @return string
     */
    public function printInputForm($form, $options) {
    
    	$this->_name = $options['name'];
    	$this->_label = $options['label'];
    	$this->_value = $options['value'];
    	$this->_default = $options['default'];
    	
    	$this->_form = $form;
    	
    	//if(!$this->_model->id and !is_null($this->_default) and $this->_value === null) {
    	if(!is_null($this->_default) and $this->_value === null) {
    		$this->_value = $this->_default;
    	}
    }
}
