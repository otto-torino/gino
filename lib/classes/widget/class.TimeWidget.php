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
	 * @see Gino.Widget::inputValue()
	 */
	public function inputValue($value, $options=array()) {
		 
		$seconds = array_key_exists('seconds', $options) ? $options['seconds'] : false;
		
		$value = dbTimeToTime($value, $seconds);
		
		return $value;
	}
	
	/**
	 * @see Gino.Widget::printInputForm()
	 * 
	 * @param array $options opzioni dell'elemento del form
     *   - opzioni dei metodi input() e input_label() della classe Gino.Input
     *   - @b seconds (boolean): per mostrare i secondi
	 */
	public function printInputForm($options) {
	
		parent::printInputForm($options);
		
		$seconds = array_key_exists('seconds', $options) ? $options['seconds'] : false;
		if($seconds) {
			$size = 9;
			$maxlength = 8;
		}
		else {
			$size = 6;
			$maxlength = 5;
		}
		$options['size'] = $size;
		$options['maxlength'] = $maxlength;
		
		$buffer = Input::input_label($this->_name, 'text', $this->_value_retrieve, $this->_label, $options);
		
		return $buffer;
	}
}
