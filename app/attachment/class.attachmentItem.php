<?php
/**
 * \file class.attachmentItem.php
 * @brief Contiene la definizione ed implementazione della classe AttachmentItem.
 *
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\attachment;

/**
 * Classe tipo model che rappresenta una singolo allegato
 *
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class AttachmentItem extends \Gino\Model {

  /**
   * @brief tabella del modello
   */
  public static $tbl_item = 'attachment';

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


  /**
   * Costruttore
   * 
   * @param integer $id valore ID del record
   * @param object $instance istanza del controller
   */
  function __construct($id, $instance) {

    $this->_controller = $instance;
    $this->_tbl_data = self::$tbl_item;

    $this->_fields_label = array(
      'insertion_date'=>_('Inserimento'),
      'last_edit_date'=>_('Ultima modifica'),
      'category'=>_('Categoria'),
      'file'=>_("File"),
      'notes'=>_("Note")
    );

    parent::__construct($id);

    $this->_model_label = $this->id ? $this->name : '';

    $this->_img_extension = array('jpg','jpeg','png','gif');
    $this->_xls_extension = array('xls','xlt','xlsx','csv','sxc','stc','ods','ots');
    $this->_doc_extension = array('doc','docx','odt','ott','sxw','stw','rtf','txt');
    $this->_pdf_extension = array('pdf');

  }

  /**
   * @brief Cast a stringa del modello
   * @return rappresentazione a stringa del modello
   */
  function __toString() {

    return $this->file;

  }

  /**
   * @brief Tipologia di allegato (img, xls, doc, pdf, other)
   * @return string tipologia dell'allegato
   */
  public function type() {
    if(\Gino\extension($this->file, $this->_img_extension)) {
      return 'img';
    }
    elseif(\Gino\extension($this->file, $this->_xls_extension)) {
      return 'xls';
    }
    elseif(\Gino\extension($this->file, $this->_doc_extension)) {
      return 'doc';
    }
    elseif(\Gino\extension($this->file, $this->_pdf_extension)) {
      return 'pdf';
    }
    else {
      return 'other';
    }
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

    $structure['category'] = new \Gino\ForeignKeyField(array(
      'name'=>'category',
      'model'=>$this,
      'foreign'=>'\Gino\App\Attachment\attachmentCtg',
      'foreign_controller'=>$this->_controller,
      'foreign_order'=>'name',
      'add_related' => true,
      'add_related_url' => $this->_home.'?evt['.\get_class($this->_controller).'-manageAttachment]&block=ctg&insert=1',
    ));

    $structure['insertion_date'] = new \Gino\DatetimeField(array(
      'name'=>'insertion_date',
      'model'=>$this,
      'required'=>true,
      'auto_now'=>false,
      'auto_now_add'=>true,
    ));

    $structure['last_edit_date'] = new \Gino\DatetimeField(array(
      'name'=>'last_edit_date',
      'model'=>$this,
      'required'=>true,
      'auto_now'=>true,
      'auto_now_add'=>true,
    ));

    // se esiste l'id costruisce il path, in inserimento lo costruisce la subclass di adminTable
    if($id) {
    	$ctg = new AttachmentCtg($this->category, $this->_controller);
    	$base_path = $ctg->path('abs');
    }
    else {
    	$base_path = null;
    }

    $structure['file'] = new \Gino\FileField(array(
      'name'=>'file',
      'model'=>$this,
      'required'=>true,
      'extensions'=>array(),
      'path'=>$base_path,
      'check_type'=>false,
    ));

    return $structure;

  }

  /**
   * Restituisce oggetti di tipo AttachmentItem
   * 
   * @param news $controller istanza del controller
   * @param array $options array associativo di opzioni (where, order e limit)
   * @return array di istanze di tipo attachmentItem
   */
  public static function get($controller, $options = null) {

    $res = array();

    $where = \Gino\gOpt('where', $options, '');
    $order = \Gino\gOpt('order', $options, 'last_edit_date DESC');
    $limit = \Gino\gOpt('limit', $options, null);

    $db = \Gino\db::instance();
    $selection = 'id';
    $table = self::$tbl_item;

    $rows = $db->select($selection, $table, $where, array('order'=>$order, 'limit'=>$limit));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new attachmentItem($row['id'], $controller);
      }
    }

    return $res;

  }

  /**
   * @brief Elimina tutti gli allegati di una categoria
   * @static
   * @param int $ctg_id id della categoria
   */
  public static function deleteFromCtg($ctg_id, $controller) {

    $db = \Gino\db::instance();
    $rows = $db->select('id', self::$tbl_item, "category='".$ctg_id."'");
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $item = new AttachmentItem($row['id'], $controller);
        $item->delete();
      }
    }
  }

	/**
	 * @brief Percorso dell'allegato
	 * 
	 * @param string $type tipo di percorso:
	 *   - abs: assoluto
	 *   - rel: relativo alla DOCUMENT ROOT
	 *   - view: realtivo alla ROOT
	 *   - url: url assoluto
	 *   - download: url relativo per il download
	 * @return string il percorso dell'allegato
	 */
	public function path($type) {

		if($type == 'download') {
			$link = new \Gino\Link();
			return $link->aLink(get_class($this->_controller), 'downloader', array('id'=>$this->id));
		}
		else {
			
			$ctg = new AttachmentCtg($this->category, $this->_controller);
			return $ctg->path($type).$this->file;
		}
	}

	/**
	 * @brief Link al preview dell'allegato
	 * 
	 * @description lightbox per le immagini e dowload per altri tipi di file
	 * 
	 * @see path()
	 * @params string $label etichetta da mostrare nel link, i possibili valori sono filename | path
	 */
	public function previewLink($label = 'filename') {

		$alabel = $label == 'path' ? $this->path('view') : $this->file;

		if($this->type() === 'img') {
			$onclick = "Slimbox.open('".$this->path('view')."')";
			return "<span class=\"link\" onclick=\"$onclick\">".$alabel."</span>";
		}
		else {
			$link = new \Gino\Link();
			return "<a href=\"".$link->aLink(\get_class($this->_controller), 'downloader', array('id'=>$this->id))."\">".$alabel."</a>";
	 	}
	}

}

?>