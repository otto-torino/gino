<?php

class ModuleInstance extends Model {

  public static $table = TBL_MODULE;

  function __construct($id) {

    $this->_tbl_data = self::$table;
    parent::__construct($id);

  }

  public function className() {
    $module_app = $this->moduleApp();
    return $module_app->name;
  }

  public static function get($options = array()) {

    $where = gOpt('where', $options, null);
    $order = gOpt('order', $options, null);

    $res = array();

    $db = db::instance();
    $rows = $db->select('id', self::$table, $where, array('order' => $order));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new ModuleInstance($row['id']);
      }
    }

    return $res;

  }

  public static function getFromName($name) {

    $db = db::instance();
    $rows = $db->select('id', self::$table, "name='$name'");
    if($rows and count($rows)) {
      return new ModuleInstance($rows[0]['id']);
    }

    return null;

  }

  public static function getFromModuleApp($module_app_id) {

    $res = array();

    $db = db::instance();
    $rows = $db->select('id', self::$table, "module_app='$module_app_id'");
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new ModuleInstance($row['id']);
      }
    }

    return $res;

  }

  public function moduleApp() {
    loader::import('sysClass', 'ModuleApp');
    return new ModuleApp($this->module_app);
  }


}
