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
	 * @param boolean $ns mostra o meno il nome della classe completo di namespace (default true)
	 * @return string
	 */
	public function className($ns=true) {
		
		$module_app = $this->moduleApp();
		
		$ns = $ns ? get_app_mamespace($module_app->name).'\\' : '';
		
		$class = $ns.$module_app->name;
		
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
        $res[] = new ModuleInstance($row['id']);
      }
    }

    return $res;
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
		
		\Gino\Loader::import('sysClass', '\Gino\App\SysClass\ModuleApp');
		return new ModuleApp($this->module_app);
	}
}
