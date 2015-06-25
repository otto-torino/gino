<?php
/**
 * @file class.SelectWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SelectWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo select nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class SelectWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		$enum = array_key_exists('enum', $options) ? $options['enum'] : null;
		
		$buffer = $this->_form->cselect($this->_name, $this->_value, $enum, $this->_label, $options);
		
		return $buffer;
	}
}
