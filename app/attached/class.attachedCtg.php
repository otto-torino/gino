<?php
/**
 * \file class.attachedCtg.php
 * @brief Contiene la definizione ed implementazione della classe AttachedCtg.
 * 
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * Classe tipo model che rappresenta una categoria di allegati.
 *
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class AttachedCtg extends Model {

  public static $tbl_ctg = 'attached_ctg';

  /**
   * Costruttore
   *
   * @param integer $id valore ID del record
   * @param object $instance istanza del controller
   */
  function __construct($id, $instance) {

    $this->_controller = $instance;
    $this->_tbl_data = self::$tbl_ctg;

    $this->_fields_label = array(
      'name'=>_("Nome"),
      'directory'=>_("Nome directory")
    );

    parent::__construct($id);

    $this->_model_label = $this->id ? $this->name : '';
  }

  /**
   * @brief Cast a stringa del modello
   * @return rappresentazione a stringa del modello
   */
  function __toString() {

    return $this->name;

  }

  /**
   * Sovrascrive la struttura di default
   *
   * @see propertyObject::structure()
   * @param integer $id
   * @return array
   */
  public function structure($id) {

    $structure = parent::structure($id);

    $structure['directory'] = new hiddenField(array(
      'name'=>'directory',
      'model'=>$this,
    ));

    return $structure;

  }

  /**
   * Restituisce oggetti di tipo attachedCtg
   * 
   * @param news $controller istanza del controller
   * @param array $options array associativo di opzioni (where, order e limit)
   * @return array di istanze di tipo attachedCtg
   */
  public static function get($controller, $options = null) {

    $res = array();

    $where = gOpt('where', $options, '');
    $order = gOpt('order', $options, 'name');
    $limit = gOpt('limit', $options, null);

    $db = db::instance();
    $selection = 'id';
    $table = self::$tbl_ctg;

    $rows = $db->select($selection, $table, $where, array('order'=>$order, 'limit'=>$limit));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new attachedCtg($row['id'], $controller);
      }
    }

    return $res;

  }

  /**
   * Array di categorie nella forma id=>name
   * 
   * @param news $controller istanza del controller
   * @param array $options array associativo di opzioni (where, order e limit)
   * @return array associativo id=>name
   */
  public static function getForSelect($controller, $options = null) {

    $res = array();

    $where = gOpt('where', $options, '');
    $order = gOpt('order', $options, 'name');
    $limit = gOpt('limit', $options, null);

    $db = db::instance();
    $selection = 'id, name';
    $table = self::$tbl_ctg;

    $rows = $db->select($selection, $table, $where, array('order'=>$order, 'limit'=>$limit));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[$row['id']] = htmlChars($row['name']);
      }
    }

    return $res;

  }


	/**
	 * @brief Percorso alla directory
	 * 
	 * @param string $type tipo di percorso:
	 *   - abs: assoluto
	 *   - rel: relativo alla DOCUMENT ROOT
	 *   - view: realtivo alla ROOT
	 *   - url: url assoluto
	 * @return string
	 */
	public function path($type) {

		$directory = '';

		if($type == 'abs') {
			$directory = $this->_controller->getDataDir().OS.$this->directory.OS;
		}
		elseif($type == 'rel') {
			$directory = $this->_controller->getDataWWW().'/'.$this->directory.'/';
		}
		elseif($type == 'view') {
			$directory = preg_replace("#^".preg_quote(SITE_WWW)."/#", "", $this->_controller->getDataWWW().'/'.$this->directory.'/');
		}
		elseif($type == 'url') {
			$directory = 'http://'.$_SERVER['HTTP_HOST'].SITE_WWW.$this->_controller->getDataWWW().'/'.$this->directory.'/';
		}

		return $directory;
	}
}

?>
