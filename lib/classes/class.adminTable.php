<?php
/**
 * @file class.adminTable.php
 * @brief Contiene la classe adminTable
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Genera i form
 * 
 * Fornisce gli strumenti per costruire la struttura del form
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Tabella delle associazioni tipo di campo -> tipo di input
 * 
 * <table>
 * <tr><th>Campo</th><th>Metodo</th><th>Input (d: default)</th></tr>
 * <tr><td>INTEGER (TINYINT, SMALLINT, MEDIUMINT, INT)</td><td>integerField()</td><td>testo (d), campo nascosto, select, radio, checkbox, checkbox multiplo</td></tr>
 * <tr><td>CHAR (CHAR, VARCHAR)</td><td>charField()</td><td>testo (d), campo nascosto, textarea</td></tr>
 * <tr><td>TEXT</td><td>textField()</td><td>textarea (d), testo, editor html</td></tr>
 * <tr><td>ENUM</td><td>enumField()</td><td>radio (d), select</td></tr>
 * <tr><td>FILE</td><td>fileField()</td><td>testo di tipo file</td></tr>
 * <tr><td>FOREIGN_KEY</td><td>foreignKeyField()</td><td>radio (d), select</td></tr>
 * <tr><td>DATE</td><td>dateField()</td><td>testo in formato data</td></tr>
 * <tr><td>TIME</td><td>timeField()</td><td>testo in formato ora</td></tr>
 * <tr><td>DATETIME</td><td>datetimeField()</td><td>nessun input (d), campo nascosto, testo in formato datetime</td></tr>
 * </table>
 */
class adminTable {

	private $_db, $_form;
	private $_structure, $_hidden;

	function __construct($structure='') {

		$this->_db = db::instance();
		$this->_form = new Form('', '', '');
		$this->_structure = $structure;
	}
	
	/**
	 * Carica i valori dei campi hidden del form
	 * 
	 * @param array $hidden array delle accoppiate nome-valore dei campi hidden non impostati automaticamente 
	 */
	public function hidden($hidden=array()) {

		$this->_hidden = $hidden;
	}
	
	/**
	 * Imposta la query di selezione dei dati di una chiave esterna
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b table (string): nome della tabella dei dati
	 *   - @b id (string): nome del campo delle chiavi (default: id)
	 *   - @b field (string): nome del campo dei valori
	 *   - @b where (mixed): condizioni della query
	 *     - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
	 *     - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
	 *   - @b order (string): ordinamento dei valori (es. name ASC)
	 * @return string
	 */
	private function foreignKey($options=array()) {
		
		$table = array_key_exists('table', $options) ? $options['table'] : null;
		$id = array_key_exists('id', $options) ? $options['id'] : 'id';
		$field = array_key_exists('field', $options) ? $options['field'] : null;
		$where = array_key_exists('where', $options) ? $options['where'] : '';
		$order = array_key_exists('order', $options) ? $options['order'] : '';
		
		if(sizeof($options) == 0 || is_null($table) || is_null($field))
			return null;
		
		if(is_array($where) && count($where))
		{
			$where = implode(" AND ", $where);
		}
		
		if($where) $where = "WHERE $where";
		if($order) $order = "ORDER BY $order";
		
		$query = "SELECT $id, $field FROM $table $where $order";
		return $query;
	}
	
