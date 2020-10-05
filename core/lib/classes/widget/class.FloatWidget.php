<?php
/**
 * @file class.FloatWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FloatWidget
 *
 * @copyright 2015-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo float nei form
 *
 * @copyright 2015-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FloatWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($options) {
	
		parent::printInputForm($options);
		
		if(!array_key_exists('maxlength', $options)) {
			
			if(in_array('int_digits', $options) and $options['int_digits'] > 0) {
				$options['maxlength'] = $options['int_digits']+1;
			}
			elseif(in_array('decimal_digits', $options) and $options['decimal_digits'] > 0) {
				$options['maxlength'] = $options['decimal_digits'];
			}
		}
		
		$buffer = Input::input_label($this->_name, 'text', $this->_value_retrieve, $this->_label, $options);
		
		return $buffer;
	}
}
