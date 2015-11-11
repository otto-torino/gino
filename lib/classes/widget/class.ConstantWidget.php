<?php
/**
 * @file class.ConstantWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ConstantWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo costante nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ConstantWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		$view_value = array_key_exists('view_value', $options) ? htmlChars($options['view_value']) : null;
		
		$buffer = $this->_form->hidden($this->_name, htmlInput($this->_value), $options);
		$buffer .= $this->_form->noinput($this->_label, $view_value, $options);
		
		return $buffer;
	}
}
