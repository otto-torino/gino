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
 * @brief Gestisce i form e il back-office di un modello
 * 
 * Fornisce gli strumenti per gestire la parte amministrativa di un modulo, mostrando gli elementi e interagendo con loro (inserimento, modifica, eliminazione). \n
 * Nel metodo backOffice() viene ricercato automaticamente il parametro 'id' come identificatore del record sul quale interagire. Non utilizzare il parametro 'id' per altri riferimenti.
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Il campo di nome @a instance viene trattato diversamente dagli altri campi: non compare nel form e il valore gli viene passato direttamente dall'istanza. \n
 * 
 * Per attivare i filtri di ricerca nella pagina di visualizzazione dei record occorre indicare i campi sui quali applicare il filtro nella chiave @a filter_fields (opzioni della vista). \n
 * Nella tabella di visualizzazione dei record i campi sui quali è possibile ordinare i risultati sono quelli per i quali la tipologia è "ordinabile", ovvero il metodo @a canBeOrdered() ritorna il valore @a true. \n  
 * 
 * Per attivare l'importazione dei file utilizzare l'opzione @a import_file come specificato nel metodo modelAction() e sovrascrivere il metodo readFile(). \n
 * 
 * @b "Gestione dei permessi" \n
 * La gestione delle autorizzazioni a operare sulle funzionalità del modulo avviene impostando opportunamente le opzioni @a allow_insertion, @a edit_deny, @a delete_deny quando si istanzia la classe adminTable(). \n
 * Esempio:
 * @code
 * // se gruppo1 $edit_deny = 'all'
 * // se gruppo2 $edit_deny = array(2);
 * // altrimenti $edit_deny = null;
 * $admin_table = new adminTable($this, array('allow_insertion'=>true, 'delete_deny'=>'all'));
 * @endcode
 * 
 * La gestione fine delle autorizzazioni a operare sui singoli campi della tabella avviene indicando i gruppi autorizzati nell'array delle opzioni della funzionalità utilizzando la chiave @a permission. \n
 * Il formato è il seguente:
 * @code
 * $buffer = $admin_table->backOffice('elearningCtg', 
 *   array(
 *     'list_display' => array('id', 'name'),
 *     'add_params_url'=>array('block'=>'ctg')
 *   ), 
 *   array(
 *     'permission'=>array(
 *       'view'=>group, 
 *       'fields'=>array(
 *       'field1'=>group, 
 *       'field2'=>group)
 *     )
 *   )
 * );
 * @endcode
 * dove @a group (mixed) indica il o i gruppi autorizzati a una determinata funzione/campo. \n
 * La chiave @a view contiene il permesso di accedere alla singola funzionalità (view, edit, delete), e per il momento non viene utilizzata. \n
 * 
 * Tabella delle associazioni del tipo di campo con il tipo input di default
 * 
 * <table>
 * <tr><th>Classe</th><th>Tipo di campo</th><th>Widget</th></tr>
 * <tr><td>charField()</td><td>CHAR, VARCHAR</td><td>text</td></tr>
 * <tr><td>dateField()</td><td>DATE</td><td>date</td></tr>
 * <tr><td>datetimeField()</td><td>DATETIME</td><td>null</td></tr>
 * <tr><td>enumField()</td><td>ENUM</td><td>radio</td></tr>
 * <tr><td>fileField()</td><td>FILE</td><td>file</td></tr>
 * <tr><td>floatField()</td><td>FLOAT, DOUBLE, DECIMAL</td><td>float</td></tr>
 * <tr><td>foreignKeyField()</td><td>FOREIGN_KEY</td><td>select</td></tr>
 * <tr><td>hiddenField()</td><td>HIDDEN</td><td>hidden</td></tr>
 * <tr><td>imageField()</td><td>IMAGE</td><td>image</td></tr>
 * <tr><td>integerField()</td><td>TINYINT, SMALLINT, MEDIUMINT, INT</td><td>text</td></tr>
 * <tr><td>textField()</td><td>TEXT</td><td>textarea</td></tr>
 * <tr><td>timeField()</td><td>TIME</td><td>time</td></tr>
 * </table>
 */
