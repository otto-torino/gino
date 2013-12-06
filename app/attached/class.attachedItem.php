<?php
/**
 * \file class.attachedItem.php
 * @brief Contiene la definizione ed implementazione della classe AttachedItem.
 *
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * Classe tipo model che rappresenta una singolo allegato
 *
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class AttachedItem extends Model {

  /**
   * @brief istanza del controller
   */
  private $_controller;

  /**
   * @brief tabella del modello
   */
  public static $tbl_item = 'attached';

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
    if(extension($this->file, $this->_img_extension)) {
      return 'img';
    }
    elseif(extension($this->file, $this->_xls_extension)) {
      return 'xls';
    }
    elseif(extension($this->file, $this->_doc_extension)) {
      return 'doc';
    }
    elseif(extension($this->file, $this->_pdf_extension)) {
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

    $structure['category'] = new foreignKeyField(array(
      'name'=>'category',
      'value'=>$this->category,
      'label'=>$this->_fields_label['category'],
      'fkey_table'=>attachedCtg::$tbl_ctg,
      'fkey_id'=>'id',
      'fkey_field'=>'name',
      'fkey_order'=>'name',
      'table'=>$this->_tbl_data
    ));

    $structure['insertion_date'] = new datetimeField(array(
      'name'=>'insertion_date',
      'required'=>true,
      'label'=>$this->_fields_label['insertion_date'],
      'auto_now'=>false,
      'auto_now_add'=>true,
      'value'=>$this->insertion_date,
      'table'=>$this->_tbl_data
    ));

    $structure['last_edit_date'] = new datetimeField(array(
      'name'=>'last_edit_date',
      'required'=>true,
      'label'=>$this->_fields_label['last_edit_date'],
      'auto_now'=>true,
      'auto_now_add'=>true,
      'value'=>$this->last_edit_date,
      'table'=>$this->_tbl_data
    ));

    // se esiste l'id costruisce il path, in inserimento lo costruisce la subclass di adminTable
    if($id) {
      $ctg = new attachedCtg($this->category, $this->_controller);
      $base_path = $ctg->path('abs');
    }
    else {
      $base_path = null;
    }

    $structure['file'] = new fileField(array(
      'name'=>'file',
      'required'=>true,
      'value'=>$this->file,
      'label'=>$this->_fields_label['file'],
      'extensions'=>array(),
      'path'=>$base_path,
      'check_type'=>false,
      'table'=>$this->_tbl_data
    ));

    return $structure;

  }

  /**
   * Restituisce oggetti di tipo attachedItem
   * 
   * @param news $controller istanza del controller
   * @param array $options array associativo di opzioni (where, order e limit)
   * @return array di istanze di tipo attachedItem
   */
  public static function get($controller, $options = null) {

    $res = array();

    $where = gOpt('where', $options, '');
    $order = gOpt('order', $options, 'last_edit_date DESC');
    $limit = gOpt('limit', $options, null);

    $db = db::instance();
    $selection = 'id';
    $table = self::$tbl_item;

    $rows = $db->select($selection, $table, $where, array('order'=>$order, 'limit'=>$limit));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new attachedItem($row['id'], $controller);
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

    $db = db::instance();
    $rows = $db->select('id', self::$tbl_item, "category='".$ctg_id."'");
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $item = new attachedItem($row['id'], $controller);
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
			$link = new link();
			return $link->aLink(get_class($this->_controller), 'downloader', array('id'=>$this->id));
		}
		else {
			
			$ctg = new attachedCtg($this->category, $this->_controller);
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
			$link = new link();
			return "<a href=\"".$link->aLink(get_class($this->_controller), 'downloader', array('id'=>$this->id))."\">".$alabel."</a>";
	 	}
	}

}

?>
