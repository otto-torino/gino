<?php
/**
 * @file class.FloatWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FloatWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo float nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FloatWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		if(!array_key_exists('maxlength', $options)) {
			$options['maxlength'] = $this->_int_digits+1;
		}
		
		$value = $this->_form->retvar($this->_name, htmlInput($this->_value));
		
		$buffer = $this->_form->cinput($this->_name, 'text', $value, $this->_label, $options);
		
		return $buffer;
	}
}
