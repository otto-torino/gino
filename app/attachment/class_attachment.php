<?php
/**
 * @file class_attachment.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Attachment.attachment
 *
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */


/**
 * @namespace Gino.App.Attachment
 * @description Namespace dell'applicazione Attachment, che gestisce allegati categorizzati
 */
namespace Gino\App\Attachment;

use \Gino\View;
use \Gino\Loader;
use \Gino\Document;
use \Gino\Http\Response;

require_once('class.AttachmentItem.php');
require_once('class.AttachmentCtg.php');

/**
 * @brief Classe controller del modulo di gestione di archivi di file categorizzati
 *
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class attachment extends \Gino\Controller {

    /**
     * @brief titolo del modulo
     */
    private $_title;

    function __construct(){

        parent::__construct();

        $this->_title = _('Allegati');
    }

    /**
     *  @brief Percorso assoluto alla directory dei contenuti
     *  @return percorso assoluto
     */
    public function getDataDir() {

        return $this->_data_dir;
    }

    /**
     *  @brief Percorso relativo alla directory dei contenuti
     *  @return percorso relativo
     */
    public function getDataWWW() {

        return $this->_data_www;
    }
    
    /**
     * @brief Percorso della directory di una categoria di allegati
     *
     * @param integer $ctg_id valore id della categoria
     * @return percorso
     */
    public function getPath($ctg_id) {

    	if($ctg_id) {
    		$ctg = new AttachmentCtg($ctg_id);
    		$directory = $ctg->path('abs');
    	}
    	else {
    		$directory = $this->getDataDir().OS;
    	}
    	
    	return $directory;
    }

    /**
     * @brief Downolad il un allegato
     * @throws Gino.Exception.Exception404 se l'allegato non è recuperabile
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.ResponseFile
     */
    public function downloader(\Gino\Http\Request $request){

        $doc_id = \Gino\cleanVar($request->GET, 'id', 'int', '');
        $db = \Gino\db::instance();

        if($doc_id) {
            $rows = $db->select('category, file', AttachmentItem::$table, "id='$doc_id'", array('limit'=>array(0, 1)));
            if($rows and count($rows)) {
                $category = $rows[0]['category'];
                $ctg = new attachmentCtg($category, $this);
                $filename = $rows[0]['file'];
                $full_path = $ctg->path('abs').$filename;

                return \Gino\download($full_path);
            }
        }
        else {
            throw new \Gino\Exception\Exception404();
        }

    }

    /**
     * @brief Interfaccia backoffice del modulo
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response backend di amministrazione del modulo
     */
    public function manageAttachment(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        \Gino\Loader::import('class', '\Gino\AdminTable');

        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('File'));
        $link_ctg = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=ctg'), _('Categorie'));
        $sel_link = $link_dft;

        $id = \Gino\cleanVar($request->GET, 'id', 'int');
        $start = \Gino\cleanVar($request->GET, 'start', 'int');
        $block = \Gino\cleanVar($request->GET, 'block', 'string');

        if($block == 'ctg') {
            $backend = $this->manageCategory($request);
            $sel_link = $link_ctg;
        }
        else {
            $backend = $this->manageItem($request);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $dict = array(
            'title' => $this->_title,
            'links' => array($link_ctg, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $view = new View(null, 'tab');
        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione delle categorie
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response o html form
     */
    private function manageCategory($request) {

        $admin_table = new \Gino\AdminTable($this, array());

        $backend = $admin_table->backOffice(
            'AttachmentCtg',
            array(
                'list_display' => array('id', 'name', 'directory'),
                'list_title' => _("Elenco categorie"),
                'list_description' => "<p>"._('Ciascuna allegato inserito dovrà essere associato ad una categoria qui definita.')."</p>" .
                                      "<p>"._('L\'eliminazione di una categoria comporta l\'eliminazione di tutti gli allegati associati!')."</p>"
            ),
            array(),
            array()
        );

        return $backend;

    }

    /**
     * @brief Interfaccia di amministrazione dei file
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response o html form
    */
    private function manageItem() {

        require_once('class.AttachmentItemAdminTable.php');

        $admin_table = new AttachmentItemAdminTable($this, array());

        $buffer = $admin_table->backOffice(
            'AttachmentItem',
            array(
                'list_display' => array('id', 'category', 'file', 'notes', 'last_edit_date', 
                	array('label' => _('URL relativo'), 'member' => 'pathView'), 
                	array('label' => _('URL download'), 'member' => 'pathDownload'), 
                	array('label' => '', 'member' => 'previewAdminList')
                ),
                'list_title' => _("Elenco files"),
                'list_description' => "<p>"._("Per inserire un link all'allegato utilizzare il valore della colonna \"URL relativo\", per farne effettuare il download utilizzare il valore della colonna \"URL download\"")."</p>",
                'filter_fields' => array('category', 'file', 'notes'),
            	'list_display_options' => array('file' => array('maxchars' => 30), 'pathView' => array('maxchars' => null))
            ),
            array(),
            array()
        );

        return $buffer;
    }

    /**
     * @brief Interfaccia per l'inserimento di allegati all'interno dell'editor CKEDITOR
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response, lista allegati
     */
    public function editorList(\Gino\Http\Request $request) {

        $ctgs = AttachmentCtg::getForSelect();

        $onchange = "gino.ajaxRequest('post', '".$this->_home."?evt[".$this->_class_name."-editorAttachmentList]', 'ctg_id='+$(this).value, 'attachment_table', {'load': 'attachment_table'})";
        $buffer = "
            <p class=\"attachment-filter-ctg\">
                <label for=\"attachment_ctg\">"._('Seleziona la categoria ')."</label>
                ".\Gino\Input::select('attachment_ctg', '', $ctgs, array(
                    'id' => 'attachment_ctg',
                    'js' => "onchange=\"$onchange\"",
                    'noFirst' => TRUE,
                    'firstVoice' => _('tutte le categorie'),
                    'firstValue' => 0
                ))."
                <span class=\"right link\" onclick=\"$('attachment-list-help').toggleClass('hidden')\">".\Gino\icon('help', array('text'=>_('informazioni'), 'scale'=>2))."</span>
            </p>";

        $buffer .= "<div id=\"attachment-list-help\" class=\"hidden\">";
        $buffer .= "<p>"._('Puoi effettuare il "drag and drop" degli allegati direttamente dentro all\'editor:')."</p>";
        $buffer .= "
            <dl>
                <dt><b>"._('Drag vista elemento')."</b></dt>
                <dd><i>"._('Nel caso delle immagini viene effettuato il drag and drop dell\'immagine nelle sue dimensioni originali, per altri tipi di file viene creato un link che punta alla risorsa senza download forzato, si può scegliere tra link testuale o con immagine del tipo di file')."</i></dd>
                <dt><b>"._('Drag link download')."</b></dt>
                <dd><i>"._('Viene creato un link che porta al download forzato della risorsa, si può scegliere tra link testuale o con immagine del tipo di file')."</i></dd>
            </dl>
        ";
        $buffer .= "</div>";

        $editor_attachment_list = $this->editorAttachmentList($request);

        $buffer .= "<div id=\"attachment_table\">".$editor_attachment_list->getContent()."</div>";

        return new Response($buffer);
    }

    /**
     * @brief Tabella di allegati con funzionalità per i drag and drop all'interno di CKEDITOR
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response tabella allegati
     */
    public function editorAttachmentList(\Gino\Http\Request $request) {

        $ctg_id = \Gino\cleanVar($request->POST, 'ctg_id', 'int', '');
        $where = $ctg_id ? "category='$ctg_id'" : null;

        $items = AttachmentItem::objects(null, array('where' => $where));

        $buffer = "
            <table class=\"table table-striped table-bordered\">
                <tr>
                    <th>"._('Categoria')."</th>
                    <th>"._('File')."</th>
                    <th>"._('Note')."</th>
                    <th>"._('Drag vista elemento')."</th>
                    <th>"._('Drag link download')."</th>
                    </tr>";
        foreach($items as $item) {
            if($item->type() == 'img') {
                $drag_view = "<img src=\"".$item->path('view')."\" class=\"img-responsive\" />";
                $icon = "<img src=\"".$this->_class_img."/mark_IMG.jpg\" alt=\"".$item->file."\" title=\"".$item->file."\" />";
                $drag_download = "<a href=\"".$item->path('download')."\">".$icon."</a>" . " " . "<a href=\"".$item->path('download')."\">".\Gino\htmlChars($item->file)."</a>";
            }
            else {
                if($item->type() == 'doc') {
                    $icon = "<img src=\"".$this->_class_img."/mark_DOC.jpg\" alt=\"".$item->file."\" title=\"".$item->file."\" />";
                }
                elseif($item->type() == 'xls') {
                    $icon = "<img src=\"".$this->_class_img."/mark_XLS.jpg\" alt=\"".$item->file."\" title=\"".$item->file."\" />";
                }
                elseif($item->type() == 'pdf') {
                    $icon = "<img src=\"".$this->_class_img."/mark_PDF.jpg\" alt=\"".$item->file."\" title=\"".$item->file."\" />";
                }
                else {
                    $icon = "<img src=\"".$this->_class_img."/mark_FILE.jpg\" alt=\"".$item->file."\" title=\"".$item->file."\" />";
                }

                $drag_view = "<a href=\"".$item->path('view')."\">".$icon."</a>" . " " . "<a href=\"".$item->path('view')."\">".$item->file."</a>";
                $drag_download = "<a href=\"".$item->path('download')."\">".$icon."</a>" . " " . "<a href=\"".$item->path('download')."\">".\Gino\htmlChars($item->file)."</a>";
            }

            $ctg = new AttachmentCtg($item->category, $this);
            $buffer .= "
                <tr>
                    <td>".\Gino\htmlChars($ctg->name)."</td>
                    <td>".$item->previewLink('path')."</td>
                    <td>".\Gino\htmlChars($item->notes)."</td>
                    <td class=\"drag-attachment-view\">".$drag_view."</td>
                    <td class=\"drag-attachment-download\">".$drag_download."</td>
                </tr>
            ";
        }
        $buffer .= "
            </table>";

        return new Response($buffer);

    }

    /**
     * @brief Lista immagini in formato json
     * @description Il json restituito è nella forma
     *              array(0 => array('image' => image_rel_path, 'folder' => ctg_name), 1 => ...)
     * @return Gino.Http.ResponseJson
     */
    public function jsonImageList()
    {
        $items = AttachmentItem::objects($this);
        $images = array();
        foreach($items as $item) {
            if(preg_match('#(\.png|\.jpg|\.jpeg|\.tif|\.gif)$#', $item->file)) {
                $ctg = new AttachmentCtg($item->category, $this);
                $images[] = array(
                    'image' => $item->path('rel'),
                    'folder' => $ctg->name
                );
            }
        }

        $response = Loader::load('http/ResponseJson', array($images), '\Gino\Http\\');
        return $response;
    }
}
