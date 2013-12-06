<?php
/**
 * @file class_attached.php
 * @brief Contiene la definizione ed implementazione del controller del modulo attached
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

require_once('class.attachedItem.php');
require_once('class.attachedCtg.php');

/**
 * @brief Classe controller del modulo di gestione di archivi di file categorizzati
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class attached extends Controller {

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

    $this->_action = cleanVar($_REQUEST, 'action', 'string', '');
    $this->_block = cleanVar($_REQUEST, 'block', 'string', '');

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

		$doc_id = cleanVar($_GET, 'id', 'int', '');
		$db = db::instance();

		if($doc_id) {

			$rows = $db->select('category, file', attachedItem::$tbl_item, "id='$doc_id'", array('limit'=>array(0, 1)));
			if($rows and count($rows)) {
				$category = $rows[0]['category'];
				$ctg = new attachedCtg($category, $this);
				$filename = $rows[0]['file'];
				$full_path = $ctg->path('abs').$filename;

				download($full_path);
				exit();
			}
		}

		exit();
	}

  /**
   * @brief interfaccia backoffice del modulo
   * @return interfaccia di backoffice
   */
  public function manageAttached() {

    $this->requirePerm('can_admin');

    Loader::import('class', 'AdminTable');

    $method = 'manageAttached';
    $link_ctg = "<a href=\"".$this->_home."?evt[$this->_instance_name-$method]&block=ctg\">"._("Categorie")."</a>";
    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_instance_name."-$method]\">"._("File")."</a>";
    $sel_link = $link_dft;

    // Variables
    $id = cleanVar($_GET, 'id', 'int', '');
    $start = cleanVar($_GET, 'start', 'int', '');
    // end

    if($this->_block == 'ctg') {
      $buffer = $this->manageCategory();
      $sel_link = $link_ctg;
    }
    else {
      $buffer = $this->manageItem();
    }

    $dict = array(
      'title' => $this->_title,
      'links' => array($link_ctg, $link_dft),
      'selected_link' => $sel_link,
      'content' => $buffer
    );

    $view = new view(null, 'tab');
    return $view->render($dict);

  }

  /**
   * @brief Interfaccia di amministrazione delle categorie
   * @return backoffice categorie
   */
  private function manageCategory() {

    require_once('class.attachedCtgAdminTable.php');

    $admin_table = new attachedCtgAdminTable($this, array());

    $buffer = $admin_table->backOffice(
      'attachedCtg', 
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
	 * @see attachedItemAdminTable::backOffice()
	 * @return backoffice file
	*/
	private function manageItem() {

		require_once('class.attachedItemAdminTable.php');

		$admin_table = new attachedItemAdminTable($this, array());

		$buffer = $admin_table->backOffice(
			'attachedItem', 
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

    $myform = Loader::load('Form', array('attached_list', 'post', false, array('tblLayout'=>false)));

    $ctgs = attachedCtg::getForSelect($this);

    $onchange = "gino.ajaxRequest('post', '".$this->_home."?pt[".$this->_class_name."-editorAttachedList]', 'ctg_id='+$(this).value, 'attached_table', {'load': 'attached_table'})";
    $buffer = "
      <p class=\"attached-filter-ctg\">
        <label for=\"attached_ctg\">"._('Seleziona la categoria ')."</label>
        ".$myform->select('attached_ctg', '', $ctgs, array(
          'id' => 'attached_ctg', 
          'js' => "onchange=\"$onchange\"",
          'noFirst' => true,
          'firstVoice' => _('tutte le categorie'),
          'firstValue' => 0
        ))."
        <span class=\"right link\" onclick=\"$('attached-list-help').toggleClass('hidden')\">".pub::icon('help', array('text'=>_('informazioni'), 'scale'=>2))."</span>
      </p>";

    $buffer .= "<div id=\"attached-list-help\" class=\"hidden\">";
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

    $buffer .= "<div id=\"attached_table\">".$this->editorAttachedList()."</div>";

    return $buffer;

  }

  /**
   * @brief Tabella di allegati con funzionalità per i drag and drop all'interno di CKEDITOR
   * @return tabella allegati
   */
  public function editorAttachedList() {

    $ctg_id = cleanVar($_POST, 'ctg_id', 'int', '');

    if($ctg_id) {
      $where = "category='$ctg_id'";
    }
    else {
      $where = null;
    }

    $items = attachedItem::get($this, array('where' => $where));

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
        $drag_download = "<a href=\"".$item->path('download')."\">".$icon."</a>" . " " . "<a href=\"".$item->path('download')."\">".htmlChars($item->file)."</a>";
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
        $drag_download = "<a href=\"".$item->path('download')."\">".$icon."</a>" . " " . "<a href=\"".$item->path('download')."\">".htmlChars($item->file)."</a>";
      }

      $ctg = new attachedCtg($item->category, $this);
      $buffer .= "
        <tr>
          <td>".htmlChars($ctg->name)."</td>
          <td>".$item->previewLink('path')."</td>
          <td>".htmlChars($item->notes)."</td>
          <td class=\"drag-attached-view\">".$drag_view."</td>
          <td class=\"drag-attached-download\">".$drag_download."</td>
        </tr>
      ";
    }
    $buffer .= "
      </table>";

    return $buffer;

  }

}

?>
