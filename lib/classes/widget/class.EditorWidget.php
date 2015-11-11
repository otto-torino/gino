<?php
/**
 * @file class.EditorWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.EditorWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo editor nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EditorWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		$value = $this->_form->retvar($this->_name, htmlInputEditor($this->_value));
		
		$options['ckeditor'] = true;
		$options['label'] = $this->_label;
		
		$buffer =  $this->_form->textarea($this->_name, $value, $options);
		
		return $buffer;
	}
}
