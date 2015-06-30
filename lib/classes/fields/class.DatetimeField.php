<?php
/**
 * @file class.DatetimeField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DatetimeField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo DATETIME
 *
 * Tipologie di input associabili: nessun input, testo nascosto, testo in formato datetime (YYYY-MM-DD HH:MM:SS). \n
 * 
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DatetimeField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_auto_now, $_auto_now_add;
	
    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b auto_now (boolean): imposta automaticamente il valore del campo al tempo/ora corrente ogni volta che l'oggetto viene salvato (default: true)
     *     - @b auto_now_add (boolean): imposta automaticamente il valore del campo al tempo/ora corrente la prima volta che l'oggetto viene creato (default: true)
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_value_type = 'string';
        
        $this->_auto_now = array_key_exists('auto_now', $options) ? $options['auto_now'] : true;
        $this->_auto_now_add = array_key_exists('auto_now_add', $options) ? $options['auto_now_add'] : true;
        
        if($this->_auto_now || $this->_auto_now_add) {
        	$this->_default_widget = null;
        	$this->_required = false;
        }
        else {
        	$this->_default_widget = 'datetime';
        }
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    	 
    	$prop = parent::getProperties();
    	
    	$prop['auto_now'] = $this->_auto_now;
    	$prop['auto_now_add'] = $this->_auto_now_add;
    	
    	return $prop;
    }
    
    /**
     * @brief Getter della proprietà auto_now (update ad ogni modifica del record)
     * @return proprietà auto_now
     */
    public function getAutoNow() {
    
    	return $this->_auto_now;
    }
    
    /**
     * @brief Setter della proprietà auto_now
     * @param bool $value
     * @return void
     */
    public function setAutoNow($value) {
    
    	if(is_bool($value)) $this->_auto_now = $value;
    }
    
    /**
     * @brief Getter della proprietà auto_now_add (update in inserimento record)
     * @return proprietà auto_now_add
     */
    public function getAutoNowAdd() {
    
    	return $this->_auto_now_add;
    }
    
    /**
     * @brief Setter della proprietà auto_now_add
     * @param bool $value
     * @return void
     */
    public function setAutoNowAdd($value) {
    
    	if(is_bool($value)) $this->_auto_now_add = $value;
    }

    /**
     * @see Gino.Field::getValue()
     */
    public function getValue($value) {
    	 
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_string($value)) {
    		return $value;
    	}
    	else throw new \Exception(_("Valore non valido"));
    }
}