class adminTable {

	protected $_controller;
	protected $_db, $session, $_form;
	protected $_view, $_hidden;
	
	protected $_allow_insertion, $_edit_deny, $_delete_deny;

	protected $_filter_fields, $_list_display, $_list_remove;
	protected $_ifp;
	
	/**
	 * Costruttore
	 * 
	 * @param object $instance oggetto dell'istanza
	 * @param array $opts
	 *   array associativo di opzioni
	 *   - @b view_folder (string): percorso della directory contenente la vista da caricare
	 *   - @b allow_insertion (boolean): indica se permettere o meno l'inserimento di nuovi record
	 *   - @b edit_deny (mixed): indica quali sono gli ID dei record che non posssono essere modificati
	 *     - @a string, 'all' -> tutti
	 *     - @a array, elenco ID
	 *   - @b delete_deny (mixed): indica quali sono gli ID dei record che non posssono essere eliminati
	 *     - @a string, 'all' -> tutti
	 *     - @a array, elenco ID
	 */
	function __construct($instance, $opts = array()) {

		$this->_controller = $instance;
		$this->_db = db::instance();
		$this->session = session::instance();
		
		$view_folder = gOpt('view_folder', $opts, null);
		
		$this->_form = new Form('', '', '');
		$this->_view = new view($view_folder);

		$this->_allow_insertion = gOpt('allow_insertion', $opts, true);
		$this->_edit_deny = gOpt('edit_deny', $opts, array());
		$this->_delete_deny = gOpt('delete_deny', $opts, array());
	}
	
