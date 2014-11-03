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
  protected $_m2m = array(),
            $_m2mt = array();

  protected $_is_constraint = array();
  protected $_check_is_constraint = true;
	
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

    $this->_trd = new translation($this->_lng_nav, $this->_lng_dft);

	}
	
 	public function __toString() {

		return $this->id;
	}

	public function fieldLabel($field) {

		return isset($this->_fields_label[$field]) ? $this->_fields_label[$field] : $field;
	}
	
	protected function setCheckIsConstraint($check) {
		$this->_check_is_constraint = (bool) $check;
	}

	public function setIsConstraint($is_constraint) {
		$this->_is_consraint = $is_constraint;
	}

	/**
	 * Metodo richiamato ogni volta che qualcuno prova a ottenere una proprietà dell'oggetto
	 * 
	 * L'output è il metodo get specifico per questa proprietà (se esiste), altrimenti è la proprietà
	 * 
	 * @param string $pName
	 */
	public function &__get($pName) {
		$null = null;
		if(!array_key_exists($pName, $this->_p) and !array_key_exists($pName, $this->_m2m) and !array_key_exists($pName, $this->_m2mt)) return $null;
		elseif(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		elseif(array_key_exists($pName, $this->_p)) return $this->_p[$pName];
		elseif(array_key_exists($pName, $this->_m2m)) return $this->_m2m[$pName];
		elseif(array_key_exists($pName, $this->_m2mt)) return $this->_m2mt[$pName];
		else return $null;
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

		if(!array_key_exists($pName, $this->_p) and !array_key_exists($pName, $this->_m2m)) return null;
		elseif(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($pValue);
		elseif(array_key_exists($pName, $this->_p)) {
			if($this->_p[$pName] !== $pValue && !in_array($pName, $this->_chgP)) $this->_chgP[] = $pName;
			$this->_p[$pName] = $pValue;
		}
		elseif(array_key_exists($pName, $this->_m2m)) {
			$this->_m2m[$pName] = $pValue;
		}
		elseif(array_key_exists($pName, $this->_structure) and get_class($this->_structure[$pName]) == 'ManyToManyThroughField') {
			$this->_m2mt[$pName] = $pValue;
		}
	}

  /**
   * Aggiunge un m2m al modello
   */
  public function addm2m($field, $value) {
    $this->_m2m[$field] = $value;
  }

  /**
   * Aggiunge un m2m through al modello
   */
  public function addm2mthrough($field, $value) {
    $this->_m2mt[$field] = $value;
  }

  /**
   * Ritorna l'oggetto m2m through model
   * @param string $m2mt_field nome della relazione m2mt
   * @param int $id id del record
   * @return oggetto
   */
  public function m2mtObject($m2mt_field, $id) {
    $field_obj = $this->_structure[$m2mt_field];
    $class = $field_obj->getM2m();
    return new $class($id, $field_obj->getController());
  }

	/**
	 * Array associativo id => rappresentazione a stringa a partire da array di oggetti
	 * @param array $objects
	 * @return array associativo id=>stringa
	 */
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
   * Metodo generico statico per ricavare oggetti
   */
  public static function objects($controller = null, $options = array()) {

    $where = isset($options['where']) ? $options['where'] : null;
    $order = isset($options['order']) ? $options['order'] : null;
    $limit = isset($options['limit']) ? $options['limit'] : null;

    $res = array();
    $db = db::instance();
    $rows = $db->select('id', static::$table, $where, array('order'=>$order, 'limit'=>$limit));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = $controller ? new static($row['id'], $controller) : new static($row['id']);
      }
    }

    return $res;
  }

	/**
	 * Salva i cambiamenti fatti sull'oggetto modificando o inserendo un nuovo record su DB
	 *
	 * @return boolean
	 */
	public function updateDbData() {

		$result = true;
		
		if($this->_p['id']) { 
			if(sizeof($this->_chgP)) {
				$fields = array();
				foreach($this->_chgP as $pName) $fields[$pName] = $this->_p[$pName];
				$result = $this->_db->update($fields, $this->_tbl_data, "id='{$this->_p['id']}'");
			}
		}
		else {
			if(sizeof($this->_chgP)) {
				$fields = array();
				foreach($this->_chgP as $pName) 
				{
					if(!($pName == 'id' and $this->id === null))
						$fields[$pName] = $this->_p[$pName];
        		}
				$result = $this->_db->insert($fields, $this->_tbl_data);
			}
		}
		
		if(!$result) {
			return array('error'=>9);
		}

		if(!$this->_p['id']) $this->_p['id'] = $this->_db->getlastid($this->_tbl_data);
		
		$result = $this->savem2m();

		return $result;
	}
	
	/**
	 * Salva i ManyToMany
	 * 
	 * @return boolean
	 */
 	public function savem2m() {
		
		foreach($this->_m2m as $field => $values) {
			$obj = $this->_structure[$field];
			if(get_class($obj) == 'ManyToManyField') {
				$this->_db->delete($obj->getJoinTable(), $obj->getJoinTableId()."='".$this->id."'");
				foreach($values as $fid) {
					$this->_db->insert(array(
						$obj->getJoinTableId() => $this->id,
						$obj->getJoinTableM2mId() => $fid
						), $obj->getJoinTable()
					);
				}
			}
		}
		return true;
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

    // check constraints
    if($this->_check_is_constraint and count($this->_is_constraint)) {
      $res = $this->checkIsConstraint();
      if($res !== true) {
        return array("error"=>$this->isConstraintError($res));
      }
    }

		$this->deletem2m();
		$this->deletem2mthrough();

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

  protected function isConstraintError($res) {
    $html = "<p>"._("Il record che si intende eliminare compare come riferimento nei seguenti oggetti").":</p>";
    $html .= "<ul>";
    foreach($res as $model => $records) {
      $html .= "<li>".$model."</li>";
      $html .= "<ul>";
      foreach($records as $record) {
        $html .= "<li>".$record."</li>";
      }
      $html .= "</ul>";
    }
    $html .= "</ul>";

    return $html;
  }

  protected function checkIsConstraint() {

    $res = array();
    $db = db::instance();

    foreach($this->_is_constraint as $model=>$field) {

      $an_objs = $model::get(null, null, array(0,1));
      $an_obj = count($an_objs) ? $an_objs[0] : null;
      $model_label = $an_obj ? $an_obj->getModelLabel() : '';
      if($an_obj and $an_obj->id) {
        if(isset($an_obj->_structure[$field]) and is_a($an_obj->_structure[$field], 'ManyToManyField')) {
          $field_obj = $an_obj->_structure[$field];
          $table = $field_obj->getJoinTable();
          $id_string = $field_obj->getJoinTableId();
          $m2m_id_string = $field_obj->getJoinTableM2mId();
          $rows = $db->select($id_string, $table, $m2m_id_string."='".$this->id."'");
          if($rows and count($rows)) {
            if(!isset($res[$model_label])) {
              $res[$model_label] = array();
            }
            foreach($rows as $row) {
              $obj = new $model($row[$id_string]);
              $res[$model_label][] = (string) $obj . ' - '._('m2m:').' '.$obj->fieldLabel($field);
            }
          }
        }
      }
      $objs = $model::get($field."='".$this->id."'");
      if($objs and count($objs)) {
        if(!isset($res[$model_label])) {
          $res[$model_label] = array();
        }
        foreach($objs as $obj) {
          $res[$model_label][] = (string) $obj.' - '._('campo:').' '.$obj->fieldLabel($field);
        }
      }
    }

    return count($res) ? $res : true;
  }

  public function deletem2m() {
    $result = true;
    foreach($this->_structure as $field => $obj) {
      if(get_class($obj) == 'ManyToManyField') {
        $result = $result and $this->_db->delete($obj->getJoinTable(), $obj->getJoinTableId()."='".$this->id."'");
      }
    }
    return $result;
  }

  public function deletem2mthrough() {
    $result = true;
    foreach($this->_structure as $field => $obj) {
      if(get_class($obj) == 'ManyToManyThroughField') {
        $result = $result and $this->deletem2mthroughField($field);
      }
    }
    return $result;
  }

  public function deletem2mthroughField($field_name) {
    $obj = $this->_structure[$field_name];
    $class = $obj->getM2m();
    foreach($this->_m2mt[$field_name] as $id) {
      $m2m_obj = new $class($id, $obj->getController());
      $m2m_obj->delete();
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
					'model'=>$this,
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
				);
				
				$structure[$key] = loader::load('fields/'.$dataType, array($options_field));
				/*if(!class_exists($dataType))
					error::syserrorMessage('Model', 'structure', sprintf(_("Il tipo di dato del campo %s non è riconoscibile automaticamente"), $key));
				
				$structure[$key] = new $dataType($options_field);*/
			}
		}
		return $structure;
	}

  /**
   * Update della struttura da chiamare manualmente quando ad esempio si modificano gli m2mt e si vogliono vederne gli effetti prima del ricaricamento pagina
   * Modificando gli m2mt, questi vengono aggiornati sul db, ma il modello che ha tali m2mt continua a referenziare i vecchi, questo perché il salvataggio
   * viene gestito da AdminTable e non da modello stesso che quindi ne è quasi all'oscuro. Ora questo metodo viene anche chiamato da AdminTable e quindi
   * le modifiche si riflettono immediatamente anche sul modello. Chiamarlo manualmente se la modifica agli m2mt viene fatta in modo diverso dall'uso del
   * metodo modelAction di AdminTable
   */
  public function updateStructure() {
    $this->_structure = $this->structure($this->id);
  }
	
 	/**
	 * Uniforma il tipo di dato di un campo definito dal metodo DbManager::getTableStructure() 
	 * e ritorna il nome della classe che gestisce il modello del tipo di campo
	 * 
	 * @param string $type tipo di dato
	 * @return string
	 */
	private function dataType($type) {
		
		if($type == 'tinyint' || $type == 'smallint' || $type == 'int' || $type == 'mediumint' || $type == 'bigint')
		{
			$dataType = 'integer';
		}
		elseif($type == 'float' || $type == 'double' || $type == 'decimal' || $type == 'numeric')
		{
			$dataType = 'float';
		}
		elseif($type == 'mediumtext' || $type == 'longtext')
		{
			$dataType = 'text';
		}
		elseif($type == 'varchar')
		{
			$dataType = 'char';
		}
		else
		{
			$dataType = $type;
		}
		
		$dataType = ucfirst($dataType).'Field';
		
		return $dataType;
	}
}
?>