<?php
/**
 * @file class.model.php
 * @brief Contiene la classe Model
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
 * Le proprietà su DB possono essere lette attraverso la funzione __get, ma possono anche essere protette costruendo una funzione get personalizzata all'interno della classe. \n
 * Le proprietà su DB possono essere impostate attraverso il metodo __set, che di default legge le proprietà dall'array POST come valore stringa. \n
 * Di conseguenza può essere necessario impostare puntualmente una proprietà assegnandole un valore oppure, se il tipo di valore è diverso da stringa, implementare uno specifico metodo __set che viene chiamato prima.
 * 
 * La classe figlia che costruisce la classe le passa il valore ID del record dell'oggetto direttamente nel costruttore:
 * @code
 * parent::__construct($i);
 * @endcode
 * 
 * ###Criteri di costruzione di una tabella per la definizione della struttura
 * Le tabelle che si riferiscono alle applicazioni possono essere gestite in modo automatico attraverso la classe @a adminTable. \n
 * I modelli delle tabelle estendono la classe @a model che ne ricava la struttura. Ne deriva che le tabelle devono essere costruite seguendo specifici criteri:
 *   - i campi obbligatori devono essere 'not null'
 *   - un campo auto-increment viene gestito automaticamente come input di tipo hidden
 *   - definire gli eventuali valori di default (soprattutto nei campi enumerazione)
 * 
 * ###Ulteriori elementi che contribuiscono alla definizione della struttura
 * Le label dei campi devono essere definite nel modello nella proprietà @a $_fields_label. Una label non definita prende il nome del campo. \n
 * Esempio:
 * @code
 * $this->_fields_label = array(
 *   'ctg'=>_("Categoria"),
 *   'name'=>_("Titolo"),
 *   'private'=>array(_("Tipologia"), _("privato: visibile solo dal relativo gruppo"))
 * );
 * @endcode
 */
 abstract class Model {

	protected $_registry;
	protected $_db;
	protected $_tbl_data;
	protected $_model_label;
	
	/**
	 * Struttura della tabella
	 * 
	 * @var array
	 */
	protected $_structure;
	
	/**
	 * Oggetto della localizzazione
	 * 
	 * @var object
	 */
	protected $_locale;
	
	//protected $_main_class;
	
	/**
	 * Intestazioni dei campi del database nel form
	 * 
	 * @var array
	 */
	protected $_fields_label=array();
	protected $_p, $_chgP = array();
	
	protected $_lng_dft, $_lng_nav;
	private $_trd;

	/**
	 * Costruttore
	 *
	 * @param integer $id valore ID del record dell'oggetto
	 * @return void
	 */
	function __construct($id) {

    $this->_registry = registry::instance();
    $session = $this->_registry->session;
	  $this->_db = $this->_registry->db;
		$this->_lng_dft = $session->lngDft;
		$this->_lng_nav = $session->lng;
		$this->_structure = $this->structure($id);
		$this->_p['instance'] = null;
		
		//$this->_locale = locale::instance_to_class($this->_main_class);
		
    $this->_trd = new translation($this->_lng_nav, $this->_lng_dft);
	}
	
 	public function __toString() {

		return $this->id;
	}

	public function fieldLabel($field) {

		return $this->_fields_label[$field];
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
	 * @param mixed $pValue
	 */
	public function __set($pName, $pValue) {

		if(!array_key_exists($pName, $this->_p)) return null;
		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($pValue);
		else {
			if($this->_p[$pName] !== $pValue && !in_array($pName, $this->_chgP)) $this->_chgP[] = $pName;
			$this->_p[$pName] = $pValue;
		}
	}

  public static function getSelectOptionsFromObjects($objects) {
    $res = array();
    foreach($objects as $obj) {
      $res[$obj->id] = (string) $obj;
    }
    return $res;
  }

	/**
	 * Recupera le proprietà con la traduzione
	 * 
	 * @param string $pName
	 * @return string
	 */
	public function ml($pName) {
		
		return ($this->_trd->selectTXT($this->_tbl_data, $pName, $this->_p['id']));
	}
	
	/**
	 * Recupera la struttura
	 */
	public function getStructure() {
		
		return $this->_structure;
	}

	/**
	 * Salva i cambiamenti fatti sull'oggetto modificando o inserendo un nuovo record su DB
	 *
	 * @return boolean
	 */
	public function updateDbData() {
	
		if($this->_p['id']) { 
			if(!sizeof($this->_chgP)) return true;
			
			$fields = array();
			foreach($this->_chgP as $pName) $fields[$pName] = $this->_p[$pName];
			
			$result = $this->_db->update($fields, $this->_tbl_data, "id='{$this->_p['id']}'");
		}
		else {
			if(!sizeof($this->_chgP)) return true;
			
			$fields = array();
			foreach($this->_chgP as $pName) $fields[$pName] = $this->_p[$pName];
			
			$result = $this->_db->insert($fields, $this->_tbl_data);
		}
		
		if(!$result) {
			return array('error'=>9);
		}

		if(!$this->_p['id']) $this->_p['id'] = $this->_db->getlastid($this->_tbl_data);

		return $result;
	}

	/**
	 * Elimina le proprietà su DB di un oggetto
	 *
	 * @return boolean
	 */
	public function deleteDbData() {
	
		$this->_registry->trd->deleteTranslations($this->_tbl_data, $this->_p['id']);
		
		$result = $this->_db->delete($this->_tbl_data, "id='{$this->_p['id']}'");
		
		return $result;
	}
	
	/**
	 * Elimina l'oggetto
	 * 
	 * @return boolean
	 */
 	public function delete() {

		$result = $this->deleteDbData();
		if($result !== true) {
			return array("error"=>37);
		}

		foreach($this->_structure as $field) {
			if(method_exists($field, 'delete')) {
				$result = $field->delete();
				if($result !== true) {
					return $result;
				}
			}
		}

		return true;
	}
	
 	/**
	 * Espone il testo per personalizzare l'intestazione del form di modifica/inserimento
	 * 
	 * @return string
	 */
	public function getModelLabel() {
		
		return $this->_model_label;
	}
	
	/**
	 * Espone il nome della tabella
	 * 
	 * @return string
	 */
	public function getTable() {
		
		return $this->_tbl_data;
	}
	
	/**
	 * Definisce la struttura dei campi di una tabella del database
	 * 
	 * Gli elementi della struttura possono essere sovrascritti all'interno del metodo structure() della classe che estende model. \n
	 * 
	 * Ogni elemento viene associato a una classe del tipo di dato e le vengono passate le specifiche del campo. \n
	 * Esistono classi che corrispondono al tipo di dato e classi specifiche, per poter associare le quali è necessario sovrascrivere il campo nel metodo structure(). \n
	 * Classi specifiche per particolati tipi di dato sono foreignKeyField, imageField, fileField, hiddenField.
	 * 
	 * @see DbManager::getTableStructure()
	 * @see dataCache::get()
	 * @see dataCache::save()
	 * @param integer $id valore ID del record di riferimento
	 * @return array
	 * 
	 * Esempio di riscrittura del metodo structure():
	 * @code
	 * public function structure($id) {
	 *   
	 *   $structure = parent::structure($id);
	 *   
	 *   $structure['ctg'] = new foreignKeyField(array(
	 *     'name'=>'ctg', 
	 *     'value'=>$this->ctg, 
	 *     'label'=>$this->_fields_label['ctg'], 
	 *     'lenght'=>11, 
	 *     'fkey_table'=>eventCtg::$_tbl_ctg, 
	 *     'fkey_field'=>'name', 
	 *     'fkey_order'=>'name'
	 *   ));
	 *   
	 *   $base_path = $this->_controller->getBasePath(); // example -> /contents/events/eventsInterface/
	 *   $add_path_image = $this->id ? $this->_controller->getAddPath($this->id, 'image') : '';	// example -> id/img/
	 *   
	 *   $structure['image'] = new imageField(array(
	 *     'name'=>'image', 
	 *     'value'=>$this->image, 
	 *     'label'=>$this->_fields_label['image'], 
	 *     'lenght'=>200, 
	 *     'extensions'=>self::$extension_media, 
	 *     'path'=>$base_path, 
	 *     'add_path'=>$add_path_image, 
	 *     'resize'=>true, 
	 *     'check_type'=>false, 
	 *     'width'=>$this->_controller->getImageWidth(),
	 *     'thumb_width'=>$this->_controller->getImageThumbWidth()
	 *   ));
	 * }
	 * @endcode
	 */
 	public function structure($id) {

    if(!$this->_tbl_data) {
      exit(error::syserrorMessage('Model', 'structure', _('La tabella _tbl_data del modello non è definita')));
    }

    loader::import('class', array('Cache'));
 		if($id)
		{
			$records = $this->_db->select('*', $this->_tbl_data, "id='$id'");
			if(count($records))
				$this->_p = $records[0];
		}
 		
 		$cache = new DataCache();
		if(!$fieldsTable = $cache->get('table_structure', $this->_tbl_data, 3600)) {
			$fieldsTable = $this->_db->getTableStructure($this->_tbl_data);
			$cache->save($fieldsTable);
		}
		
		$structure = array();
		
		if(sizeof($fieldsTable) > 0)
		{
			$primary_key = $fieldsTable['primary_key'];
			$fields = $fieldsTable['fields'];
			$keys = $fieldsTable['keys'];	// array delle chiavi uniche
			
			foreach($fields AS $key=>$value)
			{
				if(!$id) $this->_p[$key] = null;
				
				$type = $value['type'];
				$maxLenght = $value['max_length'];
				$numberIntDigits = $value['n_int'];
				$numberDecimalDigits = $value['n_precision'];
				$order = $value['order'];
				$default = $value['default'];
				$null = $value['null'];
				$extra = $value['extra'];
				$enum = $value['enum'];
				
				$pkey = $key == $primary_key ? true : false;
				$ukey = in_array($key, $keys) ? true : false;
				$auto_increment = $extra == 'auto_increment' ? true : false;
				
				$dataType = $this->dataType($type);
				
				// Valori di un campo enumerazione
				if($enum)
				{
					$array = explode(',', $enum);
					$array_clean = array();
					foreach($array AS $evalue)
					{
						preg_match("#\'([0-9a-zA-Z-_,.']+)\'#", $evalue, $matches);
						if(isset($matches[1]))
							$array_clean[$matches[1]] = $matches[1];
					}
					$enum = $array_clean;
				}
				
				$label = array_key_exists($key, $this->_fields_label) ? $this->_fields_label[$key] : ucfirst($key);
				
				$options_field = array(
					'name'=>$key,
					'lenght'=>$maxLenght,
					'primary_key'=>$pkey,
					'unique_key'=>$ukey,
					'auto_increment'=>$auto_increment, 
					'type'=>$type, 
					'int_digits'=>$numberIntDigits, 
					'decimal_digits'=>$numberDecimalDigits, 
					'order'=>$order, 
					'default'=>$default, 
					'required'=>$null=='NO' ? true : false, 
					'extra'=>$extra, 
					'enum'=>$enum, 
					'label'=>$label, 
					'value'=>$this->_p[$key], 
					'table'=>$this->_tbl_data
				);
				
				$structure[$key] = loader::load('fields/'.$dataType, array($options_field));
				/*if(!class_exists($dataType))
					error::syserrorMessage('Model', 'structure', sprintf(_("Il tipo di dato del campo %s non è riconoscibile automaticamente"), $key));
				
				$structure[$key] = new $dataType($options_field);*/
			}
		}
		return $structure;
	}
	
	private function dataType($type) {
		
		if($type == 'tinyint' || $type == 'smallint'  || $type == 'mediumint' || $type == 'int' || $type == 'bigint')
			$dataType = 'integer';
		elseif($type == 'float' || $type == 'double' || $type == 'decimal')
			$dataType = 'float';
		elseif($type == 'mediumtext')
			$dataType = 'text';
		elseif($type == 'varchar')
			$dataType = 'char';
		else
			$dataType = $type;
		
		$dataType = ucfirst($dataType).'Field';
		
		return $dataType;
	}
}
?>
