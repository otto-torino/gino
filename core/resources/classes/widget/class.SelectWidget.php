<?php
/**
 * @file class.SelectWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SelectWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo select nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class SelectWidget extends Widget {

	/**
	 * @see Gino.Widget::inputValue()
	 */
	public function inputValue($value, $options=array()) {
		
		return $value;
	}
	
	/**
	 * @see Gino.Widget::printInputForm()
	 * 
	 * @param array $options
	 *   - @b choice (mixed): elementi del select
	 */
	public function printInputForm($options) {
	
		parent::printInputForm($options);
		
		$choice = array_key_exists('choice', $options) ? $options['choice'] : null;
		
		$buffer = Input::select_label($this->_name, $this->_value, $choice, $this->_label, $options);
		
		return $buffer;
	}
}
