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
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		$checked = array_key_exists('checked', $options) ? $options['checked'] : false;
		$buffer = $this->_form->ccheckbox($this->_name, $checked, $this->_value, $this->_label, $options);
		
		return $buffer;
	}
}
