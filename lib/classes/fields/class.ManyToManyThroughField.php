<?php
/**
 * @file class.ManyToManyThroughField.php
 * @brief Contiene la definizione ed implementation della classe Gino.ManyToManyThroughField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo many to many con associazione attraverso un modello che porta informazioni aggiuntive
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Il prefisso dei nomi degli input che compongono il ManyToManyThroughField viene impostato dal javascript (m2mt_).
 */
class ManyToManyThroughField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_m2m, $_m2m_controller, $_controller;
	
    /**
     * @brief Costruttore
     *
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b controller (object): controller del modello cui appartiene il campo
     *     - @b m2m (string): classe attraverso la quale si esprime la relazione molti a molti (nome completo di namespace)
     *     - @b m2m_controller (object): oggetto controller da passare evenualmente al costruttore della classe m2m
     * @return void
     */
    function __construct($options) {

        $this->_default_widget = 'unit';
        parent::__construct($options);
        
        $this->_controller = $options['controller'];
        $this->_m2m = $options['m2m'];
        $this->_m2m_controller = array_key_exists('m2m_controller', $options) ? $options['m2m_controller'] : null;
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    
    	$prop = parent::getProperties();
    
    	$prop['controller'] = $this->_controller;
    	$prop['m2m'] = $this->_m2m;
    	$prop['m2m_controller'] = $this->_m2m_controller;
    
    	return $prop;
    }
    
    /**
     * @see Gino.Field::valueFromDb()
     * 
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
    		throw new \Exception(_("Valore non valido"));
    	}
    }
}