	/**
	 * Costruisce il form
	 * 
	 * @see Form::__construct()
	 * @see Form::form()
	 * @see Form::load()
	 * @see Form::hidden()
	 * @see Form::cinput()
	 * @param array $options
	 *   array associativo di opzioni
	 *     1. opzioni del costruttore della classe Form
	 *       - @b formId (string)
	 *       - @b method (string)
	 *       - @b validation (boolean)
	 *       - @b trnsl_table (string)
	 *       - @b trnsl_id (string)
	 *       - @b verifyToken (boolean)
	 *       - @b tblLayout (boolean)
	 *       - @b form_label_width (string)
	 *       - @b form_field_width (string)
	 *     2. opzioni del metodo load
	 *       - @b session_value (string)
	 *     3. opzioni del metodo form
	 *       - @b f_action (string)
	 *       - @b f_upload (boolean)
	 *       - @b f_required (string)
	 *       - @b f_func_confirm (string)
	 *       - @b f_text_confirm (string)
	 *       - @b f_generateToken (boolean)
	 *     4. opzioni del metodo hidden
	 *       - @b id (string)
	 *       - @b value (mixed)
	 *     5. opzioni del metodo cinput (type=submit)
	 *       - @b s_name (string): nome del submit
	 *       - @b s_value (string): valore del submit
	 *       - @b s_classField (string): nome della classe del tag input
	 * @return string
	 */
	public function makeForm($options=array()) {
		
		if(!$this->_structure) return null;
		
		$formId = array_key_exists('formId', $options) ? $options['formId'] : '';
		$method = array_key_exists('method', $options) ? $options['method'] : 'post';
		$validation = array_key_exists('validation', $options) ? $options['validation'] : false;
		$trnsl_table = array_key_exists('trnsl_table', $options) ? $options['trnsl_table'] : '';
		$trnsl_id = array_key_exists('trnsl_id', $options) ? $options['trnsl_id'] : 0;
		$verifyToken = array_key_exists('verifyToken', $options) ? $options['verifyToken'] : false;
		$tblLayout = array_key_exists('tblLayout', $options) ? $options['tblLayout'] : true;
		$form_label_width = array_key_exists('form_label_width', $options) ? $options['form_label_width'] : '';
		$form_field_width = array_key_exists('form_field_width', $options) ? $options['form_field_width'] : '';
		
		$session_value = array_key_exists('session_value', $options) ? $options['session_value'] : '';
		
		$f_action = array_key_exists('f_action', $options) ? $options['f_action'] : '';
		$f_upload = array_key_exists('f_upload', $options) ? $options['f_upload'] : false;
		$f_required = array_key_exists('f_required', $options) ? $options['f_required'] : '';
		$f_func_confirm = array_key_exists('f_func_confirm', $options) ? $options['f_func_confirm'] : '';
		$f_text_confirm = array_key_exists('f_text_confirm', $options) ? $options['f_text_confirm'] : '';
		$f_generateToken = array_key_exists('f_generateToken', $options) ? $options['f_generateToken'] : false;
		
		$s_name = array_key_exists('s_name', $options) ? $options['s_name'] : 'submit';
		$s_value = array_key_exists('s_value', $options) ? $options['s_value'] : 'submit';
		$s_classField = array_key_exists('s_classField', $options) ? $options['s_classField'] : 'submit';
		
		$buffer = '';
		
		$gform = new Form($formId, $method, $validation, 
			array(
				"trnsl_table"=>$trnsl_table,
				"trnsl_id"=>$trnsl_id,
				"verifyToken"=>$verifyToken,
				"tblLayout"=>$tblLayout,
				"form_label_width"=>$form_label_width,
				"form_field_width"=>$form_field_width
			)
		);
		$gform->load($session_value);
		$buffer .= $gform->form($f_action, $f_upload, $f_required, 
			array(
				'func_confirm'=>$f_func_confirm,
				'text_confirm'=>$f_text_confirm,
				'generateToken'=>$f_generateToken
			)
		);
		
		if(sizeof($this->_hidden) > 0)
		{
			foreach($this->_hidden AS $key=>$value)
			{
				if(is_array($value))
				{
					$h_value = array_key_exists('value', $options) ? $options['value'] : '';
					$h_id = array_key_exists('id', $options) ? $options['id'] : '';
					$buffer .= $gform->hidden($key, $h_value, array('id'=>$h_id));
				}
				else $buffer .= $gform->hidden($key, $value);
			}
		}
		
		if(sizeof($this->_structure) > 0)
		{
			foreach($this->_structure AS $value)
			{
				$buffer .= $value;
			}
		}
		$buffer .= $gform->cinput($s_name, 'submit', $s_value, '', array("classField"=>$s_classField));
		
		$buffer .= $gform->cform();
		
		return $buffer;
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
	private function inputFormHidden($name, $value, $options) {
		
		return $this->_form->hidden($name, htmlInput($value), $options);
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
	private function inputFormText($name, $value, $label, $options) {
		
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
	private function inputFormFile($name, $value, $label, $options) {
		
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
	private function inputFormTextarea($name, $value, $label, $options) {
		
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
	private function inputFormSelect($name, $value, $data, $label, $options) {
		
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
	private function inputFormRadio($name, $value, $data, $default, $label, $options) {
		
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
	private function inputFormDate($name, $value, $label, $options=array()) {
		
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
	private function inputFormCheckbox($name, $checked, $value, $label, $options=array()) {
		
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
	private function inputFormMulticheck($name, $value, $data, $label, $options=array()) {
		
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
	private function inputFormEditor($name, $value, $label, $options=array()) {
		
		$value = $this->_form->retvar($name, htmlInputEditor($value));
		
		return $this->_form->fcktextarea($name, $value, $label, $options);
	}
	
	/**
	 * Predisposizione dei parametri di default
	 * 
	 * @param array $options
	 * @return array
	 */
	private function setParams($options) {
		
		$name = array_key_exists('name', $options) ? $options['name'] : '';
		$lenght = array_key_exists('lenght', $options) ? $options['lenght'] : 11;
		$auto_increment = array_key_exists('auto_increment', $options) ? $options['auto_increment'] : false;
		$primary_key = array_key_exists('primary_key', $options) ? $options['primary_key'] : false;
		
		if(!$name) return null;
		
		$foreign_key = array_key_exists('foreign_key', $options) ? $options['foreign_key'] : array();
		$input = array_key_exists('input', $options) ? $options['input'] : array();
		
		if(sizeof($input) == 0) return null;
		
		$i_name = array_key_exists('name', $input) ? $input['name'] : $name;
		$i_value = array_key_exists('value', $input) ? $input['value'] : '';
		$i_label = array_key_exists('label', $input) ? $input['label'] : '';
		$i_type = array_key_exists('type', $input) ? $input['type'] : '';
		$i_default = array_key_exists('default', $input) ? $input['default'] : null;
		
		return array(
			'name'=>$name, 
			'lenght'=>$lenght, 
			'auto_increment'=>$auto_increment, 
			'primary_key'=>$primary_key, 
			'foreign_key'=>$foreign_key, 
			'input'=>$input, 
			'i_name'=>$i_name, 
		 	'i_value'=>$i_value, 
		 	'i_label'=>$i_label, 
		 	'i_type'=>$i_type , 
			'i_default'=>$i_default
		);
	}
	
	/**
	 * Campi speciali
	 * 
	 * @param array $options
	 *   - opzioni del campo del db
	 *   - opzioni dell'elemento del form
	 *   - opzioni aggiuntive
	 * @return string
	 */
	public function specialField($options=array()) {
		
		$params = $this->setParams($options);
		
		return null;
	}
	
	/**
	 * Campo di tipo intero (TINYINT, SMALLINT, MEDIUMINT, INT)
	 * 
	 * Tipologie di input associabili: testo (default), campo nascosto, select, radio, checkbox, checkbox multiplo
	 * 
	 * @see inputFormText()
	 * @see inputFormHidden()
	 * @see inputFormSelect()
	 * @see inputFormRadio()
	 * @see inputFormCheckbox()
	 * @see inputFormMulticheck()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function integerField($options=array()) {
		
		$params = $this->setParams($options);
		
		$buffer = '';
		
		if($params['i_type'] == 'hidden')
		{
			$buffer .= $this->inputFormHidden($params['i_name'], $params['i_value'], $params['input']);
		}
		elseif($params['i_type'] == 'select')
		{
			$i_enum = array_key_exists('enum', $params['input']) ? $params['input']['enum'] : array();
			$buffer .= $this->inputFormSelect($params['i_name'], $params['i_value'], $i_enum, $params['i_label'], $params['input']);
		}
		elseif($params['i_type'] == 'radio')
		{
			$i_enum = array_key_exists('enum', $params['input']) ? $params['input']['enum'] : array();
			$buffer .= $this->inputFormRadio($params['i_name'], $params['i_value'], $i_enum, $params['i_default'], $params['i_label'], $params['input']);
		}
		elseif($params['i_type'] == 'checkbox')
		{
			$i_checked = array_key_exists('checked', $params['input']) ? $params['input']['checked'] : array();
			$buffer .= $this->inputFormCheckbox($params['i_name'], $i_checked, $params['i_value'], $params['i_label'], $params['input']);
		}
		elseif($params['i_type'] == 'multicheck')
		{
			$i_enum = array_key_exists('enum', $params['input']) ? $params['input']['enum'] : array();
			$buffer .= $this->inputFormMulticheck($params['i_name'], $params['i_value'], $i_enum, $params['i_label'], $params['input']);
		}
		else
		{
			$buffer .= $this->inputFormText($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
		}
		
		return $buffer;
	}
	
	/**
	 * Campo di tipo CHAR (CHAR, VARCHAR)
	 * 
	 * Tipologie di input associabili: testo (default), campo nascosto, textarea
	 * 
	 * @see inputFormText()
	 * @see inputFormHidden()
	 * @see inputFormTextarea()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function charField($options=array()) {
		
		$params = $this->setParams($options);
		
		$buffer = '';
		
		if($params['i_type'] == 'hidden')
		{
			$buffer .= $this->inputFormHidden($params['i_name'], $params['i_value'], $params['input']);
		}
		elseif($params['i_type'] == 'textarea')
		{
			$buffer .= $this->inputFormTextarea($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
		}
		else
		{
			$buffer .= $this->inputFormText($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
		}
		
		return $buffer;
	}
	
	/**
	 * Campo di tipo TEXT
	 * 
	 * Tipologie di input associabili: textarea (default), testo, editor html
	 * 
	 * @see inputFormTextarea()
	 * @see inputFormEditor()
	 * @see inputFormText()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function textField($options=array()) {
		
		$params = $this->setParams($options);
		
		$buffer = '';
		
		if($params['i_type'] == 'editor')
		{
			$buffer .= $this->inputFormEditor($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
		}
		elseif($params['i_type'] == 'text')
		{
			$buffer .= $this->inputFormText($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
		}
		else
		{
			$buffer .= $this->inputFormTextarea($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
		}
		
		return $buffer;
	}
	
	/**
	 * Campo di tipo FILE (estensione)
	 * 
	 * Tipologie di input associabili: testo di tipo file
	 * 
	 * @see inputFormFile()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function fileField($options=array()) {
		
		$params = $this->setParams($options);
		
		return $this->inputFormFile($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
	}
	
	/**
	 * Campo di tipo FOREIGN_KEY (estensione)
	 * 
	 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere impostati nella chiave 'foreign_key'. \n
	 * Tipologie di input associabili: radio (default), select
	 * 
	 * @see foreignKey()
	 * @see inputFormRadio()
	 * @see inputFormSelect()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function foreignKeyField($options=array()) {
		
		$params = $this->setParams($options);
		
		if(!$this->foreignKey($params['foreign_key']))
			return null;
		else
			$data = $this->foreignKey($params['foreign_key']);
		
		$buffer = '';
		
		if($params['i_type'] == 'select')
		{
			$buffer .= $this->inputFormSelect($params['i_name'], $params['i_value'], $data, $params['i_label'], $params['input']);
		}
		else
		{
			$buffer .= $this->inputFormRadio($params['i_name'], $params['i_value'], $data, $params['i_default'], $params['i_label'], $params['input']);
		}
		
		return $buffer;
	}
	
	/**
	 * Campo di tipo ENUM
	 * 
	 * Tipologie di input associabili: radio (default), select
	 * 
	 * @see inputFormRadio()
	 * @see inputFormSelect()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function enumField($options=array()) {
		
		$params = $this->setParams($options);
		$i_enum = array_key_exists('enum', $params['input']) ? $params['input']['enum'] : array();
		
		$buffer = '';
		if($params['i_type'] == 'select')
		{
			$buffer .= $this->inputFormSelect($params['i_name'], $params['i_value'], $i_enum, $params['i_label'], $params['input']);
		}
		else
		{
			$buffer .= $this->inputFormRadio($params['i_name'], $params['i_value'], $i_enum, $params['i_default'], $params['i_label'], $params['input']);
		}
		
		return $buffer;
	}
	
	/**
	 * Campo di tipo DATE
	 * 
	 * Tipologie di input associabili: testo in formato data
	 * 
	 * @see inputFormDate()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function dateField($options=array()) {
		
		$params = $this->setParams($options);
		
		return $this->inputFormDate($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
	}
	
	/**
	 * Campo di tipo TIME
	 * 
	 * Tipologie di input associabili: testo in formato ora. \n
	 * L'orario può essere mostrato con o senza i secondi utilizzando la chiave @a seconds.
	 * 
	 * @see inputFormText()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function timeField($options=array()) {
		
		$params = $this->setParams($options);
		$i_seconds = array_key_exists('seconds', $params['input']) ? $params['input']['seconds'] : false;
		
		if($i_seconds)
		{
			$size = 9;
			$maxlength = 8;
		}
		else
		{
			$size = 6;
			$maxlength = 5;
		}
		$value = dbTimeToTime($params['i_value'], $i_seconds);
		$params['input']['size'] = $size;
		$params['input']['maxlength'] = $maxlength;
		
		return $this->inputFormText($params['i_name'], $value, $params['i_label'], $params['input']);
	}
	
	/**
	 * Campo di tipo DATETIME
	 * 
	 * Tipologie di input associabili: nessun input (d), testo nascosto, testo in formato datetime (YYYY-MM-DD HH:MM:SS)
	 * 
	 * @see inputFormHidden()
	 * @see inputFormText()
	 * @param array $options opzioni del campo del db e dell'elemento del form
	 * @return string
	 */
	public function datetimeField($options=array()) {
		
		$params = $this->setParams($options);
		
		$buffer = '';
		if($params['i_type'] == 'hidden')
		{
			$buffer .= $this->inputFormHidden($params['i_name'], $params['i_value'], $params['input']);
		}
		elseif($params['i_type'] == 'text')
		{
			$params['input']['size'] = 20;
			$params['input']['maxlength'] = 19;
			$buffer .= $this->inputFormText($params['i_name'], $params['i_value'], $params['i_label'], $params['input']);
		}
		
		return $buffer;
	}
}
?>
