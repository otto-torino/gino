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
 * Tabella delle associazioni del tipo di campo con il tipo input di default
 * 
 * <table>
 * <tr><th>Classe</th><th>Tipo di campo</th><th>Widget</th></tr>
 * <tr><td>integerField()</td><td>TINYINT, SMALLINT, MEDIUMINT, INT</td><td>text</td></tr>
 * <tr><td>charField()</td><td>CHAR, VARCHAR</td><td>text</td></tr>
 * <tr><td>dateField()</td><td>DATE</td><td>date</td></tr>
 * <tr><td>datetimeField()</td><td>DATETIME</td><td>null</td></tr>
 * <tr><td>enumField()</td><td>ENUM</td><td>radio</td></tr>
 * <tr><td>fileField()</td><td>FILE</td><td>file</td></tr>
 * <tr><td>floatField()</td><td>FLOAT, DOUBLE, DECIMAL</td><td>float</td></tr>
 * <tr><td>foreignKeyField()</td><td>FOREIGN_KEY</td><td>select</td></tr>
 * <tr><td>hiddenField()</td><td>HIDDEN</td><td>hidden</td></tr>
 * <tr><td>imageField()</td><td>IMAGE</td><td>image</td></tr>
 * <tr><td>textField()</td><td>TEXT</td><td>textarea</td></tr>
 * <tr><td>timeField()</td><td>TIME</td><td>time</td></tr>
 * </table>
 */
class adminTable {

	private $_db, $_form;
	private $_structure, $_hidden;
	
