<?php
/**
 * @file class.Css.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Css
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use Gino\Http\Redirect;

/**
 * @brief Libreria per la gestione dei file css dei singoli moduli e dei file css del layout (da associare alle skin)
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Css extends Model {

    public static $table = 'sys_layout_css';
    public static $columns;
    
    protected $_tbl_data;
    private $_class, $_module, $_name, $_label, $_css_list;
    private $_instance_class;
    private $_mdlLink;
    private $_interface;
    private $_tbl_module;

    /**
     * @brief Costruttore
     * 
     * I nomi dei file CSS del modulo vengono recuperati richiamando il metodo getClassElements() che ritorna un array con la chiave @a css (array). \n
     * Se il modulo non è istanziabile il metodo getClassElements() dovrà riportare anche la chiave @a instance con valore @a false. \n
     * 
     * @see getClassElements()
     * @param string $type tipo di utilizzo
     *   - @b module
     *   - @b layout
     * @param array $params
     *   array associativo di opzioni
     *   - @b id (integer): valore ID del record
     *   - @b class (string): nome della classe
     *   - @b module (integer): valore ID del modulo
     *   - @b name (string): nome del modulo
     *   - @b label (string): etichetta del modulo
     * @return istanza di Gino.Css
     */
    function __construct($type, $params=array()) {

        $db = db::instance();
        if($type=='module') {
            $this->_class = $params['class'];
            $this->_module = $params['module'];
            $this->_name = $params['name'];
            $this->_label = $params['label'];
            $classElements = call_user_func(array($this->_class, 'getClassElements'));
            $this->_css_list = $classElements['css'];
            $this->_instance_class = array_key_exists('instance', $classElements) ? $classElements['instance'] : true;
            $method = $this->_instance_class ? 'manageDoc' : 'manage'.ucfirst($this->_class);
            $this->_mdlLink = HOME_FILE."?evt[{$this->_name}-{$method}]&block=css";
        }
        elseif($type=='layout') {
            $id = $params['id'];
            $this->_tbl_data = self::$table;
            parent::__construct($id);

            $this->_interface = 'layout';
        }
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
    	
    	$columns['filename'] = new \Gino\CharField(array(
    			'name'=>'filename',
    			'label'=>_("File"),
    			'required'=>true,
    			'max_lenght'=>200,
    	));
    	$columns['label'] = new \Gino\CharField(array(
    			'name'=>'label',
    			'label'=>_("Label"),
    			'required'=>true,
    			'max_lenght'=>200,
    	));
    	$columns['description'] = new \Gino\TextField(array(
    			'name'=>'description',
    			'label' => _("Descrizione"),
    			'required'=>false
    	));
    	
    	return $columns;
    }

    /**
     * @brief Lista oggetti
     *
     * @param string $order campo di ordinamento risultati
     * @return array di oggetti Gino.Css
     */
    public static function getAll($order = 'label') {

        $db = Db::instance();
        $res = array();
        $rows = $db->select('id', self::$table, '', array('order' => $order));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $res[] = new Css('layout', array('id'=>$row['id']));
            }
        }

        return $res;
    }

    /**
     * @brief Ricava il nome del file css dell'istanza di un modulo
     * @return nome file
     */
    private function cssFileName($css_file) {

        $name = $this->_instance_class ? baseFileName($css_file)."_".$this->_name.".css" : $css_file;
        return $name;
    }


    /**
     * @brief Setta il nome del file
     * @param string $value nome file
     * @return TRUE
     */
    public function setFilename($value) {

        if($this->_p['filename']!=$value && !in_array('filename', $this->_chgP)) $this->_chgP[] = 'filename';
        $this->_p['filename'] = $value;
        return TRUE;
    }

    /**
     * @brief Recupera l'oggetto a partire dal mone del file
     * @return Gino.Css o null se non lo trova
     */
    public static function getFromFilename($filename) {

        $db = Db::instance();
        $res = null;
        $rows = $db->select('id', self::$table, "filename='$filename'");
        if($rows and count($rows)) {
            $res = new Css('layout', array('id'=>$rows[0]['id']));
        }

        return $res;
    }

    /**
     * @brief Form per la creazione e la modifica di un file css (layout)
     * @return codice html form
     */
    public function formCssLayout() {

        $gform = Loader::load('Form', array('gform', 'post', true, array("trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id)));
        $gform->load('dataform');

        $action = $this->id ? 'modify':'insert';
        $title = $this->id ? sprintf(_('Modifica "%s"'), htmlChars($this->label)) : _("Nuovo foglio di stile");

        $formaction = $this->_registry->router->link('layout', 'actionCss');
        $required = 'label';
        $buffer = $gform->open($formaction, true, $required);
        $buffer .= $gform->hidden('id', $this->id);
        $buffer .= $gform->hidden('old_filename', $this->filename);

        $buffer .= $gform->cfile('filename', $this->filename, _("File"), array("required"=>true, "extensions"=>array("css"), "del_check"=>true));
        $buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"label"));
        $buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array("cols"=>45, "rows"=>4, "trnsl"=>true, "field"=>"description"));

        $buffer .= $gform->cinput('submit_action', 'submit', (($this->id)?_("modifica"):_("inserisci")), '', array("classField"=>"submit"));
        $buffer .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
        'title' => $title,
        'class' => 'admin',
        'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di inserimento/modifica css
     * @see self::formCssLayout()
     * @param \Gino\Http\Request istanza di Gino.Request
     * @return Gino.Http.Response
     */
    public function actionCssLayout(\Gino\Http\Request $request) {

        $gform = Loader::load('Form', array('gform', 'post', TRUE));
        $gform->save('dataform');
        $req_error = $gform->arequired();

        $action = $this->id ? 'modify' : 'insert';
        $link_error = $this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=css&id=$this->id&action=$action");

        if($req_error > 0) 
            return error::errorMessage(array('error'=>1), $link_error);

        $filename_tmp = $_FILES['filename']['tmp_name'];
        $old_filename = cleanVar($request->POST, 'old_filename', 'string', '');

        $directory = CSS_DIR.OS;
        $redirect = $this->_interface.'-manageLayout';
        $link = "block=css";
        $link .= $this->id ? "&action=modify&id=$this->id" : "&action=insert";

        foreach($_POST as $k=>$v) {
            $this->{$k} = cleanVar($request->POST, $k, 'string', '');
        }
        $this->save();

        $gform->manageFile('filename', $old_filename, false, array('css'), $directory, $link_error, $this->_tbl_data, 'filename', 'id', $this->id, array("check_type"=>true, "types_allowed"=>array("text/css", "text/x-c", "text/plain")));


        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'css')));
    }

    /**
     * @brief Form per l'eliminazione di un file css (layout)
     *
     * @return codice html form
     */
    public function formDelCssLayout() {

        $gform = Loader::load('Form', array('gform', 'post', true));
        $gform->load('dataform');

        $title = sprintf(_('Elimina foglio di stile "%s"'), $this->label);

        $buffer = "<p class=\"backoffice-info\">"._('Attenzione! L\'eliminazione determina l\'eliminazione del file css dalle skin che lo contengono!')."</p>";
        
        $formaction = $this->_registry->router->link($this->_interface, 'actionDelCss');
        $required = '';
        $buffer .= $gform->open($formaction, '', $required);
        $buffer .= $gform->hidden('id', $this->id);
        $buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), _('Sicuro di voler procedere?'), array("classField"=>"submit"));
        $buffer .= $gform->close();

        $view = new view();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $title,
            'class' => 'admin',
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di eliminazione css
     * @see self::formDelCssLayout()
     * @param \Gino\Http\Request istanza di Gino.Request
     * @return Gino.Http.Response
     */
    public function actionDelCssLayout(\Gino\Http\Request $request) {

        if($this->filename) @unlink(CSS_DIR.OS.$this->filename);

        Loader::import('class', '\Gino\Skin');
        Skin::removeCss($this->id);

        $this->_registry->trd->deleteTranslations($this->_tbl_data, $this->id);
        $this->deleteDbData();

        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'css')));
    }

    /**
     * @brief Descrizione della procedura
     *
     * @return informazioni, codice html
     */
    public static function layoutInfo() {

        $buffer = "<h2>"._("CSS")."</h2>\n";
        $buffer .= "<p>"._("Upload di fogli di stile da associare eventualmente ad una skin. Il css viene accodato ai file di default di <i>gino</i>, pertanto è possibile definire nuovi stili o sovrascrivere quelli già presenti.")."</p>\n";

        return $buffer;
    }
}

Css::$columns=Css::columns();
