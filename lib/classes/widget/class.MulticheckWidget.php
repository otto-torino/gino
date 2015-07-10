<?php
/**
 * @file class.MulticheckWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.MulticheckWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo multicheck nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class MulticheckWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		$choice = array_key_exists('choice', $options) ? $options['choice'] : null;
		
		$buffer = $this->_form->multipleCheckbox($this->_name, $this->_value, $choice, $this->_label, $options);
		
		return $buffer;
	}
}
