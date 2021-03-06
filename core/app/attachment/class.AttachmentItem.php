<?php
/**
 * @file class.AttachmentItem.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Attachment.AttachmentItem.
 *
 * @copyright 2013-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Attachment;

/**
 * @brief Classe di tipo Gino.Model che rappresenta una singolo allegato
 *
 * @copyright 2013-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class AttachmentItem extends \Gino\Model {

    /**
     * @brief tabella del modello
     */
    public static $table = 'attachment';
    
    public static $columns;

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
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @return void
     */
    function __construct($id) {

    	$this->_tbl_data = self::$table;
    	$this->_controller = new attachment();
    	
    	parent::__construct($id);
    	
    	$this->_model_label = _('Allegato');

        $this->_img_extension = array('jpg','jpeg','png','gif');
        $this->_xls_extension = array('xls','xlt','xlsx','csv','sxc','stc','ods','ots');
        $this->_doc_extension = array('doc','docx','odt','ott','sxw','stw','rtf','txt');
        $this->_pdf_extension = array('pdf');
    }
    
    /**
     * @brief Rappresentazione a stringa del modello
     * @return string, nome file
     */
    function __toString() {

        return $this->file;
    }

    /**
     * @brief Tipologia di allegato (img, xls, doc, pdf, other)
     * @return string tipologia dell'allegato (img, xls, doc, pdf, other)
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
     * @see Gino.Model::properties()
     */
    protected static function properties($model, $controller) {
    	
    	$property['file'] = array(
    		'path' => $controller->getPath($model->category),
    	);
    	
    	return $property;
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    	
    	$registry = \Gino\Registry::instance();
    	
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name'=>'id',
    		'primary_key'=>true,
    		'auto_increment'=>true,
    	));
    	$columns['category'] = new \Gino\ForeignKeyField(array(
            'name' => 'category',
    		'label' => _('Categoria'),
            'required' => TRUE,
            'foreign' => '\Gino\App\Attachment\AttachmentCtg',
            'foreign_order' => 'name',
            'add_related' => TRUE,
            'add_related_url' => $registry->router->link('attachment', 'manageAttachment', array(), "block=ctg&insert=1")
        ));
    	$columns['file'] = new \Gino\FileField(array(
    		'name' => 'file',
    		'label' => _("File"),
    		'required' => TRUE,
    		'extensions' => array(),
    		'path' => null,
    		'check_type' => FALSE,
    		'max_lenght' => 100,
    	));
    	$columns['insertion_date'] = new \Gino\DatetimeField(array(
    		'name' => 'insertion_date',
    		'label'=>_('Inserimento'),
    		'required' => TRUE,
    		'auto_now' => FALSE,
    		'auto_now_add' => TRUE
    	));
    	$columns['last_edit_date'] = new \Gino\DatetimeField(array(
    		'name' => 'last_edit_date',
    		'label'=>_('Ultima modifica'),
    		'required' => TRUE,
    		'auto_now' => TRUE,
    		'auto_now_add' => TRUE
    	));
    	$columns['notes'] = new \Gino\TextField(array(
    		'name' => 'notes',
    		'label' => _("Note")
    	));
    	
    	return $columns;
    }

    /**
     * @brief Elimina tutti gli allegati di una categoria
     * @param int $ctg_id id della categoria
     * @return TRUE
     */
    public static function deleteFromCtg($ctg_id) {

        $db = \Gino\db::instance();
        $rows = $db->select('id', self::$table, "category='".$ctg_id."'");
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $item = new AttachmentItem($row['id']);
                $item->delete();
            }
        }
        return true;
    }

    /**
     * @brief Percorso dell'allegato relativo alla ROOT
     * @return string il percorso dell'allegato
     */
    public function pathView() {
        return $this->path('view');
    }

    /**
     * @brief Percorso dell'allegato per il download
     * @return string il percorso dell'allegato
     */
    public function pathDownload() {
        return $this->path('download');
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
            return $this->_registry->router->link('attachment', 'downloader', array('id' => $this->id));
        }
        else {
            $ctg = new AttachmentCtg($this->category);
            return $ctg->path($type) . $this->file;
        }
    }

    /**
     * @brief Link al preview dell'allegato
     * @description Lightbox per le immagini e download per altri tipi di file
     *
     * @see path()
     * @params string $label etichetta da mostrare nel link, i possibili valori sono filename | path
     * @return string
     */
    public function previewLink($label = 'filename') {

        $alabel = $label == 'path' ? $this->path('view') : $this->file;

        if($this->type() === 'img') {
            
            $modal_body = "<img src=\"".$this->path('view')."\" />";
            
            $modal = new \Gino\Modal([
                'modal_id' => 'ModalCenter'.$this->id,
                'modal_title_id' => 'ModalCenterTitle'.$this->id,
            ]);
            
            $link = $modal->trigger($alabel, []);
            $link .= $modal->render(_("Media"), $modal_body);
            return $link;
        }
        else {
            return sprintf('<a href="%s">%s</a>', $this->_registry->router->link('attachment', 'downloader', array('id' => $this->id)), $alabel);
         }
    }

    /**
     * @brief Icona download con link preview file (lightbox immagini, download files)
     * @return string, icona con link preview
     */
    public function previewAdminList() {

        if($this->type() === 'img') {
            
            $modal_body = "<img src=\"".$this->path('view')."\" />";
            
            $modal = new \Gino\Modal([
                'modal_id' => 'ModalCenter'.$this->id,
                'modal_title_id' => 'ModalCenterTitle'.$this->id,
            ]);
            
            $link = $modal->trigger(\Gino\icon('export'), ['tagname' => 'span']);
            $link .= $modal->render(_("Media"), $modal_body);
            return $link;
        }
        else {
            return sprintf('<a href="%s">%s</a>', $this->_registry->router->link('attachment', 'downloader', array('id' => $this->id)), \Gino\icon('export'));
         }
    }
}
AttachmentItem::$columns=AttachmentItem::columns();