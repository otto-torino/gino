<?php
/**
 * @file class.propertyObject.php
 * @brief Contiene la classe propertyObject
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Contiene i metodi utilizzati da ogni classe che abbia proprietà definite sul database
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Le proprietà su DB possono essere lette attraverso la funzione __get, ma possono anche essere protette costruendo una funzione get personalizzata all'interno della classe.
 * Le proprietà su DB possono essere impostate attraverso il metodo __set, che di default legge le proprietà dall'array POST come valore stringa.
 * Di conseguenza può essere necessario impostare puntualmente una proprietà assegnandole un valore oppure, se il tipo di valore è diverso da stringa, implementare uno specifico metodo __set che viene chiamato prima.
 * 
 * La classe figlia che costruisce la classe le passa (ad esempio) i valori del metodo initP():
 * @code
 * function initP($id) {   
 *   $query = "SELECT * FROM ".self::$_tbl_ctg." WHERE id='$id'";   
 *   $a = $db->selectquery($query);   
 *   if(sizeof($a)>0) return $a[0];   
 *   else return array('id'=>null, 'name'=>null);   
 * }
 * @endcode
 * direttamente nel costruttore:
 * @code
 * parent::__construct($this->initP($id));
 * @endcode
 */
 abstract class propertyObject {

	protected $_db;
	protected $_tbl_data;
	protected $_p, $_chgP = array();
	
	protected $_lng_dft, $_lng_nav;
	private $_trd;

	/**
	 * Costruttore
	 * @param array $data array contenente le proprietà dell'init
	 * @return void
	 */
	function __construct($data) {

		$this->_db = db::instance();
		$this->_p = $data;
	
		$session = session::instance();
	
		$this->_lng_dft = $session->lngDft;
		$this->_lng_nav = $session->lng;
		$this->_trd = new translation($this->_lng_nav, $this->_lng_dft);
	}
	
	/**
	 * Metodo richiamato ogni volta che qualcuno prova a ottenere una proprietà dell'oggetto
	 * 
	 * L'output è il metodo get specifico per questa proprietà (se esiste), altrimenti è la proprietà
	 * 
	 * @param string $pName
	 */
	public function __get($pName) {
	
		if(!array_key_exists($pName, $this->_p)) return null;
		if(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		else return $this->_p[$pName];
	}
	
	/**
	 * Metodo richiamato ogni volta che qualcuno prova a impostare una proprietà dell'oggetto
	 * 
	 * L'output è il metodo set specifico per questa proprietà (se esiste), altrimenti la proprietà è impostata leggendo l'array POST e il tipo stringa
	 * 
	 * @param string $pName
	 * @param mixed $postLabel
	 */
	public function __set($pName, $postLabel) {

		if(!array_key_exists($pName, $this->_p)) return null;
		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($postLabel);
		else {
			if($this->_p[$pName]!=cleanVar($_POST, $postLabel, 'string', null) && !in_array($pName, $this->_chgP)) $this->_chgP[] = $pName;
			$this->_p[$pName] = cleanVar($_POST, $postLabel, 'string', null);
		}
	}

	/**
	 * Recupera le proprietà con la traduzione
	 * @param string $pName
	 * @return string
	 */
	public function ml($pName) {
		
		return ($this->_trd->selectTXT($this->_tbl_data, $pName, $this->_p['id']));
	}

	/**
	 * Salva i cambiamenti fatti sull'oggetto modificando o inserendo un nuovo record su DB
	 * @return boolean
	 */
	public function updateDbData() {
	
		if($this->_p['id']) { 
			if(!sizeof($this->_chgP)) return true;
			$query = "UPDATE $this->_tbl_data SET ";
			$sets = array();
			foreach($this->_chgP as $pName) $sets[] = "$pName='{$this->_p[$pName]}'";
			$query .= implode(',',$sets)." WHERE id='{$this->_p['id']}'";
		}
		else {
			if(!sizeof($this->_chgP)) return true;
			$chgf = implode(',',$this->_chgP);
			$chgv = array();
			foreach($this->_chgP as $pName) $chgv[] = "'{$this->_p[$pName]}'";
			$query = "INSERT INTO $this->_tbl_data ($chgf) VALUES (".implode(",",$chgv).")";
		}
		$result = $this->_db->actionquery($query);

		if(!$this->_p['id']) $this->_p['id'] = $this->_db->getlastid($this->_tbl_data);

		return $result;
	}

	/**
	 * Elimina le proprietà su DB di un oggetto
	 * @return boolean
	 */
	public function deleteDbData() {
	
		language::deleteTranslations($this->_tbl_data, $this->_p['id']);
		$query = "DELETE FROM $this->_tbl_data WHERE id='{$this->_p['id']}'";
		return $this->_db->actionquery($query);
	}
	
 	/**
	 * Definisce la struttura del form a partire dalla struttura di una tabella del database
	 * 
	 * @see DbManager::getTableStructure()
	 * @param array $alter array contenente le proprietà da modificare o aggiungere a un campo 
	 * @return array
	 * 
	 * La tabella del database deve essere costruita seguendo specifici criteri:
	 * - i campi obbligatori devono essere 'not null'
	 * - un campo auto_increment e il campo di nome 'instance' vengono gestiti come input di tipo hidden
	 * - definire gli eventuali valori di default (soprattutto nei campi enumerazione)
	 * 
	 * Per quanto riguarda gli elementi di complemento per la costruzione del form
	 * - le label dei campi devono essere definite nella proprietà @a _fields_label.
	 *   Una label non definita prende il nome del campo. Esempio:
	 *   @code
	 *   $this->_fields_label = array(
	 *     'ctg'=>_("Categoria"),
	 *     'name'=>_("Titolo"),
	 *     'date'=>_("Data"),
	 *     'private'=>array(_("Tipologia"), _("privato: visibile solo dal relativo gruppo"))
	 *   );
	 *   @endcode
	 * - la struttura di default del form può essere sovrascritta utilizzando il metodo structure() nella classe che richiama il form
	 *     - è possibile riscrivere gli input form definendo proprietà specifiche, oppure reimpostando il tipo di campo scegliendone uno adatto nella classe adminTable().
	 *     - è possibile modificare o aggiungere una proprietà 'input' a un campo passandola direttamente al metodo structure(array('fieldname'=>array('input'=>'id'=>'fieldname,[,])))
	 * - la struttura del form può essere modificata utilizzando le opzioni del metodo alterForm()
	 *     - è possibile non mostrare alcuni campi del form oppure scegliere quali campi mostrare
	 *     - è possibile aggiungere degli elementi html nel form, ad esempio
	 *     @code
	 *     $gform = new Form('', '', '');
	 *     $addCell = array(
	 *       'lng'=>$gform->cinput('map_addr', 'text', '', _("Indirizzo"), array("size"=>40, "maxlength"=>200, "id"=>"map_addr"))
	 *       .$gform->cinput('map_coord', 'button', _("converti"), '', array("id"=>"map_coord", "classField"=>"generic", "js"=>$onclick))
	 *     );
	 *     @endcode
	 * - nei campi di tipo FOREIGN_KEY occorre impostare i parametri di riferimento della tabella esterna nella chiave @a foreign_key:
	 *   @code
	 *   'foreign_key'=>array('table'=>$table, 'field'=>'name', 'where'=>null, 'order'=>'name'),
	 *   'input'=>array('type'=>[...])
	 *   @endcode
	 * - nei campi non FOREIGN_KEY con input di tipo select, radio, multicheck, l'insieme degli elementi (chiave=>valore) da utilizzare per popolare gli input deve essere passato come array nella chiave @a enum
	 * - nei campi di tipo TIME è possibile utilizzare la chiave @seconds per visualizzare o meno i secondi  
	 * 
	 * 
	 * Esempio di riscrittura del metodo structure():
	 * @code
	 * public function structure($options=array()) {
	 *   
	 *   $pathToThumbImage = array_key_exists('pathToThumbImage', $options) ? $options['pathToThumbImage'] : '';
	 *   $valueDuration = array_key_exists('valueDuration', $options) ? $options['valueDuration'] : 0;
	 *   
	 *   $structure = parent::structure(array(
	 *     'lat'=>array('input'=>array('id'=>'lat')), 
	 *     'duration'=>array('input'=>array('value'=>$valueDuration))
	 *   ));
	 *   
	 *   $adminTable = new adminTable();
	 *   
	 *   $structure['ctg'] = $adminTable->foreignKeyField(array(
	 *   'name'=>'ctg',
	 *    'lenght'=>11,
	 *   'foreign_key'=>array('table'=>eventCtg::$_tbl_ctg, 'field'=>'name', 'order'=>'name'),
	 *   'input'=>array('type'=>'select', 'value'=>$this->_p['ctg'], 'label'=>$this->_fields_label['ctg'])
	 *   ));
	 *   
	 *   $structure['description'] = $adminTable->textField(array(
	 *     'name'=>'description',
	 *     'input'=>array(
	 *       'type'=>'editor', 
	 *       'value'=>$this->description, 
	 *       'label'=>$this->_fields_label['description'], 
	 *       'notes'=>false, 
	 *       'img_preview'=>true, 
	 *      'trnsl'=>true, 
	 *       'field'=>"description", 
	 *       'fck_toolbar'=>self::$_fck_toolbar, 
	 *       'fck_height'=>100
	 *     )
	 *   ));
	 *   
	 *   $structure['image'] = $adminTable->fileField(array(
	 *     'name'=>'image',
	 *     'lenght'=>200,
	 *     'input'=>array(
	 *       'value'=>$this->_p['image'], 
	 *       'label'=>$this->_fields_label['image'], 
	 *       "extensions"=>self::$extension_media, 
	 *       "del_check"=>true, "preview"=>true, 
	 *       "previewSrc"=>$pathToThumbImage
	 *     )
	 *   ));
	 *   
	 *   $structure = parent::alterForm($structure, $options);
	 *   return $structure;
	 * }
	 * @endcode
	 * 
	 * Esempio di generazione del form
	 * @code
	 * [...]
	 * $gform = new Form('', '', '');
	 * $addCell = array(
	 *   'lng'=>$gform->cinput('map_address', 'text', '', array(_("Indirizzo evento"), _("es: torino, via mazzini 37<br />utilizzare 'converti' per calcolare latitudine e longitudine")), array("size"=>40, "maxlength"=>200, "id"=>"map_address"))
	 *   .$gform->cinput('map_coord', 'button', _("converti"), '', array("id"=>"map_coord", "classField"=>"generic", "js"=>$onclick))
	 * );
	 * $removeFields = $manage_ctg ? null : array('ctg');
	 * 
	 * $adminTable = new adminTable($this->structure(
	 *   array(
	 *     'removeFields'=>$removeFields, 
	 *     'viewFields'=>null, 
	 *     'addCell'=>$addCell, 
	 *     'manage_ctg'=>$manage_ctg, 
	 *     'pathToThumbImage'=>$interface->getEventWWW($this->id).$interface->getPrefixThumb().$this->image,
	 *     'valueDuration'=>$value_duration
	 *   ))
	 * );
	 * 
	 * $options = array(
	 *   'formId'=>'eform',
	 *   'validation'=>true,
	 *   'trnsl_table'=>self::$_tbl_item,
	 *   'trnsl_id'=>$this->id,
	 *   'session_value'=>'dataform',
	 *   'f_action'=>$formaction,
	 *   'f_required'=>$required,
	 *   'f_upload'=>true, 
	 *   's_name'=>'submit_action', 
	 *   's_value'=>$submit
	 * );
	 * 
	 * $adminTable->hidden = $hidden;
	 * $buffer = $adminTable->makeForm($options);
	 * @endcode
	 */
 	public function structure($alter=array()) {

		$fieldsTable = $this->_db->getTableStructure($this->_tbl_data);
		$removeFields = array('instance');	// elenco campi da non inserire nel form
		
		$structure = array();
		
		if(sizeof($fieldsTable) > 0)
		{
			$adminTable = new adminTable();
			
			$primary_key = $fieldsTable['primary_key'];
			$fields = $fieldsTable['fields'];
			$keys = $fieldsTable['keys'];
			
			foreach($fields AS $key=>$value)
			{
				$dataType = $value['type'];
				$maxLenght = $value['max_length'];
				$numberIntDigits = $value['n_int'];
				$numberDecimalDigits = $value['n_precision'];
				$order = $value['order'];
				$default = $value['default'];
				$null = $value['null'];
				$extra = $value['extra'];
				$enum = $value['enum'];
				
				if($dataType == 'tinyint' || $dataType == 'smallint'  || $dataType == 'mediumint' || $dataType == 'int')
					$dataType = 'integer';
				elseif($dataType == 'varchar')
					$dataType = 'char';
				
				$dataType = $dataType.'Field';
				$pkey = $key == $primary_key ? true : false;
				$auto_increment = $extra == 'auto_increment' ? true : false;
				
				$hidden = ($auto_increment) ? 'hidden' : '';
				$label = array_key_exists($key, $this->_fields_label) ? $this->_fields_label[$key] : ucfirst($key);
				$required = $null == 'NO' ? true : false;
				
				// Valori di un campo enumerazione
				if($enum)
				{
					$array = explode(',', $enum);
					$array_clean = array();
					foreach($array AS $evalue)
					{
						preg_match("#\'([0-9a-zA-Z-_,.']+)\'#", $evalue, $matches);
						$array_clean[$matches[1]] = $matches[1];
					}
					$enum = $array_clean;
				}
				
				$trnsl = ($dataType == 'varchar' || $dataType == 'char' || $dataType == 'text') ? true : false;
				if($trnsl)
				{
					$trnsl_table = $this->_tbl_data;
					$trnsl_id = $this->_p['id'];
					$trnsl_field = $key;
				}
				else 
				{
					$trnsl_table = '';
					$trnsl_id = '';
					$trnsl_field = '';
				}
				
				$options_field = array(
					'name'=>$key,
					'lenght'=>$maxLenght,
					'primary_key'=>$pkey,
					'auto_increment'=>$auto_increment,
					'input'=>array(
						'type'=>$hidden, 
						'value'=>$this->_p[$key], 
						'label'=>$label, 
						'required'=>$required, 
						'default'=>$default, 
						'enum'=>$enum, 
						'maxlength'=>$maxLenght, 
						'trnsl'=>$trnsl, 
						'trnsl_table'=>$trnsl_table, 
						'trnsl_id'=>$trnsl_id, 
						'field'=>$trnsl_field
					)
				);
				
				if(count($alter) && array_key_exists($key, $alter))
				{
					$a_alter = $alter[$key];
					$a_input = array_key_exists('input', $a_alter) ? $a_alter['input'] : array();
					
					if(count($a_input))
					{
						foreach ($a_input AS $okey=>$ovalue)
						{
							$options_field['input'][$okey] = $ovalue;
						}
					}
				}

				if(!in_array($key, $removeFields))
					$structure[$key] = $adminTable->{$dataType}($options_field);
			}
		}
		return $structure;
	}
	
	/**
	 * Modifica la struttura del form
	 * 
	 * @param $structure elementi del form
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b removeFields (array): elenco dei campi da non mostrare nel form
	 *   - @b viewFields (array): elenco dei campi da mostrare nel form
	 *   - @b addCell (array): elementi html da mostrare in aggiunta nel form; la chiave è il valore del campo prima del quale deve essere inserito il contenuto html
	 * @return array
	 * 
	 * Tra le opzioni @a removeFields e @a viewFields comanda la prima.
	 */
	public function alterForm($structure, $options=array()) {

		$removeFields = array_key_exists('removeFields', $options) ? $options['removeFields'] : null;
		$viewFields = array_key_exists('viewFields', $options) ? $options['viewFields'] : null;
		$addCell = array_key_exists('addCell', $options) ? $options['addCell'] : null;
		
		if($addCell)
		{
			$count = 1;
			$new = array();
			foreach($structure AS $key=>$value)
			{
				foreach($addCell AS $ref_key=>$addvalue)
				{
					if($ref_key == $key)
					{
						$addkey = 'addHtml'.$count;
						$new[$addkey] = $addvalue;
						$count++;
					}
				}
				$new[$key] = $value;
			}
			$structure = $new;
		}
		
		if($removeFields)
		{
			foreach($removeFields AS $value)
			{
				$structure[$value] = null;
			}
		}
		elseif($viewFields)
		{
			foreach($structure AS $key=>$value)
			{
				if(!in_array($key, $viewFields))
					$structure[$key] = null;
			}
		}
		
		return $structure;
	}
}
?>
