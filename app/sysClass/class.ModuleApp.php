<?php
namespace Gino\App\SysClass;

class ModuleApp extends \Gino\Model {

  public static $table = TBL_MODULE_APP;

  function __construct($id) {

    $this->_tbl_data = self::$table;
    parent::__construct($id);

  }

	function __toString() {
		return $this->label;
	}

	/**
	 * Nome della classe
	 * 
	 * @param boolean $ns mostra o meno il nome della classe completo di namespace (default true)
	 * @return string
	 */
	public function className($ns=true) {
		
		$ns = $ns ? get_app_mamespace($this->name).'\\' : '';
		
		$class = $ns.$this->name;
		
		return $class;
	}

  public static function get($options = array()) {

    $where = \Gino\gOpt('where', $options, null);
    $order = \Gino\gOpt('order', $options, null);

    $res = array();

    $db = \Gino\db::instance();
    $rows = $db->select('id', self::$table, $where, array('order' => $order));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new ModuleApp($row['id']);
      }
    }

    return $res;
  }

  public static function getFromName($name) {

    $db = \Gino\db::instance();
    $rows = $db->select('id', self::$table, "name='$name'");
    if($rows and count($rows)) {
      return new ModuleApp($rows[0]['id']);
    }

    return null;
  }
}
