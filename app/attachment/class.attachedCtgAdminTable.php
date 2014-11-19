<?php
/**
 * @file class.attachedCtgAdminTable.php
 * @brief Contiene la classe attachedCtgAdminTable
 *
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Attached;

/**
 * @brief Estende la classe adminTable base di Gino per permettere la creazione, modifica ed eliminazione di cartelle su filesystem
 */
class attachedCtgAdminTable extends \Gino\AdminTable {

  /**
   * @brief Costruttore
   * @see adminTable::__construct
   */
  function __construct($instance, $opts = array()) {
    parent::__construct($instance, $opts);
  }

  /**
   * @brief esegue le azioni del parent e poi crea una directory nel caso in cui sia stata inserita una nuova categoria
   * @see adminTable::modelAction
   */
  public function modelAction($model, $options=array(), $options_element=array()) {

    $insert = $model->id ? false : true;
    $result = parent::modelAction($model, $options, $options_element);

    if(is_array($result)) {
      return $result;
    }

    if($insert) {
      $model->directory = 'c'.$model->id;
      if($model->updateDbData()) {
        $attached_controller = new attached();
        mkdir($attached_controller->getDataDir().OS.$model->directory);
      }

    }

    return true;

  }

  /**
   *  @brief esegue le azioni del parent e provvede poi all'eliminazione di tutti i record dei file della categoria e della directory su filesystem
   * @see adminTable::adminDelete
   */
  public function adminDelete($model, $options_form) {

    attachedItem::deleteFromCtg($model->id, $this->_controller);

    $attached_controller = new attached();
    $attached_controller->deleteFileDir($attached_controller->getDataDir().OS.$model->directory, true);

    parent::adminDelete($model, $options_form);

  }


}

?>
