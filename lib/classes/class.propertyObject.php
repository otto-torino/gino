<?php
/*
 * This is a class that contains methods used by every other ckass which has DB properties.
 * - DB properties may be read through the __get function, but may also be protected by constructing 
 * a personalized get function in the class.
 * - DB properties are set through the __set method. But IMPORTANT:
 *   by default the __set method read the property from the POST array as a string!
 *   So if it's necessary to set the property giving a value and not ny a POST, or if
 *   the typecast is different from string it's necessary to implement the specific
 *   set method which is called before.
 *
 * Public Methods:
 * - construct($data)
 *   sons must construct this class passing an array containig the init properties. 
 *   Example:
 *   in the sons classes
 *   function __construct($id) {
 *     ...
 *     parent::__construct($this->initP($id)); 
 *     ...    
 *   }
 *
 *   function initP($id) {
 *     $query = "SELECT * FROM ".self::$_tbl_ctg." WHERE id='$id'";
 *     $a = $db->selectquery($query);
 *     if(sizeof($a)>0) return $a[0]; 
 *     else return array('id'=>null, 'name'=>null);
 *   }
 *
 * - __get($pName)
 *   method called every time someone tries to get an object property.
 *   If a specific getter method for this property exists then is returned, else
 *   the property is returned
 *
 * - __set($pName)    
 *   method called every time someone tries to set an object property.
 *   If a specific setter method for this poperty exists then is returned, else 
 *   the property is set reading the POST array and typecasting to string
 *
 * - ml($pName)
 *   method called to retrieve properties with translation
 *
 * - updateDbData()
 *   method called to save cjanges made to the object updating or insertinga new record in the DB
 *
 * - deleteDbData() 
 *   method called to delete DB properties of an object
 *
 * IMPORTANT
 *
 *  each class object stores his propetries in one table defined in $this->_tbl_data which is defined 
 *  here but setted in the sons classes
 *
 */
 abstract class propertyObject {

	protected $_db;
	protected $_tbl_data;
	protected $_p, $_chgP = array();
	
	protected $_lng_dft, $_lng_nav;
	private $_trd;

	function __construct($data) {

		$this->_db = db::instance();
		$this->_p = $data;
	
		$this->_lng_dft = $_SESSION['lngDft'];
		$this->_lng_nav = $_SESSION['lng'];
		$this->_trd = new translation($this->_lng_nav, $this->_lng_dft);

	}
	
	public function __get($pName) {
	
		if(!array_key_exists($pName, $this->_p)) return null;
		if(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		else return $this->_p[$pName];
	}
	
	public function __set($pName, $postLabel) {

		if(!array_key_exists($pName, $this->_p)) return null;
		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($postLabel);
		else {
			if($this->_p[$pName]!=cleanVar($_POST, $postLabel, 'string', null) && !in_array($pName, $this->_chgP)) $this->_chgP[] = $pName;
			$this->_p[$pName] = cleanVar($_POST, $postLabel, 'string', null);
		}
	}

	public function ml($pName) {
		
		return ($this->_trd->selectTXT($this->_tbl_data, $pName, $this->_p['id']));
	}

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

	public function deleteDbData() {
	
		language::deleteTranslations($this->_tbl_data, $this->_p['id']);
		$query = "DELETE FROM $this->_tbl_data WHERE id='{$this->_p['id']}'";
		return $this->_db->actionquery($query);
	}
}
?>
