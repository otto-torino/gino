<?php
namespace Gino\App\Module;
use \Gino\App\SysClass\ModuleApp;

class ModuleInstance extends \Gino\Model {

  public static $table = TBL_MODULE;

  function __construct($id) {

    $this->_tbl_data = self::$table;
    parent::__construct($id);

  }

	/**
	 * Nome della classe
	 * 
	 * @return string
	 */
	public function className() {
		
		$module_app = $this->moduleApp();
		return $module_app->name;
		
	}

    /**
	 * Nome della classe con namespace completo
	 * 
	 * @return string
	 */
	public function classNameNs($ns=true) {
		$module_app = $this->moduleApp();
		return get_app_name_class_ns($module_app->name);
	}

  public static function getFromName($name) {

    $db = \Gino\db::instance();
    $rows = $db->select('id', self::$table, "name='$name'");
    if($rows and count($rows)) {
      return new ModuleInstance($rows[0]['id']);
    }

    return null;
  }

  public static function getFromModuleApp($module_app_id) {

    $res = array();

    $db = \Gino\db::instance();
    $rows = $db->select('id', self::$table, "module_app='$module_app_id'");
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new ModuleInstance($row['id']);
      }
    }

    return $res;
  }

	public function moduleApp() {
		
		\Gino\Loader::import('sysClass', 'ModuleApp');
		return new ModuleApp($this->module_app);
	}
}
