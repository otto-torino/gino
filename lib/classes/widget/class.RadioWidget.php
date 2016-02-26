<?php
/**
 * @file class.RadioWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.RadioWidget
 *
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo radio button nei form
 *
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class RadioWidget extends Widget {

	/**
	 * @see Gino.Widget::inputValue()
	 */
	public function inputValue($value, $options=array()) {
	
		if(is_bool($value)) {
			return (int) $value;
		} else {
			return parent::inputValue($value, $options);
		}
	}
	
	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($options) {
	
		parent::printInputForm($options);
		
		$choice = array_key_exists('choice', $options) ? $options['choice'] : null;
		$default = array_key_exists('default', $options) ? $options['default'] : $this->_default;
		
		$buffer = Input::radio_label($this->_name, $this->_value_retrieve, $choice, $default, $this->_label, $options);
		
		return $buffer;
	}
}
