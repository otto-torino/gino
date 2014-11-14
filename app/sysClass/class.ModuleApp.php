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
	 * @return string
	 */
	public function className() {
		
		return $this->name;
		
	}

    /**
	 * Nome della classe con namespace completo
	 * 
	 * @return string
	 */
	public function classNameNs($ns=true) {
		return get_app_name_class_ns($this->name);
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
