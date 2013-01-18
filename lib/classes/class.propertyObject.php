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
 * La classe figlia che costruisce la classe le passa il valore ID del record dell'oggetto direttamente nel costruttore:
 * @code
 * parent::__construct($i);
 * @endcode
 */
 abstract class propertyObject {

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

		$this->_db = db::instance();
		$this->_structure = $this->structure($id);
		$this->_p['instance'] = null;
		
		$session = session::instance();
	
		$this->_lng_dft = $session->lngDft;
		$this->_lng_nav = $session->lng;
		$this->_trd = new translation($this->_lng_nav, $this->_lng_dft);
	}
	
 	public function __toString() {

		return $this->id;
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
		
		if($query) {
			$result = $this->_db->actionquery($query);
			if(!$result) {
				return array('error'=>9);
			}
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
	
		language::deleteTranslations($this->_tbl_data, $this->_p['id']);
		$query = "DELETE FROM $this->_tbl_data WHERE id='{$this->_p['id']}'";
		return $this->_db->actionquery($query);
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
	 * Gli elementi della struttura possono essere sovrascritti all'interno del metodo structure() della classe che estende propertyObject. \n
	 * 
	 * Ogni elemento viene associato a una classe del tipo di dato e le vengono passate le specifiche del campo. \n
	 * Esistono classi che corrispondono al tipo di dato e classi specifiche, per poter associare le quali è necessario sovrascrivere il campo nel metodo structure(). \n
	 * Classi specifiche per particolati tipi di dato sono foreignKeyField, imageField, fileField, hiddenField.
	 * 
	 * @see DbManager::getTableStructure()
	 * @param integer $id valore ID del record di riferimento
	 * @return array
	 * 
	 * La tabella del database deve essere costruita seguendo specifici criteri:
	 * - i campi obbligatori devono essere 'not null'
	 * - un campo auto_increment viene gestito automaticamente come input di tipo hidden
	 * - definire gli eventuali valori di default (soprattutto nei campi enumerazione)
	 * 
	 * Ulteriori elementi che contribuiscono alla definizione della struttura
	 * - le label dei campi devono essere definite nella proprietà @a $_fields_label \n
	 *   Una label non definita prende il nome del campo. Esempio:
	 *   @code
	 *   $this->_fields_label = array(
	 *     'ctg'=>_("Categoria"),
	 *     'name'=>_("Titolo"),
	 *     'private'=>array(_("Tipologia"), _("privato: visibile solo dal relativo gruppo"))
	 *   );
	 *   @endcode
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
	 *   $base_path = $this->_controller->getBasePath('abs'); // ritorna /contents/events/eventsInterface/
	 *   $add_path_image = $this->id ? $this->_controller->getAddPath($this->id, 'image') : '';	// id/img/
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

 		if($id)
		{
			$query = "SELECT * FROM ".$this->_tbl_data." WHERE id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a)>0) $this->_p = $a[0];
		}
 		
 		$fieldsTable = $this->_db->getTableStructure($this->_tbl_data);
		
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
				
				if(!class_exists($dataType))
					error::syserrorMessage('propertyObject', 'structure', sprintf(_("Il tipo di dato del campo %s non è riconoscibile automaticamente"), $key));
				
				$structure[$key] = new $dataType($options_field);
			}
		}
		return $structure;
	}
	
	private function dataType($type) {
		
		if($type == 'tinyint' || $type == 'smallint'  || $type == 'mediumint' || $type == 'int')
			$dataType = 'integer';
		elseif($type == 'float' || $type == 'double' || $type == 'decimal')
			$dataType = 'float';
		elseif($type == 'mediumtext')
			$dataType = 'text';
		elseif($type == 'varchar')
			$dataType = 'char';
		else
			$dataType = $type;
		
		$dataType = $dataType.'Field';
		
		return $dataType;
	}
}
?>
