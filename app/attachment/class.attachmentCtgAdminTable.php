<?php
/**
 * @file class.attachmentCtgAdminTable.php
 * @brief Contiene la classe attachmentCtgAdminTable
 *
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Attachment;

/**
 * @brief Estende la classe adminTable base di Gino per permettere la creazione, modifica ed eliminazione di cartelle su filesystem
 */
class attachmentCtgAdminTable extends \Gino\AdminTable {

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
        $attachment_controller = new attachment();
        mkdir($attachment_controller->getDataDir().OS.$model->directory);
      }

    }

    return true;

  }

  /**
   *  @brief esegue le azioni del parent e provvede poi all'eliminazione di tutti i record dei file della categoria e della directory su filesystem
   * @see adminTable::adminDelete
   */
  public function adminDelete($model, $options_form) {

    AttachmentItem::deleteFromCtg($model->id, $this->_controller);

    $attachment_controller = new attachment();
    $attachment_controller->deleteFileDir($attachment_controller->getDataDir().OS.$model->directory, true);

    parent::adminDelete($model, $options_form);

  }


}

?>
