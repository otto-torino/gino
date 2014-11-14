<?php
/**
 * @file class.charField.php
 * @brief Contiene la classe charField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo CHAR (CHAR, VARCHAR)
 * 
 * Tipologie di input associabili: testo, campo nascosto, textarea
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class CharField extends Field {

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
		
		$this->_default_widget = 'text';
		$this->_value_type = 'string';
		
		$this->_trnsl = isset($options['trnsl']) ? $options['trnsl'] : true;
	}
	
	public function getTrnsl() {
		
		return $this->_trnsl;
	}
	
	public function setTrnsl($value) {
		
		if(is_bool($value)) $this->_trnsl = $value;
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
	 * Stampa l'elemento del form
	 * 
	 * @see field::formElement()
	 */
	public function formElement($form, $options) {
		
		if(!isset($options['trnsl'])) $options['trnsl'] = $this->_trnsl;
		if(!isset($options['field'])) $options['field'] = $this->_name;
		
		return parent::formElement($form, $options);
	}
}
?>