	/**
	 * Gestione delle autorizzazioni alle funzionalità di back-office e ai singoli campi
	 * 
	 * @see AbstractEvtClass::getAccessGroup()
	 * @param array $options
	 *   array associativo di opzioni delle diverse funzionalità
	 *   - @b permission (array): raggruppa le autorizzazioni ad accedere a una determinata funzione/campo
	 *     - @b view (mixed): autorizzazione ad accedere a una determinata funzione (funzionalità non attiva)
	 *     - @b fields (array): contiene i riferimenti di accesso ai singoli campi nella forma nome_campo=>autorizzazione (mixed)
	 * @param string $field nome del campo; se vuoto viene controllata l'autorizzazione ad accedere alla funzionalità richiesta
	 * @return redirect or boolean
	 */
	protected function permission($options, $field='') {
		
		$control = array_key_exists('permission', $options) ? $options['permission'] : null;
		
		if($field)
		{
			if(isset($control['fields']) && 
			isset($control['fields'][$field]) && 
			!is_null($control['fields'][$field]))
			{
				if(!$this->_controller->getAccessGroup($control['fields'][$field]))
					return false;
			}
			return true;
		}
		else
		{
			if(isset($control['view']) && !is_null($control['view']))
				$this->_controller->getAccessGroup($control['view'], false);
		}
		return true;
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
	 * @see permission()
	 * @param object $model oggetto dell'elemento (record da processare)
	 * @param array $options
	 *   - opzioni del form
	 *   - opzioni per modificare la struttura del form
	 *     - @b removeFields (array): elenco dei campi da non mostrare nel form
	 *     - @b viewFields (array): elenco dei campi da mostrare nel form
	 *     - @b addCell (array): elementi html da mostrare in aggiunta nel form
	 *   - @b permission (array): raggruppa le autorizzazioni ad accedere a una determinata funzione/campo
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
	 * - @b formId, valore generato
	 * - @b method, post
	 * - @b validation, true
	 * - @b trnsl_table, il nome della tabella viene recuperato dal modello
	 * - @b trnsl_id, il valore del record id viene recuperato dal modello
	 * - @b session_value, valore generato
	 * - @b upload, viene impostato a true se l'oggetto di un campo del form appartiene almeno a una classe fileField() o imageField()
	 * - @b required, l'elenco dei campi obbigatori viene costruito controllando il valore della proprietà @a $_required dell'oggetto del campo
	 * - @b s_name, il nome del submit è 'submit'
	 * - @b s_value, il valore del submit è 'modifica' o 'inserisci'
	 * 
	 * Le opzioni degli elementi input sono formattate nel seguente modo: nome_campo=>array(opzione=>valore[,...]) \n
	 * E' possibile rimuovere gli elementi input dalla struttura del form (@a removeFields) oppure selezionare gli elementi da mostrare (@a viewFields). \n
	 * E' inoltre possibile aggiungere degli elementi input all'interno della struttura del form indicando come chiave il nome del campo prima del quale inserire ogni elemento (@a addCell). \n
	 * Il campo @a instance non viene mostrato nel form, neanche come campo nascosto.
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
	 * $admin_table = new adminTable($this);
	 * $admin_table->hidden(array('ref'=>$reference));
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
		$form_label_width = array_key_exists('form_label_width', $options) ? $options['form_label_width'] : null;
		$form_field_width = array_key_exists('form_field_width', $options) ? $options['form_field_width'] : null;
		
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
		$removeFields = gOpt('removeFields', $options, null);
		$viewFields = gOpt('viewFields', $options, null);
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
			
			if($this->permission($options, $field) &&
			(
				($removeFields && !in_array($field, $removeFields)) || 
				($viewFields && in_array($field, $viewFields)) || 
				(!$viewFields && !$removeFields)
			))
			{
				if(isset($inputs[$field]))
					$options_input = $inputs[$field];
				else 
					$options_input = array();
				
				if($field == 'instance')
				{
					$object->setWidget(null);
					$object->setRequired(false);
				}
				
				$structure .= $object->formElement($gform, $options_input);
				
				$name_class = get_class($object);
				
				if($object instanceof fileField || $object instanceof imageField)
					$form_upload = true;
				
				if($object->getRequired() == true && $object->getWidget() != 'hidden')
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
	 * @see field::clean()
	 * @param object $model
	 * @param array $options
	 *   - opzioni per il recupero dei dati dal form
	 *   - opzioni per selezionare gli elementi da recuperare dal form
	 *     - @b removeFields (array): elenco dei campi non presenti nel form
	 *     - @b viewFields (array): elenco dei campi presenti nel form
	 *   - @b import_file (array): attivare l'importazione di un file
	 *     - @a field_import (string): nome del campo del file di importazione
	 *     - @a field_log (string): nome del campo del file di log
	 * @param array $options_element opzioni per formattare uno o più elementi da inserire nel database
	 * @return void
	 * 
	 * Per recuperare i dati dal form vengono impostati i seguenti parametri di default:
	 * - @b formId, valore generato
	 * - @b method, post
	 * - @b validation, true
	 * - @b session_value, valore generato
	 * 
	 * Esempio:
	 * @code
	 * $id = cleanVar($_POST, 'id', 'int', '');
	 * $start = cleanVar($_POST, 'start', 'int', '');
	 * 
	 * $item = new eventItem($id, $this);
	 * $options_form = array(
	 *   'removeFields'=>null
	 * );
	 * $options_element = array('code'=>array('value_type'=>'int'));
	 * 
	 * $admin_table = new adminTable();
	 * $admin_table->modelAction($item, $options_form, $options_element);
	 * @endcode
	 */
	public function modelAction($model, $options=array(), $options_element=array()) {
		
		// Importazione di un file
		$import = false;
		if(isset($options['import_file']) && is_array($options['import_file']))
		{
			$field_import = array_key_exists('field_import', $options['import_file']) ? $options['import_file']['field_import'] : null;
			$field_log = array_key_exists('field_log', $options['import_file']) ? $options['import_file']['field_log'] : null;
			
			if($field_import) $import = true;
		}
		
		// Valori di default di form e sessione
		$default_formid = 'form'.$model->getTable().$model->id;
		$default_session = 'dataform'.$model->getTable().$model->id;
		
		// Opzioni generali per il recupero dei dati dal form
		$formId = array_key_exists('formId', $options) ? $options['formId'] : $default_formid;
		$method = array_key_exists('method', $options) ? $options['method'] : 'post';
		$validation = array_key_exists('validation', $options) ? $options['validation'] : true;
		$session_value = array_key_exists('session_value', $options) ? $options['session_value'] : $default_session;
		
		// Opzioni per selezionare gli elementi da recuperare dal form
		$removeFields = array_key_exists('removeFields', $options) ? $options['removeFields'] : null;
		$viewFields = array_key_exists('viewFields', $options) ? $options['viewFields'] : null;
		
		$gform = new Form($formId, $method, $validation);
		$gform->save($session_value);
		$req_error = $gform->arequired();
		
		if($req_error > 0) 
			return array('error'=>1);
		
		foreach($model->getStructure() as $field=>$object) {
			
			if($this->permission($options, $field) &&
			(
				($removeFields && !in_array($field, $removeFields)) || 
				($viewFields && in_array($field, $viewFields)) || 
				(!$viewFields && !$removeFields)
			))
			{
				if(isset($options_element[$field]))
					$opt_element = $options_element[$field];
				else 
					$opt_element = array();
				
				if($field == 'instance' && is_null($model->instance))
				{
					$model->instance = $this->_controller->getInstance();
				}
				else
				{
					$value = $object->clean($opt_element);
					$result = $object->validate($value);

					if($result === true) {
						$model->{$field} = $value;
					}
					else {
						return array('error'=>$result['error']);
					}
					
					if($import)
					{
						if($field == $field_import)
							$path_to_file = $object->getPath();
					}
				}
			}
		}
		
		if($import)
		{
			$result = $this->readFile($model, $path_to_file);
			if($field_log)
				$model->{$field_log} = $result;
		}
		
		return $model->updateDbData();
	}
	
	/**
	 * Gestisce il backoffice completo di un modello (wrapper)
	 * 
	 * @see permission()
	 * @see adminForm()
	 * @see adminDelete()
	 * @see adminList()
	 * @param string $model_class nome della classe del modello
	 * @param array $options_view opzioni della vista (comprese le autorizzazioni a visualizzare singoli campi)
	 * @param array $options_form opzioni del form (comprese le autorizzazioni a mostrare l'input di singoli campi e a salvarli)
	 * @param array $inputs opzioni degli elementi nel form
	 * @return string
	 */
	public function backOffice($model_class, $options_view=array(), $options_form=array(), $inputs=array()) {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$model_obj = new $model_class($id, $this->_controller);

		$insert = cleanVar($_GET, 'insert', 'int', '');
		$edit = cleanVar($_GET, 'edit', 'int', '');
		$delete = cleanVar($_GET, 'delete', 'int', '');

		if($insert) {
			$buffer = $this->adminForm($model_obj, $options_form, $inputs);
		}
		elseif($edit) {
			$buffer = $this->adminForm($model_obj, $options_form, $inputs);
		}
		elseif($delete) {
			$buffer = $this->adminDelete($model_obj, $options_form);
		}
		else {
			$buffer = $this->adminList($model_obj, $options_view);
		}

		return $buffer;
	}

	/**
	 * Eliminazione di un record (richiesta di conferma soltanto da javascript)
	 * 
	 * @see backOffice()
	 * @param object $model
	 * @param array $options_form
	 *   array associativo di opzioni
	 *   - @b link_delete (string): indirizzo al quale si viene rimandati dopo la procedura di eliminazione del record (se non presente viene costruito automaticamente)
	 * @return redirect
	 */
	public function adminDelete($model, $options_form) {

		if($this->_delete_deny == 'all' || in_array($model->id, $this->_delete_deny)) {
			error::raise404();	
		}
		
		$result = $model->delete();
		
		if(isset($options_form['link_delete']) && $options_form['link_delete'])
			$link_return = $options_form['link_delete'];
		else
			$link_return = $this->editUrl(array(), array('delete', 'id'));
		
		if($result === true) {
			
			header("Location: ".$link_return);
			exit();
		}
		else {
			exit(error::errorMessage($result, $link_return));
		}
	}

	/**
	 * Wrapper per mostrare e processare il form
	 * 
	 * @see modelForm()
	 * @see modelAction()
	 * @see editUrl()
	 * @param object $model_obj
	 * @param array $options_form
	 *   array associativo di opzioni
	 *   - @b link_return (string): indirizzo al quale si viene rimandati dopo un esito positivo del form (se non presente viene costruito automaticamente)
	 * @param array $inputs
	 * @return redirect or string
	 */
	public function adminForm($model_obj, $options_form, $inputs) {

		// $this->permission($options_form);
		
		if(count($_POST)) {
	 		$link_error = $this->editUrl(null, null);
			$options_form['link_error'] = $link_error ;

			$action_result = $this->modelAction($model_obj, $options_form, $inputs);
			
			if($action_result === true) {
				
				if(isset($options_form['link_return']) && $options_form['link_return'])
					$link_return = $options_form['link_return'];
				else
					$link_return = $this->editUrl(array(), array('edit', 'insert', 'id'));
				
				header("Location: $link_return");
				exit();
			}
			else {
				exit(error::errorMessage($action_result, $link_error));
			}
		}
		else {

			// edit
			if($model_obj->id) {
				if($this->_edit_deny == 'all' || in_array($model_obj->id, $this->_edit_deny)) {
					error::raise404();	
				}
				$title = sprintf(_("Modifica %s"), $model_obj->getModelLabel());
			}
			// insert
			else {
				if(!$this->_allow_insertion) {
					error::raise404();	
				}
				$title = sprintf(_("Inserimento %s"), $model_obj->getModelLabel());
			}

			$form = $this->modelForm($model_obj, $options_form, $inputs);

			$this->_view->setViewTpl('admin_table_form');
			$this->_view->assign('title', $title);
			$this->_view->assign('form', $form);

			return $this->_view->render();
		}
	}

	/**
	 * Lista dei record
	 * 
	 * @see backOffice()
	 * @param object $model
	 * @param array $options_view
	 *   array associativo di opzioni
	 *   - @b filter_fields (array): campi sui quali applicare il filtro per la ricerca automatica
	 *   - @b list_display (array): campi mostrati nella lista (se vuoto mostra tutti)
	 *   - @b list_remove (array): campi da non mostrare nella lista (default: instance)
	 *   - @b items_for_page (integer): numero di record per pagina
	 *   - @b list_title (string): titolo
	 *   - @b list_description (string): descrizione sotto il titolo (informazioni aggiuntive)
	 *   - @b list_where (array): condizioni della query che estrae i dati dell'elenco
	 *   - @b link_fields (array): campi sui quali impostare un collegamento, nel formato nome_campo=>array('link'=>indirizzo, 'param_id'=>'ref')
	 *     - @a link (string), indirizzo del collegamento
	 *     - @a param_id (string), nome del parametro identificativo da aggiungere all'indirizzo (default: id[=valore_id])
	 *     esempio: array('link_fields'=>array('codfisc'=>array('link'=>$this->_plink->aLink($this->_instanceName, 'view')))
	 *   - @b add_params_url (array): parametri aggiuntivi da passare ai link delle operazioni sui record
	 *   - @b add_buttons (array): bottoni aggiuntivi da anteporre a quelli di modifica ed eliminazione, nel formato array(array('label'=>pub::icon('group'), 'link'=>indirizzo, 'param_id'=>'ref'))
	 *     - @a label (string), nome del bottone
	 *     - @a link (string), indirizzo del collegamento
	 *     - @a param_id (string), nome del parametro identificativo da aggiungere all'indirizzo (default: id[=valore_id])
	 * @return string
	 */
	public function adminList($model, $options_view=array()) {

		// $this->permission($options_view);
		
		$db = db::instance();

		$model_structure = $model->getStructure();
		$model_table = $model->getTable();

		// some options
		$this->_filter_fields = gOpt('filter_fields', $options_view, array());
		$this->_list_display = gOpt('list_display', $options_view, array());
		$this->_list_remove = gOpt('list_remove', $options_view, array('instance'));
		$this->_ifp = gOpt('items_for_page', $options_view, 20);
		$list_title = gOpt('list_title', $options_view, ucfirst($model->getModelLabel()));
		$list_description = gOpt('list_description', $options_view, "<p>"._("Lista record registrati")."</p>");
		$list_where = gOpt('list_where', $options_view, array());
		$link_fields = gOpt('link_fields', $options_view, array());
		$addParamsUrl = gOpt('add_params_url', $options_view, array());
		$add_buttons = gOpt('add_buttons', $options_view, array());

		// fields to be shown
		$fields_loop = array();
		if($this->_list_display) {
			foreach($this->_list_display as $fname) {
				$fields_loop[$fname] = $model_structure[$fname];
			}
		}
		else {
			$fields_loop = $model_structure;
			if(count($this->_list_remove))
			{
				foreach($this->_list_remove AS $value)
					unset($fields_loop[$value]);
			}
		}
		
		$order = cleanVar($_GET, 'order', 'string', '');
		if(!$order) $order = 'id DESC';
		// get order field and direction
		preg_match("#^([^ ,]*)\s?((ASC)|(DESC))?.*$#", $order, $matches);
		$field_order = isset($matches[1]) && $matches[1] ? $matches[1] : '';
		$order_dir = isset($matches[2]) && $matches[2] ? $matches[2] : '';

		// filter form
		$tot_ff = count($this->_filter_fields);
		if($tot_ff) $this->setSessionSearch($model);	

		// managing instance
		$query_where = array();
		if(array_key_exists('instance', $model_structure)) {
			$query_where[] = "instance='".$this->_controller->getInstance()."'";
		}
		
		//prepare query
		$query_selection = "DISTINCT(".$model_table.".id)";
		$query_table = array($model_table);
		if(count($list_where)) {
			$query_where = array_merge($query_where, $list_where);
		}
		// filters
		if($tot_ff) {
			$this->addWhereClauses($query_where, $model);
		}
		// order
		$query_order = $model_structure[$field_order]->adminListOrder($order_dir, $query_where, $query_table);

		$tot_records_result = $db->select("COUNT(id) as tot", $query_table, implode(' AND ', $query_where), null);
		$tot_records = $tot_records_result[0]['tot'];

		$pagelist = new PageList($this->_ifp, $tot_records, 'array');

		$limit = array($pagelist->start(), $pagelist->rangeNumber);

		$records = $db->select($query_selection, $query_table, implode(' AND ', $query_where), $query_order, $limit);
		if(!$records) $records = array();

		$heads = array();

		foreach($fields_loop as $field_name=>$field_obj) {

			if($this->permission($options_view, $field_name))
			{
				$model_label = $model_structure[$field_name]->getLabel();
				$label = is_array($model_label) ? $model_label[0] : $model_label;

				if($field_obj->canBeOrdered()) {

					$ord = $order == $field_name." ASC" ? $field_name." DESC" : $field_name." ASC";
					if($order == $field_name." ASC") {
						$jsover = "$(this).getNext('.arrow').removeClass('arrow_up').addClass('arrow_down')";
						$jsout = "$(this).getNext('.arrow').removeClass('arrow_down').addClass('arrow_up')";
						$css_class = "arrow_up";
					}
					elseif($order == $field_name." DESC") {
						$jsover = "$(this).getNext('.arrow').removeClass('arrow_down').addClass('arrow_up')";
						$jsout = "$(this).getNext('.arrow').removeClass('arrow_up').addClass('arrow_down')";
						$css_class = "arrow_down";
					}
					else {
						$js = '';
						$jsover = "$(this).getNext('.arrow').addClass('arrow_up')";
						$jsout = "$(this).getNext('.arrow').removeClass('arrow_up')";
						$a_style = "visibility:hidden";
						$css_class = '';
					}

					$add_params = $addParamsUrl;
					$add_params['order'] = $ord;
					$link = $this->editUrl($add_params, array('start'));
					$head_t = "<a href=\"".$link."\" onmouseover=\"".$jsover."\" onmouseout=\"".$jsout."\" onclick=\"$(this).setProperty('onmouseout', '')\">".$label."</a>";
					$heads[] = $head_t." <div style=\"margin-right: 5px;top:3px;\" class=\"right arrow $css_class\"></div>";
				}
				else {
					$heads[] = $label;
				}
			}
		}
		$heads[] = array('text'=>'', 'class'=>'no_border no_bkg');

		$rows = array();
		foreach($records as $r) {
				
			$record_model = new $model($r['id'], $this->_controller);
			$record_model_structure = $record_model->getStructure();

			$row = array();
			foreach($fields_loop as $field_name=>$field_obj) {
				
				if($this->permission($options_view, $field_name))
				{
					$record_value = (string) $record_model_structure[$field_name];
					if(isset($link_fields[$field_name]) && $link_fields[$field_name])
					{
						$link_field = $link_fields[$field_name]['link'];
						$link_field_param = array_key_exists('param_id', $link_fields[$field_name]) ? $link_fields[$field_name]['param_id'] : 'id';
						
						// PROBLEMI CON I PERMALINKS
						//$plink = new Link();
						//$link_field = $plink->addParams($link_field, $link_field_param."=".$r['id'], false);
						$link_field = $link_field.'&'.$link_field_param."=".$r['id'];
						
						$record_value = "<a href=\"".$link_field."\">$record_value</a>";
					}
					
					$row[] = $record_value;
				}
			}

			$links = array();
			
			if(count($add_buttons))
			{
				foreach($add_buttons AS $value)
				{
					if(is_array($value))
					{
						$label_button = array_key_exists('label', $value) ? $value['label'] : null;
						$link_button = array_key_exists('link', $value) ? $value['link'] : null;
						$param_id_button = array_key_exists('param_id', $value) ? $value['param_id'] : 'id';
						
						if($label_button && $link_button && $param_id_button)
						{
							$link_button = $link_button.'&'.$param_id_button."=".$r['id'];
							$links[] = "<a href=\"$link_button\">$label_button</a>";
						}
					}
				}
			}
			
			$add_params_edit = array('edit'=>1, 'id'=>$r['id']);
			$add_params_delete = array('delete'=>1, 'id'=>$r['id']);
			if(count($addParamsUrl))
			{
				foreach($addParamsUrl AS $key=>$value)
				{
					$add_params_edit[$key] = $value;
					$add_params_delete[$key] = $value;
				}
			}
			
			if($this->_edit_deny != 'all' && !in_array($r['id'], $this->_edit_deny)) {
				$links[] = "<a href=\"".$this->editUrl($add_params_edit)."\">".pub::icon('modify')."</a>";
			}
			if($this->_delete_deny != 'all' && !in_array($r['id'], $this->_delete_deny)) {
				$links[] = "<a href=\"javascript: if(confirm('".htmlspecialchars(sprintf(_("Sicuro di voler eliminare \"%s\"?"), $record_model), ENT_QUOTES)."')) location.href='".$this->editUrl($add_params_delete)."';\">".pub::icon('delete')."</a>";
			}
			$buttons = array(
				array('text' => implode(' ', $links), 'class' => 'no_border no_bkg')
			); 

			$rows[] = array_merge($row, $buttons);
		}

		$this->_view->setViewTpl('table');
		$this->_view->assign('class', 'generic');
		$this->_view->assign('caption', '');
		$this->_view->assign('heads', $heads);
		$this->_view->assign('rows', $rows);

		$table = $this->_view->render();

		if($this->_allow_insertion) {
			$link_insert = "<a href=\"".$this->editUrl(array('insert'=>1))."\">".pub::icon('insert')."</a>";
		}
		else {
			$link_insert = "";
		}

		$this->_view->setViewTpl('admin_table_list');
		$this->_view->assign('title', $list_title);
		$this->_view->assign('description', $list_description);
		$this->_view->assign('link_insert', $link_insert);
		$this->_view->assign('table', $table);
		$this->_view->assign('tot_records', $tot_records);
		$this->_view->assign('form_filters_title', _("Filtri"));
		$this->_view->assign('form_filters', $tot_ff ? $this->formFilters($model, $options_view) : null);
		$this->_view->assign('pnavigation', $pagelist->listReferenceGINO($_SERVER['REQUEST_URI'], false, '', '', '', false, null, null, array('add_no_permalink'=>true)));
		$this->_view->assign('psummary', $pagelist->reassumedPrint());

		return $this->_view->render();
	}

	/**
	 * Setta le variabili di sessione usate per filtrare i record nella lista amministrativa
	 *
	 * @param object $model
	 * @return void
	 */
	protected function setSessionSearch($model) {

		$model_structure = $model->getStructure();
		$class_name = get_class($model);

		foreach($this->_filter_fields as $fname) {

			if(!isset($this->session->{$class_name.'_'.$fname.'_filter'})) {
				$this->session->{$class_name.'_'.$fname.'_filter'} = null;
			}
		}

		if(isset($_POST['ats_submit'])) {

			foreach($this->_filter_fields as $fname) {

				if(isset($_POST[$fname]) && $_POST[$fname] !== '') {
					$this->session->{$class_name.'_'.$fname.'_filter'} = $model_structure[$fname]->clean(array("escape"=>false));
				}
				else {
					$this->session->{$class_name.'_'.$fname.'_filter'} = null;
				}
			}
		}
	}

	/**
	 * Setta la condizione where usata per filtrare i record nella admin list
	 *
	 * @param array $query_where
	 * @param object $model
	 * @return string (the where clause)
	 */
	protected function addWhereClauses(&$query_where, $model) {

		$model_structure = $model->getStructure();
		$class_name = get_class($model);

		foreach($this->_filter_fields as $fname) {
			if(isset($this->session->{$class_name.'_'.$fname.'_filter'})) {
				$query_where[] = $model_structure[$fname]->filterWhereClause($this->session->{$class_name.'_'.$fname.'_filter'});
			}
		}
	}

	/**
	 * Form per filtraggio record
	 * 
	 * @see permission()
	 * @param object $model
	 * @param array $options autorizzazioni alla visualizzazione dei singoli campi
	 * @return il form
	 */
	protected function formFilters($model, $options) {

		$model_structure = $model->getStructure();
		$class_name = get_class($model);

		$gform = new Form('atbl_filter_form', 'post', false);

		$form = $gform->form($this->editUrl(array(), array('start')), false, '');

		foreach($this->_filter_fields as $fname) {
			
			if($this->permission($options, $fname))
			{
				$field = $model_structure[$fname];
				$field->setValue($this->session->{$class_name.'_'.$fname.'_filter'});
				$form .= $field->formElement($gform, array('required'=>false));
			}
		}

		$onclick = "onclick=\"$$('#atbl_filter_form input, #atbl_filter_form select').each(function(el) {
			if(el.get('type')==='text') el.value='';
			else if(el.get('type')==='radio') el.removeProperty('checked');
			else if(el.get('tag')=='select') el.getChildren('option').removeProperty('selected');
		});\"";

		$input_reset = $gform->input('ats_reset', 'button', _("tutti"), array("classField"=>"generic", "js"=>$onclick));
		$form .= $gform->cinput('ats_submit', 'submit', _("filtra"), '', array("classField"=>"submit", "text_add"=>' '.$input_reset));
		$form .= $gform->cform();

		return $form;
	}

	/**
	 * Costruisce il percorso per il reindirizzamento
	 * 
	 * @param array $add_params elenco parametri da aggiungere alla REQUEST_URI (formato chiave=>valore)
	 * @param array $remove_params elenco parametri da rimuovere dalla REQUEST_URI
	 * @return string
	 */
	protected function editUrl($add_params, $remove_params=null) {

		$url = $_SERVER['REQUEST_URI'];

		if($remove_params) {
			foreach($remove_params as $key) {
				$url = preg_replace("#&?".preg_quote($key)."=[^&]*#", '', $url);
			}
		}
		
		if($add_params) {
			$add_url = '';
			foreach($add_params as $key=>$value) {
				$url = preg_replace("#&".preg_quote($key)."=[^&]*#", '', $url);
				$add_url .= '&'.$key.'='.$value;
			}

			if(preg_match("#\?#", $url)) {
				$url =  $url.$add_url;		
			}
			else {
				$url = $url."?".substr($add_url, 1);
			}
		}

		return $url;
	}
	
	protected function readFile($model, $path_to_file) {
		
		return null;
	}
}
?>
