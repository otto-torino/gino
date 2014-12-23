<?php
/**
 * @file class.AttachmentItem.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Attachment.AttachmentItem.
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Attachment;

/**
 * @brief Classe di tipo Gino.Model che rappresenta una singolo allegato
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class AttachmentItem extends \Gino\Model {

    /**
     * @brief tabella del modello
     */
    public static $table = 'attachment';

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
     * @return istanza di Gino.App.Attachment.AttachmentItem
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'insertion_date'=>_('Inserimento'),
            'last_edit_date'=>_('Ultima modifica'),
            'category'=>_('Categoria'),
            'file'=>_("File"),
            'notes'=>_("Note")
        );

        parent::__construct($id);

        $this->_model_label = _('Allegato');

        $this->_img_extension = array('jpg','jpeg','png','gif');
        $this->_xls_extension = array('xls','xlt','xlsx','csv','sxc','stc','ods','ots');
        $this->_doc_extension = array('doc','docx','odt','ott','sxw','stw','rtf','txt');
        $this->_pdf_extension = array('pdf');

    }

    /**
     * @brief Rappresentazione a stringa del modello
     * @return nome file
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
     * @brief Sovrascrive la struttura di default
     *
     * @see Gino.Model::structure()
     * @param integer $id
     * @return array, struttura
     */
    public function structure($id) {

        $structure = parent::structure($id);

        $structure['category'] = new \Gino\ForeignKeyField(array(
            'name' => 'category',
            'model' => $this,
            'required' => TRUE,
            'foreign' => '\Gino\App\Attachment\AttachmentCtg',
            'foreign_order' => 'name',
            'add_related' => TRUE,
            'add_related_url' => $this->_registry->router->link('attachment', 'manageAttachment', array(), "block=ctg&insert=1")
        ));

        $structure['insertion_date'] = new \Gino\DatetimeField(array(
            'name' => 'insertion_date',
            'model' => $this,
            'required' => TRUE,
            'auto_now' => FALSE,
            'auto_now_add' => TRUE
        ));

        $structure['last_edit_date'] = new \Gino\DatetimeField(array(
            'name' => 'last_edit_date',
            'model' => $this,
            'required' => TRUE,
            'auto_now' => TRUE,
            'auto_now_add' => TRUE
        ));

        // se esiste l'id costruisce il path, in inserimento lo costruisce la subclass di adminTable
        if($id) {
            $ctg = new AttachmentCtg($this->category);
            $base_path = $ctg->path('abs');
        }
        else {
            $base_path = null;
        }

        $structure['file'] = new \Gino\FileField(array(
            'name' => 'file',
            'model' => $this,
            'required' => TRUE,
            'extensions' => array(),
            'path' => $base_path,
            'check_type' => FALSE
        ));

        return $structure;

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
     *
     * @description Lightbox per le immagini e download per altri tipi di file
     *
     * @see path()
     * @params string $label etichetta da mostrare nel link, i possibili valori sono filename | path
     * @return link preview
     */
    public function previewLink($label = 'filename') {

        $alabel = $label == 'path' ? $this->path('view') : $this->file;

        if($this->type() === 'img') {
            $onclick = "Slimbox.open('".$this->path('view')."')";
            return sprintf('<span class="link" onclick="%s">%s</label>', $onclick, $alabel);
        }
        else {
            return sprintf('<a href="%s">%s</a>', $this->_registry->router->link('attachment', 'downloader', array('id' => $this->id)), $alabel);
         }
    }

    /*+
     * @brief Icona download con link preview file (lightbox immagini, download files)
     * @return html, icona con link preview
     */
    public function previewAdminList() {

        if($this->type() === 'img') {
            $onclick = "Slimbox.open('".$this->path('view')."')";
            return sprintf('<span class="link" onclick="%s">%s</label>', $onclick, \Gino\icon('export'));
        }
        else {
            return sprintf('<a href="%s">%s</a>', $this->_registry->router->link('attachment', 'downloader', array('id' => $this->id)), \Gino\icon('export'));
         }
    }

}
