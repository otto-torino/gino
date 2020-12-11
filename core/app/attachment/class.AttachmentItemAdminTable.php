<?php
/**
 * @file class.AttachmentItemAdminTable.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Attachment.AttachmentItemAdminTable
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Attachment;

/**
 * @brief Estende la classe Gino.AdminTable per gestire il salvataggio dell'allegato quando viene modificata la directory (categoria)
 * @see Gino.AdminTable
 */
class AttachmentItemAdminTable extends \Gino\AdminTable {

    /**
     * @brief rispetto al parent consente di salvare il file all'interno della directory selezionata
     * @see Gino.AdminTable::modelAction()
     */
    public function modelAction($model, $options=array(), $options_element=array()) {

        $request = \Gino\Http\Request::instance();
        
        $ctg = new AttachmentCtg($request->POST['category']);

        if($model->id and $model->category != $ctg->id) {
            $old_ctg = new AttachmentCtg($model->category);
            $filename = $request->POST['old_file'];
            if(!$request->FILES['file']['name'] or $request->FILES['file']['name'] == $request->POST['old_file']) {
                @rename($old_ctg->path('abs').$filename, $ctg->path('abs').$filename);
            }
            else {
                @unlink($old_ctg->path('abs').$filename);
            }
        }

        return parent::modelAction($model, $options, $options_element);
    }

}
