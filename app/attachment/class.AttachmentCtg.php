<?php
/**
 * @file class.AttachmentCtg.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Attachment.AttachmentCtg
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Attachment;

/**
 * @brief Classe di tipo Gino.Model che rappresenta una categoria di allegati.
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class AttachmentCtg extends \Gino\Model {

    public static $table = 'attachment_ctg';
    public static $columns;

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Attachment.AttachmentCtg
     */
    function __construct($id) {

        $this->_model_label = _('Categoria');
        $this->_tbl_data = self::$table;
        
        parent::__construct($id);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome categoria
     */
    function __toString() {

        return (string) $this->name;
    }

    /**
	 * Struttura dei campi della tabella di un modello
	 *
	 * @return array
	 */
    public static function columns() {
    	
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name'=>'id',
    		'primary_key'=>true,
    		'auto_increment'=>true,
    	));
    	$columns['name'] = new \Gino\CharField(array(
    		'name' => 'name',
    		'label'=>_("Nome"),
    		'required' => true,
    		'max_lenght'=>100,
    	));

        $controller = new attachment();

        $columns['directory'] = new \Gino\DirectoryField(array(
            'name' => 'directory',
        	'label'=>array(_("Nome directory"), _("Se non indicato viene preso il valore del campo 'Nome'.<br /><b>Attenzione!</b> Modificando il nome della directory, si perdono i collegamenti esistenti in campi di testo ai file in essa contenuti.")),
            'required' => true,
            'path' => $controller->getDataDir(),
            'default_name' => array(
                'field' => 'name'
            ),
    		'max_lenght'=>20,
        ));
        return $columns;
    }

    /**
     * @brief Array di categorie nella forma id=>name
     * @description Utilizzato per popolare un input select
     *
     * @param array $options array associativo di opzioni (where, order e limit)
     * @return array associativo id=>name
     */
    public static function getForSelect($options = array()) {

        $res = array();

        $where = \Gino\gOpt('where', $options, '');
        $order = \Gino\gOpt('order', $options, 'name');
        $limit = \Gino\gOpt('limit', $options, null);

        $db = \Gino\db::instance();
        $selection = 'id, name';
        $table = self::$table;

        $rows = $db->select($selection, $table, $where, array('order'=>$order, 'limit'=>$limit));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $res[$row['id']] = \Gino\htmlChars($row['name']);
            }
        }

        return $res;
    }

    /**
     * @brief Percorso alla directory con OS o slash finale
     *
     * @param string $type tipo di percorso:
     *   - abs: assoluto
     *   - rel: relativo alla DOCUMENT ROOT
     *   - view: realtivo alla ROOT
     *   - url: url assoluto
     * @return percorso directory
     */
    public function path($type) {

        $controller = new attachment();

        $directory = '';

        if($type == 'abs') {
            $directory = $controller->getDataDir().OS.$this->directory.OS;
        }
        elseif($type == 'rel') {
            $directory = $controller->getDataWWW().'/'.$this->directory.'/';
        }
        elseif($type == 'view') {
            $directory = preg_replace("#^".preg_quote(SITE_WWW)."/#", "", $controller->getDataWWW().'/'.$this->directory.'/');
        }
        elseif($type == 'url') {
            $request = \Gino\Http\Request::instance();
            $directory = $request->root_absolute_url.$controller->getDataWWW().'/'.$this->directory.'/';
        }

        return $directory;
    }

    /**
     * @brief Eliminazione modello
     * @description Elimina tutti gli allegati della categoria
     * @return TRUE
     */
    public function delete() {
        $result = parent::delete();
        AttachmentItem::deleteFromCtg($this->id);
        return TRUE;
    }
}

AttachmentCtg::$columns=AttachmentCtg::columns();
