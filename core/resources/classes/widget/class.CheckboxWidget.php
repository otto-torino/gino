<?php
/**
 * @file class.CheckboxWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.CheckboxWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi checkbox nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class CheckboxWidget extends Widget {

	/**
	 * @see Gino.Widget::inputValue()
	 */
	public function inputValue($value, $options=array()) {
		
		return $value;
	}
	
	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($options) {
	
		parent::printInputForm($options);
		
		$checked = gOpt('checked', $options, false);
		$print_label = gOpt('print_label', $options, true);
		
		if($print_label) {
			$buffer = Input::checkbox_label($this->_name, $checked, $this->_value, $this->_label, $options);
		}
		else {
			$buffer = Input::checkbox($this->_name, $checked, $this->_value, $options);
		}
		
		return $buffer;
	}
}
