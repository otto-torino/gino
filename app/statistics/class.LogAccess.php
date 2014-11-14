<?php
namespace Gino\App\Statistics;

class LogAccess extends \Gino\Model {

  public static $table = TBL_LOG_ACCESS;

  function __construct($id) {
    $this->_tbl_data = TBL_LOG_ACCESS;
    parent::__construct($id);
  }

  public static function getCountForUser($user_id) {

    $db = \Gino\db::instance();
    return $db->getNumRecords(self::$table, "user_id='$user_id'");

  }

  public static function get($options = array()) {

    $where = \Gino\gOpt('where', $options, null);
    $order = \Gino\gOpt('order', $options, null);

    $res = array();

    $db = \Gino\db::instance();
    $rows = $db->select('id', self::$table, $where, array('order' => $order));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new LogAccess($row['id']);
      }
    }

    return $res;

  }

  public static function getLastForUser($user_id) {

    $res = null;

    $db = \Gino\db::instance();
    $rows = $db->select('id', self::$table, "user_id='$user_id'", array('order'=>'date DESC', 'limit' => array(1, 1)));
    if($rows and count($rows)) {
      $res = new LogAccess($rows[0]['id']);
    }

    return $res;

  }

}
