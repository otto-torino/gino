<?php
/**
 * @file class.textField.php
 * @brief Contiene la classe textField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

loader::import('class/fields', 'Field');

/**
 * @brief Campo di tipo TEXT
 * 
 * Tipologie di input associabili: textarea, testo, editor html
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TextField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_trnsl;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b trnsl (boolean): campo con traduzioni
	 * @return void
	 */
	function __construct($options) {
		
		parent::__construct($options);
		
		$this->_default_widget = 'textarea';
		$this->_value_type = 'string';
		
		$this->_trnsl = isset($options['trnsl']) ? $options['trnsl'] : true;
	}
	
	public function getTrnsl() {
		
		return $this->_trnsl;
	}
	
	public function setTrnsl($value) {
		
		if(is_bool($value)) $this->_trnsl = $value;
	}
	
	public function canBeOrdered() {

		return false;
	}
	
	/**
	 * @see field::filterWhereClause()
	 */
	public function filterWhereClause($value) {

		$value = str_replace("'", "''", $value);
		
		if(preg_match("#^\"([^\"]*)\"$#", $value, $matches))
			$condition = "='".$matches[1]."'";
		elseif(preg_match("#^\"([^\"]*)$#", $value, $matches))
			$condition = " LIKE '".$matches[1]."%'";
		else
			$condition = " LIKE '%".$value."%'";
		
		return $this->_table.".".$this->_name.$condition;
	}
	
	/**
	 * @see field::formElement()
	 */
	public function formElement($form, $options) {
		
		if(!isset($options['trnsl'])) $options['trnsl'] = $this->_trnsl;
		if(!isset($options['field'])) $options['field'] = $this->_name;

        if(isset($options['is_filter']) and $options['is_filter']) {
            $options['widget'] = 'input';
        }
		
		return parent::formElement($form, $options);
	}
	
	/**
	 * @see field::clean()
	 */
	public function clean($options=null) {
		
		$value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
		$method = isset($options['method']) ? $options['method'] : $_POST;
		$escape = gOpt('escape', $options, true);
		$widget = gOpt('widget', $options, null);
		
		if($widget == 'editor')
			return cleanVarEditor($method, $this->_name, '');
		else
			return cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));
	}
}
?>
