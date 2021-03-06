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
	protected $_auto_now, $_auto_now_add, $_view_input;
	
    /**
     * Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b auto_now (boolean): imposta automaticamente il valore del campo al tempo/ora corrente ogni volta che l'oggetto viene salvato (default: true)
     *     - @b auto_now_add (boolean): imposta automaticamente il valore del campo al tempo/ora corrente la prima volta che l'oggetto viene creato (default: true)
     *     - @b view_input (boolean): per visualizzare l'input nel form (default false)
     */
    function __construct($options) {

        $this->_default_widget = 'datetime';
        parent::__construct($options);
        
        $this->_auto_now = array_key_exists('auto_now', $options) ? $options['auto_now'] : true;
        $this->_auto_now_add = array_key_exists('auto_now_add', $options) ? $options['auto_now_add'] : true;
        $this->_view_input = array_key_exists('view_input', $options) ? $options['view_input'] : false;
        
        if($this->_auto_now || $this->_auto_now_add) {
        	$this->_widget = null;
        	$this->_required = false;
        }
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    	 
    	$prop = parent::getProperties();
    	
    	$prop['auto_now'] = $this->_auto_now;
    	$prop['auto_now_add'] = $this->_auto_now_add;
    	$prop['view_input'] = $this->_view_input;
    	
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
}
