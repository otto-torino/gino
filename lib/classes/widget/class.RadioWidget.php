<?php
/**
 * @file class.RadioWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.RadioWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo radio button nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class RadioWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		$choice = array_key_exists('choice', $options) ? $options['choice'] : null;
		$default = array_key_exists('default', $options) ? $options['default'] : $this->_default;
		
		$value = $this->_form->retvar($this->_name, htmlInput($this->_value));
		
		$buffer = $this->_form->cradio($this->_name, $value, $choice, $default, $this->_label, $options);
		
		return $buffer;
	}
}
