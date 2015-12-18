<?php
/**
 * @file class.DateWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DateWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo date nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DateWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($options) {
	
		parent::printInputForm($options);
		
		$value = dbDateToDate($this->_value_retrieve, "/");
		
		$buffer = Input::input_date($this->_name, $value, $this->_label, $options);
		
		return $buffer;
	}
}
