<?php

class ModuleApp extends Model {

  public static $table = TBL_MODULE_APP;

  function __construct($id) {

    $this->_tbl_data = self::$table;
    parent::__construct($id);

  }

  function __toString() {
    return $this->label;
  }

  public function className() {
    return $this->name;
  }

  public static function get($options = array()) {

    $where = gOpt('where', $options, null);
    $order = gOpt('order', $options, null);

    $res = array();

    $db = db::instance();
    $rows = $db->select('id', self::$table, $where, array('order' => $order));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new ModuleApp($row['id']);
      }
    }

    return $res;

  }

  public static function getFromName($name) {

    $db = db::instance();
    $rows = $db->select('id', self::$table, "name='$name'");
    if($rows and count($rows)) {
      return new ModuleApp($rows[0]['id']);
    }

    return null;

  }


}
