<?php
/**
 * @file class.inputForm.php
 * @brief Contiene la classe inputForm
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Interfaccia ai metodi che che generano gli elementi input nella classe Form()
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class inputForm {

	private $_form;

	/**
	 * Costruttore
	 * 
	 * @param object $form oggetto form
	 * @return void
	 */
	function __construct($form) {

		$this->_form = $form;
	}
	
	/**
	 * Form: campo nascosto
	 * 
	 * @see Form::hidden()
	 * @param string $name
	 * @param mixed $value
	 * @param array $options opzioni del metodo hidden() della classe Form
	 * @return string
	 */
	public function hidden($name, $value, $options) {
		
		return $this->_form->hidden($name, htmlInput($value), $options);
	}
	
	/**
	 * Form: mostra etichetta e valore al di fuori di un input
	 * 
	 * @see Form::noinput()
	 * @param string $label
	 * @param mixed $value
	 * @param array $options opzioni del metodo noinput() della classe Form
	 * @return string
	 */
	public function noinput($label, $value, $options) {
		
		return $this->_form->noinput($label, htmlChars($value), $options);
	}
	
	/**
	 * Form: campo testo
	 * 
	 * @see Form::input()
	 * @see Form::cinput()
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $label
	 * @param array $options opzioni dei metodi input() e cinput() della classe Form
	 * @return string
	 */
	public function text($name, $value, $label, $options) {
		
		$value = $this->_form->retvar($name, htmlInput($value));
		
		return $this->_form->cinput($name, 'text', $value, $label, $options);
	}
	
	/**
	 * Form: caricamento file
	 * 
	 * @see Form::input()
	 * @see Form::cfile()
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $label
	 * @param array $options opzioni dei metodi input() e cfile() della classe Form
	 * @return string
	 */
	public function file($name, $value, $label, $options) {
		
		return $this->_form->cfile($name, htmlInput($value), $label, $options);
	}
	
	/**
	 * Form: textarea
	 * 
	 * @see Form::textarea()
	 * @see Form::ctextarea()
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $label
	 * @param array $options opzioni dei metodi textarea() e ctextarea() della classe Form
	 * @return string
	 */
	public function textarea($name, $value, $label, $options) {
		
		$value = $this->_form->retvar($name, htmlInput($value));
		
		return $this->_form->ctextarea($name, $value, $label, $options);
	}
	
	/**
	 * Form: select
	 * 
	 * @see Form::select()
	 * @see Form::cselect()
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $data
	 *   - string, query della FOREIGN_KEY
	 *   - array, insieme di elementi (chiave=>valore) da utilizzare per popolare l'input (associato alla chiave @a enum)
	 * @param mixed $label
	 * @param array $options opzioni dei metodi select() e cselect() della classe Form
	 * @return string
	 */
	public function select($name, $value, $data, $label, $options) {
		
		return $this->_form->cselect($name, $value, $data, $label, $options);
	}
	
	/**
	 * Form: radio button
	 * 
	 * @see Form::radio()
	 * @see Form::cradio()
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $data
	 *   - string, query della FOREIGN_KEY
	 *   - array, insieme di elementi (chiave=>valore) da utilizzare per popolare l'input (associato alla chiave @a enum)
	 * @param mixed $default
	 * @param mixed $label
	 * @param array $options opzioni dei metodi radio() e cradio() della classe Form
	 * @return string
	 */
	public function radio($name, $value, $data, $default, $label, $options) {
		
		$value = $this->_form->retvar($name, htmlInput($value));
		
		return $this->_form->cradio($name, $value, $data, $default, $label, $options);
	}
	
	/**
	 * Form: campo testo in formato data con calendario
	 * 
	 * @see Form::cinput_date()
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $label
	 * @param array $options opzioni dei metodi cinput_date() e input() della classe Form
	 * @return string
	 */
	public function date($name, $value, $label, $options=array()) {
		
		$value = $this->_form->retvar($name, htmlInput(dbDateToDate($value, "/")));
		
		return $this->_form->cinput_date($name, $value, $label, $options);
	}
	
	/**
	 * Form: checkbox
	 * 
	 * @param string $name
	 * @param boolean $checked valore selezionato (associato alla chiave @a checked)
	 * @param mixed $value
	 * @param string $label
	 * @param array $options opzioni dei metodi ccheckbox() e checkbox() della classe Form
	 * @return string
	 */
	public function checkbox($name, $checked, $value, $label, $options=array()) {
		
		return $this->_form->ccheckbox($name, $checked, $value, $label, $options);
	}
	
	/**
	 * Form: checkbox con più elementi
	 * 
	 * @param string $name
	 * @param array $value array dei valori degli elementi selezionati
	 * @param mixed $data
	 *   - string, query della FOREIGN_KEY
	 *   - array, insieme di elementi (chiave=>valore) da utilizzare per popolare l'input (associato alla chiave @a enum)
	 * @param string $label
	 * @param array $options opzioni del metodo multipleCheckbox() della classe Form
	 * @return string
	 */
	public function multicheck($name, $value, $data, $label, $options=array()) {
		
		return $this->_form->multipleCheckbox($name, $value, $data, $label, $options);
	}
	
	/**
	 * Form: editor html
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @param string $label
	 * @param array $options opzioni del metodo fcktextarea() della classe Form
	 * @return string
	 */
	public function editor($name, $value, $label, $options=array()) {
		
		$value = $this->_form->retvar($name, htmlInputEditor($value));
		
		return $this->_form->fcktextarea($name, $value, $label, $options);
	}
}
?>
