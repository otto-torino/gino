<?php
/**
 * @file class.pageTag.php
 * Contiene la definizione ed implementazione della classe pageTag.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup page
 * Classe per la gestione di tag associati alle pagine.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pageTag extends propertyObject {

	private $_controller;
	public static $_tbl_tag = "page_tag";

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_tag;

		$this->_fields_label = array(
			'name'=>_("Nome")
		);

		parent::__construct($id);

		$this->_model_label = $this->id ? $this->name : '';
	}

	/**
	 * Rappresentazione testuale del modello 
	 * 
	 * @return string
	 */
	function __toString() {
		
		return $this->_model_label;
	}

	/**
	 * Restituisce l'oggetto a aprtire dal nome del tag 
	 * 
	 * @param string $name nome del tag
	 * @param object $instance istanza delle pagine
	 * @return istanza di pageTag
	 */
	public static function getFromName($name, $instance) {

		$db = db::instance();

		$res = null;
		$rows = $db->select(array('id'), self::$_tbl_tag, "name='".$name."'", null, null);
		if($rows and count($rows)) {
			$res = new pageTag($rows[0]['id'], $instance);
		}

		return $res;
	}

	/**
	 * Restituisce una lista di tutti i tag presenti nella tabella 
	 * 
	 * @param array $options opzioni
	 * @return array contenente tutti i tag ineriti nella tabella
	 */
	public static function getAllList($options) {

		$db = db::instance();

		$res = array();
		$rows = $db->select(array('id', 'name'), self::$_tbl_tag, "", 'name', null);
		if(count($rows)) {
			foreach($rows as $row) {
				if(gOpt('jsescape', $options, false)) {
					$name = jsVar($row['name']);
				}
				else {
					$name = htmlChars($row['name']);
				}
				$res[$row['id']] = $name;
			}
		}

		return $res;
	}

	/**
	 * Inserisce un tag se non ancora presente 
	 * 
	 * @param mixed $tag il tag da inserire
	 * @return id del record contenente il tag
	 */
	public static function saveTag($tag) {

		$db = db::instance();

		if($tag == '') return null;

		$rows = $db->select('id', self::$_tbl_tag, "name='$tag'", null);
		if(count($rows) && $rows) {
			return $rows[0]['id'];
		}
		else {
			$query = "INSERT INTO ".self::$_tbl_tag." (name) VALUES ('$tag')";
			$res = $db->actionquery($query);
			return $db->getlastid(self::$_tbl_tag);
		}
	}
}

?>
