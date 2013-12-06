<?php
/**
 * \file class.user.php
 * Contiene la definizione ed implementazione della classe User.
 * 
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup auth
 * Classe tipo model che rappresenta un utente.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class User extends Model {

  public static $table = TBL_USER;

  /**
   * Costruttore
   * 
   * @param integer $id valore ID del record
   * @param object $instance istanza del controller
   */
  function __construct($id) {

    $this->_tbl_data = TBL_USER;
    parent::__construct($id);
  }

  /**
   * Restituisce lutente dati username e password
   * @param string $username lo username
   * @param string $password la password
   * @return mixed l'oggetto utente ricavato oppure null
   */
  public static function getFromUserPwd($username, $password) {

    $db = db::instance();

    $user = null;

    $registry = registry::instance();
    $crypt_method = $registry->pub->getConf('password_crypt');
    $password = $crypt_method ? $registry->pub->cryptMethod($password, $crypt_method) : $pwd;

    $rows = $db->select('id', TBL_USER, "username='$username' AND userpwd='$password' AND active='1'");
    if($rows and count($rows) == 1) {
      $user = new User($rows[0]['id']);
    }
    return $user;

  }

  public static function get($options = array()) {

    $where = gOpt('where', $options, null);
    $order = gOpt('order', $options, null);

    $res = array();

    $db = db::instance();
    $rows = $db->select('id', self::$table, $where, array('order' => $order));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new User($row['id']);
      }
    }

    return $res;

  }

  /**
   * Verifica se l'utente ha uno dei permessi della classe
   * @param string $class la classe
   * @param int|array $perms id o array di id dei permessi da verificare
   * @param int $instance istanza della classe (0 per classi non istanziabili)
   * @return boolean
   */
  public function hasPerm($class, $perms, $instance = 0) {

    if(!$this->id) {
      return false;
    }
    elseif($this->is_admin) {
      return true;
    }

    if(!is_array($perms)) {
      $perms = array($perms);
    }

    foreach($perms as $perm_code) {
      // check user permission
      $rows = $this->_db->select(TBL_USER_PERMISSION.'.perm_id', array(TBL_USER_PERMISSION, TBL_PERMISSION), array(
        TBL_USER_PERMISSION.'.perm_id' => TBL_PERMISSION.'.id',
        TBL_USER_PERMISSION.'.user_id' => $this->id,
        TBL_USER_PERMISSION.'.instance' => $instance,
        TBL_PERMISSION.'.code' => $perm_code
      ));
      if($rows and count($rows)) {
        return true;
      }
      // check user group permission
      $rows = $this->_db->select(TBL_GROUP_PERMISSION.'.perm_id', array(TBL_GROUP_PERMISSION, TBL_GROUP_USER, TBL_PERMISSION), array(
        TBL_GROUP_PERMISSION.'.perm_id' => TBL_PERMISSION.'.id',
        TBL_PERMISSION.'.code' => $perm_code,
        TBL_GROUP_PERMISSION.'.instance' => $instance,
        TBL_GROUP_PERMISSION.'.group_id' => TBL_GROUP_USER.'.group_id',
        TBL_GROUP_USER.'.user_id' => $this->id
      ));
      if($rows and count($rows)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Verifica se l'utente ha uno dei permessi amministrativi della classe
   * @param string $class la classe
   * @param int $instance istanza della classe (0 per classi non istanziabili)
   * @return boolean
   */
  public function hasAdminPerm($class, $instance = 0) {

    $perms = array();
    $rows = $this->_db->select('code', TBL_PERMISSION, "admin='1'");
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $perms[] = $row['code'];
      }
    }
    if($this->hasPerm($class, $instance, $perms)) {
      return true;
    }

    return false;

  }


}

