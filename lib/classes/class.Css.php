<?php
/**
 * @file class.Css.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Css
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use Gino\Http\Redirect;

/**
 * @brief Libreria per la gestione dei file css dei singoli moduli e dei file css del layout (da associare alle skin)
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
     * @return void, istanza di Gino.Css
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
            $this->_mdlLink = $this->_registry->router->link($this->_name, $method, array(), "block=css");
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
    	$columns['filename'] = new \Gino\FileField(array(
    		'name' => 'filename',
    		'label' => _("File"),
    		'required' => true,
    		'max_lenght' => 200, 
    		'check_type' => false,
    		'extensions' => array('css'),
    		'types_allowed' => array("text/css", "text/x-c", "text/plain"),
    		'path' => CSS_DIR.OS
    	));
    	$columns['label'] = new \Gino\CharField(array(
    		'name' => 'label',
    		'label' => _("Label"),
    		'required' => true,
    		'max_lenght' => 200,
    	));
    	$columns['description'] = new \Gino\TextField(array(
    		'name'=>'description',
    		'label' => _("Descrizione"),
    		'required' => false
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
     * @return string, nome file
     */
    private function cssFileName($css_file) {

        $name = $this->_instance_class ? baseFileName($css_file)."_".$this->_name.".css" : $css_file;
        return $name;
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
     * @return string, codice html form
     */
    public function formCssLayout() {

    	$gform = Loader::load('Form', array());
        $gform->load('dataform');
        
        $title = $this->id ? sprintf(_('Modifica "%s"'), htmlChars($this->label)) : _("Nuovo foglio di stile");
        
        $formaction = $this->_registry->router->link($this->_interface, 'actionCss');
        
        $buffer = $gform->open($formaction, true, 'label', array('form_id'=>'gform'));
        $buffer .= \Gino\Input::hidden('id', $this->id);
        
        $buffer .= \Gino\Input::input_label('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id));
        $buffer .= \Gino\Input::textarea_label('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array("cols"=>45, "rows"=>4, "trnsl"=>false));
        $buffer .= \Gino\Input::input_file('filename', $this->filename, _("File"), array('extensions' => array("css")));
        
        $buffer .= \Gino\Input::input_label('submit_action', 'submit', (($this->id)?_("modifica"):_("inserisci")), '', array("classField"=>"submit"));
        
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

    	$gform = Loader::load('Form', array());
    	$gform->saveSession('dataform');
    	$req_error = $gform->checkRequired();
    	
    	$action = ($this->id) ? 'modify' : 'insert';
    	
    	$link_error = $this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=css&id=$this->id&action=$action");
    	
    	if($req_error > 0) {
    		return Error::errorMessage(array('error'=>1), $link_error);
    	}
    	
    	$request = \Gino\Http\Request::instance();
    	
    	$filename = $request->FILES['filename']['name'];
    	
    	if($filename) {
    		$filename_size = $request->FILES['filename']['size'];
    		$filename_tmp = $request->FILES['filename']['tmp_name'];
    		
    		if(!$filename_size) {
    			throw new \Gino\Exception\ValidationError(_("Empty filename"));
    		}
    		/*
    		$finfo = finfo_open(FILEINFO_MIME_TYPE);
    		$mime = finfo_file($finfo, $filename_tmp);
    		finfo_close($finfo);
    		*/
    		if(!\Gino\extension($filename, 'css') ||
    		preg_match('#%00#', $filename)) {
    			throw new \Exception($code_messages[3]);
    		}
    		
    		$upload = move_uploaded_file($filename_tmp, SITE_ROOT.OS.'css'.OS.$filename) ? TRUE : FALSE;
    		if(!$upload) {
    			throw new \Exception(_("Errore nel salvataggio del file"));
    		}
    		else {
    			$this->filename = $filename;
    		}
    	}
    	
    	$this->label = cleanVar($request->POST, 'label', 'string', null);
    	$this->description = cleanVar($request->POST, 'description', 'string', null);
    	
    	$this->save();
    	
    	return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'css')));
    }

    /**
     * @brief Form per l'eliminazione di un file css (layout)
     *
     * @return string, codice html form
     */
    public function formDelCssLayout() {

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $title = sprintf(_('Elimina foglio di stile "%s"'), $this->label);

        $buffer = "<p class=\"backoffice-info\">"._('Attenzione! L\'eliminazione determina l\'eliminazione del file css dalle skin che lo contengono!')."</p>";
        
        $buffer .= $gform->open($this->_registry->router->link($this->_interface, 'actionDelCss'), '', '', array('form_id'=>'gform'));
        $buffer .= \Gino\Input::hidden('id', $this->id);
        $buffer .= \Gino\Input::input_label('submit_action', 'submit', _("elimina"), _('Sicuro di voler procedere?'), array("classField"=>"submit"));
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

        if($this->filename) {
        	@unlink(CSS_DIR.OS.$this->filename);
        }

        Loader::import('class', '\Gino\Skin');
        Skin::removeCss($this->id);

        $this->_registry->trd->deleteTranslations($this->_tbl_data, $this->id);
        $this->deleteDbData();

        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'css')));
    }

    /**
     * @brief Descrizione della procedura
     *
     * @return string, informazioni
     */
    public static function layoutInfo() {

        $buffer = "<h2>"._("CSS")."</h2>\n";
        $buffer .= "<p>"._("Upload di fogli di stile da associare eventualmente ad una skin. Il css viene accodato ai file di default di <i>gino</i>, pertanto è possibile definire nuovi stili o sovrascrivere quelli già presenti.")."</p>\n";

        return $buffer;
    }
}

Css::$columns=Css::columns();
