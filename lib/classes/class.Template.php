<?php
/**
 * @file class.Template.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Template
 */
namespace Gino;

use Gino\Http\Redirect;

/**
 * @brief Libreria per la gestione dei template del documento html da associare alle @ref Gino.Skin
 */
class Template extends Model {

	public static $table = 'sys_layout_tpl';
    public static $columns;
    
    private static $table_block = 'sys_layout_tpl_block';
    private $_home, $_interface;

    private $_blocks_number, $_blocks_properties;
    private $_align_dict;
    private $_um_dict;

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @return void
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_home = 'index.php';
        $this->_interface = 'layout';
    }
    
    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string
     */
    function __toString() {
        
        return (string) $this->ml('label');
    }
    
    /**
     * @brief Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    	
     	$columns['id'] = new \Gino\IntegerField(array(
    		'name' => 'id',
    		'primary_key' => true,
    		'auto_increment' => true,
    	));
     	$columns['label'] = new \Gino\CharField(array(
     	    'name' => 'label',
     	    'label' => _("Etichetta"),
     	    'required' => true,
     	    'max_lenght' => 200
     	));
     	$columns['filename'] = new \Gino\CharField(array(
     	    'name' => 'filename',
     	    'label' => [_("Nome file"), _("Inserire senza estensione")],
     	    'required' => true,
     	    'max_lenght' => 200,
     	    'trnsl' => false
     	));
    	$columns['description'] = new \Gino\TextField(array(
    		'name' => 'description',
    	    'label' => _("Descrizione"),
    		'required' => true
    	));
    	$columns['free'] = new \Gino\BooleanField(array(
    		'name' => 'free',
    	    'label' => _("Template libero"),
    		'required' => true
    	));
    	return $columns;
    }

    /**
     * @brief Descrizione della procedura
     *
     * @return string
     */
    public static function layoutInfo() {

        $buffer = "<h2>"._('Template')."</h2>";
        $buffer .= "<p>"._("Nel template è possibile controllare finemente ogni aspetto del layout finale della pagina. 
Il template comprende l'intero documento, dalla definizione del DOCTYPE alla chiusura del tag html. 
E' possibile utilizzare codice php, si hanno a disposizione tutte le librerie di gino. 
In questo caso non è necessario associare fogli di stile caricati a proposito, in quanto si possono direttamente 
controllare le chiamate a css, javascript etc. modificando l'intestazione del documento.")."</p>\n";
        $buffer .= "<p>"._("Il template dovrà poi essere associato ad una skin per essere renderizzato secondo le regole definite dalla skin stessa.");
        
        return $buffer;
    }

    /**
     * @brief Form di inserimento/modifica template di tipo free
     * @return string
     */
    public function formFreeTemplate() {
        
        $codemirror = \Gino\Loader::load('CodeMirror', array(['type' => 'view']));
        
        if($this->id) {
            $title = _("Modifica template")." '".htmlChars($this->label)."'";
            $code = file_get_contents(TPL_DIR.OS.$this->filename);
        }
        else {
            $title = _("Nuovo template");
            $code = file_get_contents(TPL_DIR.OS."default_free_tpl.php");
        }
        
        $info = "<div class=\"backoffice-info\">";
        $info .= "<p>"._('La scrittura di template in modalità libera consente di scrivere direttamente il template utilizzando codice php. È uno strumento molto potente quanto pericoloso, si consiglia di non modificare template amministrativi in questo modo, in quanto se dovessero verificarsi degli errori non sarebbe in alcuni casi possibile correggerli.')."</p>";
        $info .= "<p>"._('Tutte le classi di gino sono disponibili attraverso il modulo Loader, ed il registro $register è già disponibile. Consultare le reference di gino per maggiori informazioni.')."</p>";
        $info .= "<p>".sprintf(_('Le viste disponibili sono inseribili all\'interno del template utilizzando una particolare sintassi. <span class="link" onclick="%s">CLICCA QUI</span> per ottenere un elenco.'), 
        "var w = new gino.layerWindow({
        'title': '"._('Moduli e pagine')."',
        'url': '".$this->_registry->router->link($this->_interface, 'modulesCodeList')."',
        'width': 800,
        'height': 500,
        'overlay': false
        }); w.display();")."</p>";
        $info .= "</div>";
        
        if($this->id) {
            $file_readonly = true;
            $file_pattern = null;
            $file_hint = null;
        }
        else {
            $file_readonly = false;
            $file_pattern = "^[\d\w_-]*$";
            $file_hint = _("caratteri alfanumerici, '_', '-'");
        }
        
        $mform = \Gino\Loader::load('ModelForm', array($this, array(
            'form_id' => 'gform',
        )));
        
        $buffer = $mform->view(
            array(
                'session_value' => 'dataform',
                'view_title' => false,
                'f_action' => $this->_registry->router->link($this->_interface, 'actionTemplate', array(), array('free' => 1)),
                's_value' => _("salva"),
                'show_save_and_continue' => true,
                'savecontinue_name' => 'savecontinue_action',
                'removeFields' => ['free'],
                'addCell' => [
                    'filename' => ['name' => 'free', 'field' => \Gino\Input::hidden('free', 1)],
                    'last_cell' => [
                        'name' => 'code', 
                        'field' => $codemirror->inputText('code', $code, ['label' => _("Codice PHP")])
                    ]
                ]
            ),
            array(
                'filename' => ['readonly' => $file_readonly, "pattern" => $file_pattern, "hint" => $file_hint]
            )
        );
        
        $buffer .= $codemirror->renderScript();
        
        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $title,
            'class' => null,
            'content' => $info.$buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di inserimento/modifica template free
     * @see self::formFreeTemplate()
     * @param \Gino\Http\Request $request istanza di Gino.Request
     * @return Gino.Http.Response
     */
    public function actionFreeTemplate(\Gino\Http\Request $request) {

        $this->free = 1;
        $this->label = cleanVar($request->POST, 'label', 'string', '');
        $this->description = cleanVar($request->POST, 'description', 'string', '');
        $tplFilename = cleanVar($request->POST, 'filename', 'string', '');
        if($tplFilename) {
            $this->filename = $tplFilename.".php";
        }

        $action = ($this->id) ? 'modify' : 'insert';
        $link_error = $this->_registry->router->link($this->_interface, 'manageLayout', array(), 'block=template&action=$action&free=1');

        if(!$this->id && is_file(TPL_DIR.OS.$this->filename.".php")) {
            return Error::errorMessage(array('error'=>_("Nome file già presente")), $link_error);
        }

        if($fp = @fopen(TPL_DIR.OS.$this->filename, "wb")) {
          $code = filter_input(INPUT_POST, 'code');
          if(!fwrite($fp, $code)) {
                return Error::errorMessage(array('error'=>_("Impossibile scrivere il file")), $link_error);
          }
          fclose($fp);
        }
        else {
            return Error::errorMessage(array('error'=>_("Impossibile creare il file"), 'hint'=>_("Controllare i permessi in scrittura all'interno della cartella ".TPL_DIR.OS)), $link_error);
        }

        $this->save();

        if(isset($request->POST['savecontinue_action'])) {
            return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=template&id=".$this->id."&action=modify&free=1"));
        }
        else {
            return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=template"));
        }
    }

    /**
     * @brief Form di eliminazione di un template
     *
     * @return string
     */
    public function formDelTemplate() {

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $buffer = "<p class=\"backoffice-info\">"._("L'eliminazione di un template determina l'eliminazione del template dalle skin che lo contengono!")."</p>";
        
        $buffer .= $gform->open($this->_registry->router->link($this->_interface, 'actionDelTemplate'), '', '', array('form_id'=>'gform'));
        $buffer .= \Gino\Input::hidden('id', $this->id);
        
        $submit = \Gino\Input::submit('submit_action', _("elimina"));
        $buffer .= \Gino\Input::placeholderRow(_("Sicuro di voler procedere?"), $submit);
        
        $buffer .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => sprintf(_('Elimina template "%s"'), htmlChars($this->label)),
            'class' => null,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di eliminazione di un template
     * @see self::formDelTemplate()
     * @see Gino.Skin::removeTemplate()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function actionDelTemplate(\Gino\Http\Request $request) {

        Loader::import('class', '\Gino\Skin');

        if($this->filename) @unlink(TPL_DIR.OS.$this->filename);

        Skin::removeTemplate($this->id);

        $this->_registry->trd->deleteTranslations($this->_tbl_data, $this->id);
        if(!$this->free) {
            $this->deleteBlocks();
        }
        $this->deleteDbData();

        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=template"));
    }
}

Template::$columns=Template::columns();