<?php
/**
 * @file class.TimeWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TimeWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campi di tipo orario nei form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TimeWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
	
		parent::printInputForm($form, $options);
		
		$seconds = array_key_exists('seconds', $options) ? $options['seconds'] : false;
		if($seconds)
		{
			$size = 9;
			$maxlength = 8;
		}
		else
		{
			$size = 6;
			$maxlength = 5;
		}
		$value = dbTimeToTime($this->_value, $seconds);
		$options['size'] = $size;
		$options['maxlength'] = $maxlength;
		
		$value = $this->_form->retvar($this->_name, htmlInput($value));
		
		$buffer = $this->_form->cinput($this->_name, 'text', $value, $this->_label, $options);
		
		return $buffer;
	}
}
