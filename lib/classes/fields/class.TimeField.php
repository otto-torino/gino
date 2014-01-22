<?php
/**
 * @file class.timeField.php
 * @brief Contiene la classe timeField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo TIME
 * 
 * Tipologie di input associabili: testo in formato ora. \n
 * L'orario può essere mostrato con o senza i secondi utilizzando la chiave @a seconds.
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class timeField extends field {

	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'time';
		$this->_value_type = 'string';
	}
	
	function __toString() {
		
		return (string) $this->_value;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 *   - opzioni dei metodi input() e cinput() della classe Form
	 *   - @b seconds (boolean): mostra i secondi
	 * @return string
	 */
	public function formElement($form, $options) {
		
		return parent::formElement($form, $options);
	}
	
	/**
	 * Formatta un elemento input di tipo @a time per l'inserimento in database
	 * 
	 * @see field::clean()
	 */
	public function clean($options=null) {
		
		$value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
		$method = isset($options['method']) ? $options['method'] : $_POST;
		
		return timeToDbTime(cleanVar($method, $this->_name, $value_type, null));
	}
}
?>
