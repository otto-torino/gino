<?php
/**
 * @file class.DatetimeWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DatetimeWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo datetime nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DatetimeWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($options) {
	
		parent::printInputForm($options);
		
		$options['size'] = 20;
		$options['maxlength'] = 19;
		
		$buffer = Input::input_label($this->_name, 'text', $this->_value_retrieve, $this->_label, $options);
		
		return $buffer;
	}
}
