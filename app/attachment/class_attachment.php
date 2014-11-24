<?php
/**
 * @file class_attachment.php
 * @brief Contiene la definizione ed implementazione del controller del modulo attachment
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Attachment;

use \Gino\View;
use \Gino\HttpResponseView;

require_once('class.attachmentItem.php');
require_once('class.attachmentCtg.php');

/**
 * @brief Classe controller del modulo di gestione di archivi di file categorizzati
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class attachment extends \Gino\Controller {

  /**
   * @brief titolo del modulo
   */
  private $_title;

  /**
   * @brief numero di allegati mostrati per pagina
   */
  private $_items_for_page;

  /**
   * @brief estensioni associate a icone immagini
   */
  private $_img_extension;

  /**
   * @brief estensioni associate a icona spreadsheets
   */
  private $_xls_extension;

  /**
   * @brief estensioni associate a icona pdf
   */
  private $_pdf_extension;

  /**
   * @brief estensioni associate a icona doc
   */
  private $_doc_extension;
  /*
   * Parametro action letto da url 
   */
  private $_action;

  /*
   * Parametro block letto da url 
   */
  private $_block;

  function __construct(){

    parent::__construct();

    $this->_title = _('Allegati');
    $this->_items_for_page = 30;

    $this->_img_extension = array('jpg','jpeg','png','gif');
    $this->_xls_extension = array('xls','xlt','xlsx','csv','sxc','stc','ods','ots');
    $this->_doc_extension = array('doc','docx','odt','ott','sxw','stw','rtf','txt');
    $this->_pdf_extension = array('pdf');
    $this->_extension = array();

    $this->_action = \Gino\cleanVar($_REQUEST, 'action', 'string', '');
    $this->_block = \Gino\cleanVar($_REQUEST, 'block', 'string', '');

  }

	/**
	 *  @bries Percorso assoluto alla directory dei contenuti
	 *  
	 *  @return percorso assoluto alla directory dei contenuti
	 */
	public function getDataDir() {
		
		return $this->_data_dir;
	}
	
	/**
	 *  @bries Percorso relativo alla directory dei contenuti
	 *  
	 *  @return percorso relativo alla directory dei contenuti
	 */
	public function getDataWWW() {
		
		return $this->_data_www;
	}

	/**
	 * @brief Avvia il downolad il un allegato
	 *
	 * @return void
	 */
	public function downloader(){

		$doc_id = \Gino\cleanVar($_GET, 'id', 'int', '');
		$db = \Gino\db::instance();

		if($doc_id) {

			$rows = $db->select('category, file', attachmentItem::$tbl_item, "id='$doc_id'", array('limit'=>array(0, 1)));
			if($rows and count($rows)) {
				$category = $rows[0]['category'];
				$ctg = new attachmentCtg($category, $this);
				$filename = $rows[0]['file'];
				$full_path = $ctg->path('abs').$filename;

				\Gino\download($full_path);
				exit();
			}
		}

		exit();
	}

  /**
   * @brief interfaccia backoffice del modulo
   * 
   * @param object $request oggetto \Gino\HttpRequest
   * @return \Gino\HttpResponse backend di amministrazione del modulo
   */
  public function manageAttachment(\Gino\HttpRequest $request) {

    $this->requirePerm('can_admin');

    \Gino\Loader::import('class', '\Gino\AdminTable');

    $method = 'manageAttachment';
    $link_ctg = "<a href=\"".$this->_home."?evt[$this->_instance_name-$method]&block=ctg\">"._("Categorie")."</a>";
    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_instance_name."-$method]\">"._("File")."</a>";
    $sel_link = $link_dft;

    // Variables
    $id = \Gino\cleanVar($_GET, 'id', 'int', '');
    $start = \Gino\cleanVar($_GET, 'start', 'int', '');
    // end

    if($this->_block == 'ctg') {
      $backend = $this->manageCategory();
      $sel_link = $link_ctg;
    }
    else {
      $backend = $this->manageItem();
    }
    
	if(is_a($backend, '\Gino\HttpResponse')) {
		return $backend;
	}

    $dict = array(
      'title' => $this->_title,
      'links' => array($link_ctg, $link_dft),
      'selected_link' => $sel_link,
      'content' => $backend
    );

    $view = new View(null, 'tab');
    return new HttpResponseView($view, $dict);
  }

  /**
   * @brief Interfaccia di amministrazione delle categorie
   * @return backoffice categorie
   */
  private function manageCategory() {

    require_once('class.attachmentCtgAdminTable.php');

    $admin_table = new attachmentCtgAdminTable($this, array());

    $buffer = $admin_table->backOffice(
      'AttachmentCtg', 
      array(
        'list_display' => array('id', 'name', 'directory'),
        'list_title' => _("Elenco categorie"), 
        'list_description' => "<p>"._('Ciascuna allegato inserito dovrà essere associato ad una categoria qui definita.')."</p>" .
                              "<p>"._('L\'eliminazione di una categoria comporta l\'eliminazione di tutti gli allegati associati!')."</p>"
      ),
      array(
        'removeFields' => array('directory')
      ),
      array()
    );

    return $buffer;

  }

	/**
	 * @brief Interfaccia di amministrazione dei file
	 * 
	 * @see attachmentItemAdminTable::backOffice()
	 * @return backoffice file
	*/
	private function manageItem() {

		require_once('class.attachmentItemAdminTable.php');

		$admin_table = new attachmentItemAdminTable($this, array());

		$buffer = $admin_table->backOffice(
			'AttachmentItem', 
			array(
				'list_display' => array('id', 'category', 'file', 'notes', 'last_edit_date'),
				'list_title' => _("Elenco files"), 
				'list_description' => "<p>"._("Per inserire un link all'allegato utilizzare il valore della colonna \"URL relativo\", per farne efettuare il download utilizzare il valore della colonna \"URL download\"")."</p>",
				'filter_fields' => array('category', 'notes')
			),
			array(),
			array()
		);

		return $buffer;
	}

  /**
   * @brief Interfaccia per l'inserimento di allegati all'interno dell'edito CKEDITOR
   * @return lista allegati
   */
  public function editorList() {

    $myform = \Gino\Loader::load('Form', array('attachment_list', 'post', false, array('tblLayout'=>false)));

    $ctgs = attachmentCtg::getForSelect($this);

    $onchange = "gino.ajaxRequest('post', '".$this->_home."?pt[".$this->_class_name."-editorAttachmentList]', 'ctg_id='+$(this).value, 'attachment_table', {'load': 'attachment_table'})";
    $buffer = "
      <p class=\"attachment-filter-ctg\">
        <label for=\"attachment_ctg\">"._('Seleziona la categoria ')."</label>
        ".$myform->select('attachment_ctg', '', $ctgs, array(
          'id' => 'attachment_ctg', 
          'js' => "onchange=\"$onchange\"",
          'noFirst' => true,
          'firstVoice' => _('tutte le categorie'),
          'firstValue' => 0
        ))."
        <span class=\"right link\" onclick=\"$('attachment-list-help').toggleClass('hidden')\">".pub::icon('help', array('text'=>_('informazioni'), 'scale'=>2))."</span>
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

    $buffer .= "<div id=\"attachment_table\">".$this->editorAttachmentList()."</div>";

    return $buffer;

  }

  /**
   * @brief Tabella di allegati con funzionalità per i drag and drop all'interno di CKEDITOR
   * @return tabella allegati
   */
  public function editorAttachmentList() {

    $ctg_id = \Gino\cleanVar($_POST, 'ctg_id', 'int', '');

    if($ctg_id) {
      $where = "category='$ctg_id'";
    }
    else {
      $where = null;
    }

    $items = AttachmentItem::get($this, array('where' => $where));

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
        $drag_view = "<img src=\"".$item->path('view')."\" />";
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

    return $buffer;

  }

  public function jsonImageList()
  {
      $items = AttachmentItem::get($this);
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

      header('Content-Type: application/json');
      echo json_encode($images);
      exit;
  }
}

?>