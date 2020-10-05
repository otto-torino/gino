<?php
/**
 * @file class.TextWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TextWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo testo nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TextWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($options) {
	
		parent::printInputForm($options);
		
		$print_label = gOpt('print_label', $options, true);
		
		if($print_label) {
			$buffer = Input::input_label($this->_name, 'text', $this->_value_retrieve, $this->_label, $options);
		} else {
			$buffer = Input::input($this->_name, 'text', $this->_value_retrieve, $options);
		}
		
		return $buffer;
	}
}
