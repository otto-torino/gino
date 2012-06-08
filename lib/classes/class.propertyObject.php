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
 * La classe figlia che costruisce la classe le passa (ad esempio) i valori del metodo initP:
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
}
?>