	/**
	 * Costruttore
	 */
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
	 * Stampa il form
	 * 
	 * Cicla sulla struttura di model e per ogni field costruisce l'elemento del form. \n
	 * L'elemento del form di base lo prende da $model->structure[nome_campo]->formElement(opzioni);
	 * 
	 * @param object $model oggetto dell'elemento (record da processare)
	 * @param array $options
	 *   - opzioni del form
	 *   - opzioni per modificare la struttura del form
	 *     - @b removeFields (array): elenco dei campi da non mostrare nel form
	 *     - @b viewFields (array): elenco dei campi da mostrare nel form
	 *     - @b addCell (array): elementi html da mostrare in aggiunta nel form
	 * @param array $inputs opzioni degli elementi input che vengono passate al metodo formElement()
	 *   opzioni valide
	 *   - opzioni dei metodi della classe Form()
	 *   - @b widget (string): tipo di input
	 *   - @b enum (mixed): recupera gli elementi che popolano gli input radio, select, multicheck
	 *     - @a string, query per recuperare gli elementi (select di due campi)
	 *     - @a array, elenco degli elementi (key=>value)
	 *   - @b seconds (boolean): mostra i secondi
	 *   - @b default (mixed): valore di default (input radio)
	 *   - @b checked (boolean): valore selezionato (input checkbox)
	 * @return string
	 * 
	 * Nella costruzione del form vengono impostati i seguenti parametri di default:
	 * - formId, valore generato
	 * - method, post
	 * - validation, true
	 * - trnsl_table, il nome della tabella viene recuperato dal modello
	 * - trnsl_id, il valore del record id viene recuperato dal modello
	 * - session_value, valore generato
	 * - upload, viene impostato a true se l'oggetto di un campo del form appartiene almeno a una classe fileField() o imageField()
	 * - required, l'elenco dei campi obbigatori viene costruito controllando il valore della proprietà @a $_required dell'oggetto del campo
	 * - s_name, il nome del submit è 'submit'
	 * - s_value, il valore del submit è 'modifica' o 'inserisci'
	 * 
	 * Le opzioni degli elementi input sono formattate nel seguente modo: nome_campo=>array(opzione=>valore[,...]) \n
	 * E' possibile rimuovere gli elementi input dalla struttura del form (@a removeFields) oppure selezionare gli elementi da mostrare (@a viewFields). \n
	 * E' inoltre possibile aggiungere degli elementi input all'interno della struttura del form indicando come chiave il nome del campo prima del quale inserire ogni elemento (@a addCell).
	 * 
	 * Esempio:
	 * @code
	 * $gform = new Form('', '', '');
	 * $addCell = array(
	 *   'lng'=>$gform->cinput('map_address', 'text', '', array(_("Indirizzo evento"), _("es: torino, via mazzini 37)), array("size"=>40, "maxlength"=>200, "id"=>"map_address"))
	 *   .$gform->cinput('map_coord', 'button', _("converti"), '', array("id"=>"map_coord", "classField"=>"generic", "js"=>$onclick))
	 * );
	 * 
	 * $removeFields = array('instance');
	 * if(!$manage_ctg) $removeFields[] = 'ctg';
	 * 
	 * $options_form = array(
	 * 'formId'=>'eform',
	 * 'f_action'=>$this->_home."?evt[$this->_instanceName-actionEvent]",
	 * 's_name'=>'submit_action', 
	 * 'removeFields'=>$removeFields, 
	 * 'viewFields'=>null, 
	 * 'addCell'=>$addCell
	 * );
	 * 
	 * $admin_table = new adminTable();
	 * $admin_table->hidden = $hidden;
	 * $form = $admin_table->modelForm(
	 *   $model, 
	 *   $options_form, 
	 *   array(
	 *     'ctg'=>array('required'=>true), 
	 *     'name'=>array('required'=>false), 
	 *     'lat'=>array('id'=>'lat', 'trnsl'=>false), 
	 *     'lng'=>array('id'=>'lng', 'trnsl'=>false), 
	 *     'image'=>array('preview'=>true),  
	 *     'description'=>array(
	 *       'widget'=>'editor', 
	 *       'notes'=>false, 
	 *       'img_preview'=>true, 
	 *       'fck_toolbar'=>self::$_fck_toolbar, 
	 *       'fck_height'=>100), 
	 *     'summary'=>array(
	 *       'maxlength'=>$maxlength_summary, 
	 *       'id'=>'summary', 
	 *       'rows'=>6, 
	 *       'cols'=>55)
	 *   )
	 * );
	 * @endcode
	 */
	public function modelForm($model, $options=array(), $inputs=array()) {
		
		// TODO: se POST action
		
		// Valori di default di form e sessione
		$default_formid = 'form'.$model->getTable().$model->id;
		$default_session = 'dataform'.$model->getTable().$model->id;
		
		// Opzioni generali form
		$formId = array_key_exists('formId', $options) ? $options['formId'] : $default_formid;
		$method = array_key_exists('method', $options) ? $options['method'] : 'post';
		$validation = array_key_exists('validation', $options) ? $options['validation'] : true;
		$trnsl_table = array_key_exists('trnsl_table', $options) ? $options['trnsl_table'] : $model->getTable();
		$trnsl_id = array_key_exists('trnsl_id', $options) ? $options['trnsl_id'] : $model->id;
		$verifyToken = array_key_exists('verifyToken', $options) ? $options['verifyToken'] : false;
		$tblLayout = array_key_exists('tblLayout', $options) ? $options['tblLayout'] : true;
		$form_label_width = array_key_exists('form_label_width', $options) ? $options['form_label_width'] : '';
		$form_field_width = array_key_exists('form_field_width', $options) ? $options['form_field_width'] : '';
		
		$session_value = array_key_exists('session_value', $options) ? $options['session_value'] : $default_session;
		
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
		
		// Opzioni per la modifica della struttura del form
		$removeFields = array_key_exists('removeFields', $options) ? $options['removeFields'] : null;
		$viewFields = array_key_exists('viewFields', $options) ? $options['viewFields'] : null;
		$addCell = array_key_exists('addCell', $options) ? $options['addCell'] : null;
		
		$structure = '';
		$form_upload = false;
		$form_required = array();
		foreach($model->structure($model->id) as $field=>$object) {
			
			if($addCell)
			{
				foreach($addCell AS $ref_key=>$addvalue)
				{
					if($ref_key == $field)
					{
						$structure .= $addvalue;
					}
				}
			}
			
			if(($removeFields && !in_array($field, $removeFields)) || ($viewFields && in_array($field, $viewFields)))
			{
				if(isset($inputs[$field]))
					$options_input = $inputs[$field];
				else 
					$options_input = array();
				
				$structure .= $object->formElement($gform, $options_input);
				
				$name_class = get_class($object);
				if($name_class == 'fileField' || $name_class == 'imageField')
					$form_upload = true;
				
				if($object->getRequired() == true)
					$form_required[] = $field;
			}
		}
		if(sizeof($form_required) > 0)
			$form_required = implode(',', $form_required);
		
		if($model->id) {
			
			$submit = _("modifica");
		}
		else {
			
			$submit = _("inserisci");
		}
		
		$f_action = array_key_exists('f_action', $options) ? $options['f_action'] : '';
		$f_upload = array_key_exists('f_upload', $options) ? $options['f_upload'] : $form_upload;
		$f_required = array_key_exists('f_required', $options) ? $options['f_required'] : $form_required;
		$f_func_confirm = array_key_exists('f_func_confirm', $options) ? $options['f_func_confirm'] : '';
		$f_text_confirm = array_key_exists('f_text_confirm', $options) ? $options['f_text_confirm'] : '';
		$f_generateToken = array_key_exists('f_generateToken', $options) ? $options['f_generateToken'] : false;
		
		$s_name = array_key_exists('s_name', $options) ? $options['s_name'] : 'submit';
		$s_value = array_key_exists('s_value', $options) ? $options['s_value'] : $submit;
		$s_classField = array_key_exists('s_classField', $options) ? $options['s_classField'] : 'submit';
		
		$buffer = '';
		
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
		
		$buffer .= $structure;
		
		$buffer .= $gform->cinput($s_name, 'submit', $s_value, '', array("classField"=>$s_classField));
		$buffer .= $gform->cform();
		
		return $buffer;
	}
	
	/**
	 * Gestisce l'azione del form
	 * 
	 * @see propertyObject::updateDbData()
	 * @param object $model
	 * @param array $options
	 * @param array $options_element
	 * @return void
	 * 
	 * Esempio:
	 * @code
	 * $id = cleanVar($_POST, 'id', 'int', '');
	 * $start = cleanVar($_POST, 'start', 'int', '');
	 * 
	 * $item = new eventItem($id, $this);
	 * 
	 * $link_error = $this->_home."?evt[$this->_instanceName-manageDoc]&action={$this->_action}&start=$start";
	 * if($id) $link_error .= "&id=$id";
	 * 
	 * $options_form = array(
	 *   'link_error'=>$link_error, 
	 *   'removeFields'=>null
	 * );
	 * $options_element = array();
	 * 
	 * $admin_table = new adminTable();
	 * $admin_table->modelAction($item, $options_form, $options_element);
	 * @endcode
	 */
	public function modelAction($model, $options=array(), $options_element=array()) {
		
		// Valori di default di form e sessione
		$default_formid = 'form'.$model->getTable().$model->id;
		$default_session = 'dataform'.$model->getTable().$model->id;
		
		// Opzioni generali form
		$formId = array_key_exists('formId', $options) ? $options['formId'] : $default_formid;
		$method = array_key_exists('method', $options) ? $options['method'] : 'post';
		$validation = array_key_exists('validation', $options) ? $options['validation'] : true;
		$session_value = array_key_exists('session_value', $options) ? $options['session_value'] : $default_session;
		$link_error = array_key_exists('link_error', $options) ? $options['link_error'] : null;
		
		// Opzioni per la modifica della struttura del form
		$removeFields = array_key_exists('removeFields', $options) ? $options['removeFields'] : null;
		$viewFields = array_key_exists('viewFields', $options) ? $options['viewFields'] : null;
		$addCell = array_key_exists('addCell', $options) ? $options['addCell'] : null;
		
		$gform = new Form($formId, $method, $validation);
		$gform->save($session_value);
		$req_error = $gform->arequired();
		
		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		foreach($model->getStructure() as $field=>$object) {
			
			if(($removeFields && !in_array($field, $removeFields)) || ($viewFields && in_array($field, $viewFields)) || (!$removeFields && !$viewFields))
			{
				if(isset($options_element[$field]))
					$options_element = $options_element[$field];
				else 
					$options_element = array();
				
				$value = $object->clean($options_element);
				$result = $object->validate($value);
				if($result === true) {
					$model->{$field} = $value;
				}
				else {
					exit(error::errorMessage(array('error'=>$result['error']), $link_error));
				}
				
			}
		}
		
		$model->updateDbData();
	}
}
?>
