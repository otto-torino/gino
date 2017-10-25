<?php
/**
 * @file class.Template.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Template
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use Gino\Http\Redirect;

/**
 * @brief Libreria per la gestione dei template del documento html da associare alle @ref Gino.Skin
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
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
     * @return istanza di Gino.Template
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_home = 'index.php';
        $this->_interface = 'layout';

        $this->initBlocksProperties();

        $this->_align_dict = array("1"=>_("sinistra"), "2"=>_("centro"), "3"=>_("destra"));
        $this->_um_dict = array("1"=>"px", "2"=>"%");
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    	
     	$columns['id'] = new \Gino\IntegerField(array(
    		'name' => 'id',
    		'primary_key' => true,
    		'auto_increment' => true,
    	));
     	$columns['filename'] = new \Gino\FileField(array(
     		'name' => 'filename',
     		'required' => true,
     		'max_lenght' => 200
     	));
    	$columns['label'] = new \Gino\CharField(array(
    		'name' => 'label',
    		'required' => true,
    		'max_lenght' => 200
    	));
    	$columns['description'] = new \Gino\TextField(array(
    		'name' => 'description',
    		'required' => true
    	));
    	$columns['free'] = new \Gino\BooleanField(array(
    		'name' => 'free',
    		'required' => true
    	));
    	return $columns;
    }

    /**
     * @beief Imposta le proprietà dei blocchi
     * @return void
     */
    private function initBlocksProperties() {

        $this->_blocks_properties = array();
        if(!$this->id) $this->_blocks_number = 0;
        else {
            $rows = $this->_db->select("COUNT(id) as tot", self::$table_block, "tpl='".$this->id."'");
            if($rows and count($rows)) $this->_blocks_number = $rows[0]['tot'];
            else $this->_blocks_number = 0;
        }

        $rows = $this->_db->select('id, position, width, um, align, rows, cols', self::$table_block, "tpl='".$this->id."'", array('order' => 'position ASC'));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $this->_blocks_properties[$row['position']] = array(
                	"id"=>$row['id'],
                	"width"=>$row['width'],
                	"um"=>$row['um'],
                	"align"=>$row['align'],
                	"rows"=>$row['rows'],
                	"cols"=>$row['cols']
                );
            }
        }
    }

    /**
     * @brief Descrizione della procedura
     *
     * @return html, informazioni
     */
    public static function layoutInfo() {

        $buffer = "<h2>"._('Template')."</h2>";
        $buffer .= "<p>"._("<i>gino</i> supporta la creazione di tipi differenti di template, è possibile creare template a blocchi utilizzando il motorino di template apposito, oppure template liberi scrivendo direttamente codice php. Il template creato dovrà poi essere associato ad una skin per essere renderizzato secondo le regole definite dalla skin stessa.");
        $buffer .= "<h3>"._("Template a blocchi")."</h3>\n";
        $buffer .= "<p>"._("La struttura del template è formata da blocchi che contengono navate. Ciascuna navata può contenere un numero qualsiasi di moduli. I moduli lasciati 'vuoti' non occuperanno spazio all'interno del layout finale, mentre le navate 'vuote' occuperanno lo spazio in larghezza esattamente come definito nel template.")."</p>\n";
        $buffer .= "<p>"._("È possibile inserire qualunque vista esportata dai moduli e la vista corrente (quella specifica dell'url visitato). Il dimensionamento di blocchi e navate può essere gestito in px oppure in percentuali. L'intestazione del documento html non è controllabile, ma viene interamente gestita da gino.")."</p>\n";
        $buffer .= "<p>"._("Nella maschera di modifica e inserimento è presente il campo 'css' nel quale si può specificare un foglio di stile che viene caricato nella maschera di creazione del template. Selezionando un file css, il foglio di stile non viene automaticamente associato al template, cosa che deve essere fatta al momento di creazione della skin, ma viene utilizzato per creare un template adatto se si ha in previsione di utilizzarlo all'interno di una skin con un css che modifichi le dimensioni degli elementi strutturali.")."</p>\n";
        $buffer .= "<h3>"._("Template libero")."</h3>\n";
        $buffer .= "<p>"._("Creando un template libero è possibile controllare finemente ogni aspetto del layout finale della pagina. Il template comprende l'intero documento, dalla definizione del DOCTYPE alla chiusura del tag html. E' possibile utilizzare codice php, si hanno a disposizione tutte le librerie di gino. In questo caso non è necessario associare fogli di stile caricati a proposito, in quanto si possono direttamente controllare le chiamate a css, javascript etc. modificando l'intestazione del documento.")."</p>\n";

        return $buffer;
    }

    /**
     * @brief For di inserimento/modifica dati template
     * @param \Gino\Form $gform istanza di Gino.Form
     * @param bool $free indica se il template è di tipo free (TRUE) o a blocchi
     * @return html, form
     */
    private function formData($gform, $free = FALSE) {

        if($free) {
            $formaction = $this->_registry->router->link($this->_interface, 'actionTemplate', array(), array('free' => 1));
        }
        else {
            $formaction = $this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'template', 'action' => 'mngtpl'));
        }
        
        $buffer = $gform->open($formaction, '', 'label', array('form_id'=>'gform'));
        $buffer .= \Gino\Input::hidden('id', $this->id);
        $buffer .= \Gino\Input::input_label('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id));
        $buffer .= ($this->id)
            ? \Gino\Input::input_label('filename', 'text', htmlInput($this->filename), _("Nome file"), array("other"=>"disabled", "size"=>40, "maxlength"=>200))
            : \Gino\Input::input_label('filename', 'text', $gform->retvar('filename', htmlInput($this->filename)), array(_("Nome file"), _("Senza estensione, es. home_page")), array("required"=>true, "size"=>40, "maxlength"=>200, "pattern"=>"^[\d\w_-]*$", "hint"=>_("caratteri alfanumerici, '_', '-'")));
        $buffer .= \Gino\Input::textarea_label('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array("required"=>true, "cols"=>45, "rows"=>4, "trnsl"=>true, "trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id));

        if(!$free) {

            Loader::import('class', '\Gino\Css');
            $css_list = array();
            foreach(Css::getAll('label') as $css) {
                $css_list[$css->id] = htmlInput($css->label);
            }
            $buffer .= \Gino\Input::select_label('css', $gform->retvar('css', $this->css), $css_list, array(_("Css"), _("Selezionare il css qualora lo si voglia associare al template nel momento di definizione della skin (utile per la visualizzazione delle anteprime nello schema)")), null);
        }

        return $buffer;
    }

    /**
     * @brief Form di inserimento/modifica template di tipo free
     * @return html, form
     */
    public function formFreeTemplate() {

        $registry = Registry::instance();
        $registry->addJs(SITE_JS."/CodeMirror/codemirror.js");
        $registry->addCss(CSS_WWW."/codemirror.css");

        $registry->addJs(SITE_JS."/CodeMirror/htmlmixed.js");
        $registry->addJs(SITE_JS."/CodeMirror/matchbrackets.js");
        $registry->addJs(SITE_JS."/CodeMirror/css.js");
        $registry->addJs(SITE_JS."/CodeMirror/xml.js");
        $registry->addJs(SITE_JS."/CodeMirror/clike.js");
        $registry->addJs(SITE_JS."/CodeMirror/php.js");
        $options = "{
            lineNumbers: true,
            matchBrackets: true,
            mode: \"application/x-httpd-php\",
            indentUnit: 4,
            indentWithTabs: true,
            enterMode: \"keep\",
            tabMode: \"shift\"
        }";

        if($this->id) {
            $code = file_get_contents(TPL_DIR.OS.$this->filename);
        }
        else {
            $code = file_get_contents(TPL_DIR.OS."default_free_tpl.php");
        }

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $title = ($this->id) ? _("Modifica template")." '".htmlChars($this->label)."'" : _("Nuovo template");

        $buffer = "<div class=\"backoffice-info\">";
        $buffer .= "<p>"._('La scrittura di template in modalità libera consente di scrivere direttamente il template utilizzando codice php. È uno strumento molto potente quanto pericoloso, si consiglia di non modificare template amministrativi in questo modo, in quanto se dovessero verificarsi degli errori non sarebbe in alcuni casi possibile correggerli.')."</p>";
        $buffer .= "<p>"._('Tutte le classi di gino sono disponibili attraverso il modulo Loader, ed il registro $register è già disponibile. Consultare le reference di gino per maggiori informazioni.')."</p>";
        $buffer .= "<p>".sprintf(_('Le viste disponibili sono inseribili all\'interno del template utilizzando una particolare sintassi. <span class="link" onclick="%s">CLICCA QUI</span> per ottenere un elenco.'), "var w = new gino.layerWindow({
        'title': '"._('Moduli e pagine')."',
        'url': '".$this->_registry->router->link($this->_interface, 'modulesCodeList')."',
        'width': 800,
        'height': 500,
        'overlay': false
        }); w.display();")."</p>";
        $buffer .= "</div>";

        $buffer .= $this->formData($gform, TRUE);
        $buffer .= \Gino\Input::hidden('free', 1);
        $buffer .= \Gino\Input::textarea_label('code', $gform->retvar('code', $code), _("Codice PHP"), array("cols"=>45, "rows"=>14, 'id'=>'codemirror'));
        $save_and_continue = \Gino\Input::input('savecontinue_action', 'submit', _('salva e continua la modifica'), array("classField"=>"submit"));
        $buffer .= \Gino\Input::input_label('submit_action', 'submit', _('salva'), '', array("classField"=>"submit", 'text_add'=>$save_and_continue));
        $buffer .= $gform->close();

        $buffer .= "<script>var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codemirror'), $options);</script>";

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
        if($tplFilename) $this->filename = $tplFilename.".php";

        $action = ($this->id) ? 'modify' : 'insert';
        $link_error = $this->_registry->router->link($this->_interface, 'manageLayout', array(), 'block=template&action=$action&free=1');

        if(!$this->id && is_file(TPL_DIR.OS.$this->filename.".php")) {
            return Error::errorMessage(array('error'=>_("Nome file già presente")), $link_error);
        }

        if($fp = @fopen(TPL_DIR.OS.$this->filename, "wb")) {
          $code = filter_input(INPUT_POST, 'code');
            if(!fwrite($fp, $code))
                return Error::errorMessage(array('error'=>_("Impossibile scrivere il file")), $link_error);

            fclose($fp);
        }
        else return Error::errorMessage(array('error'=>_("Impossibile creare il file"), 'hint'=>_("Controllare i permessi in scrittura all'interno della cartella ".TPL_DIR.OS)), $link_error);

        $this->save();

        if(isset($request->POST['savecontinue_action'])) {
            return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=template&id=".$this->id."&action=modify&free=1"));
        }
        else {
            return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=template"));
        }
    }

    /**
     * @brief Form di inserimento/modifica di un template a blocchi
     *
     * @see self::formBlock()
     * @return html, form
     */
    public function formTemplate() {

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $title = ($this->id) ? _("Modifica template")." '".htmlChars($this->label)."'" : _("Nuovo template");

        $buffer = $this->formData($gform);
        if($this->id) {
            $buffer .= \Gino\Input::hidden('modTpl', 1);
        }
        $buffer .= $this->formBlock($gform);
        $buffer .= \Gino\Input::input_label('submit_action', 'submit', (($this->id)?_("procedi con la modifica del template"):_("crea template")), '', array("classField"=>"submit"));
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
     * @brief Form che introduce alla modifica dello schema dei template a blocchi
     *
     * @return html, form
     */
    public function formOutline() {

        if(!$this->id) return null;

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $title = _("Modifica lo schema");

        $buffer = $this->formData($gform);
        $buffer .= \Gino\Input::input_label('blocks_number', 'text', $this->_blocks_number, _('numero blocchi'), array("other"=>"disabled", 'size'=>1));
        $buffer .= \Gino\Input::input_label('submit_action', 'submit', _("vai allo schema"), '', array("classField"=>"submit"));
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
     * @brief Form di duplicazione di un template a blocchi
     *
     * @return html, form
     */
    public function formCopyTemplate() {

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $title = sprintf(_('Duplica template "%s"'), htmlChars($this->label));
        
        $buffer = $gform->open($this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=template&action=copytpl"), '', 'label,filename', array('form_id'=>'gform'));
        $buffer .= \Gino\Input::hidden('ref', $this->id);
        $buffer .= \Gino\Input::input_label('label', 'text', $gform->retvar('label', ''), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200));
        $buffer .= \Gino\Input::input_label('filename', 'text', $gform->retvar('filename', ''), array(_("Nome file"), _("Senza estensione, es. home_page")), array("required"=>true, "size"=>40, "maxlength"=>200, "pattern"=>"^[\d\w_-]*$", "hint"=>_("caratteri alfanumerici, '_', '-'")));
        $buffer .= \Gino\Input::textarea_label('description', $gform->retvar('description', ''), _("Descrizione"), array("cols"=>45, "rows"=>4));
        $buffer .= \Gino\Input::input_label('submit_action', 'submit', _("crea template"), '', array("classField"=>"submit"));

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
     * @brief Parte del form di template a blocchi per la scelta/visualizzazione del numero di blocchi
     *
     * @see tplBlockForm()
     * @param \Gino\Form $gform istanza di Gino.Form
     * @return html
     */
    private function formBlock($gform) {

        if($this->id) {

            $buffer = \Gino\Input::input_label('blocks', 'text', $this->_blocks_number, _("Numero blocchi"), array('size'=>4));
            $buffer .= \Gino\Input::hidden('blocks_number', $this->_blocks_number);
            $buffer .= "<div id=\"blocks_form\">".$this->tplBlockForm()."</div>";
        }
        else {
            for($i=1, $blocks_list=array(); $i<11; $i++) $blocks_list[$i] = $i;

            $onchange = "onchange=\"gino.ajaxRequest('post', '".$this->_registry->router->link('layout', 'manageLayout', array(), "block=template&action=mngblocks")."', 'id=$this->id&blocks_number='+$(this).value, 'blocks_form', {'load':'blocks_form'});\"";
            $buffer = \Gino\Input::select_label('blocks_number', $gform->retvar('blocks_number', $this->_blocks_number), $blocks_list, array(_("Numero blocchi"), _("Selezionare il numero di blocchi che devono comporre il layout")), array("js"=>$onchange));
            $buffer .= "<div id=\"blocks_form\"></div>";
        }

        return $buffer;
    }

    /**
     * @brief Parte del form template a blocchi per inserimento/modifica di un blocco
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request default null
     * @return html
     */
    public function tplBlockForm($request = null) {

        $gform = Loader::load('Form', array());

        $post_blocks_number = $request ? cleanVar($request->POST, 'blocks_number', 'int', '') : cleanVar($_POST, 'blocks_number', 'int', '');

        $blocks_number = $this->id ? $this->_blocks_number : $post_blocks_number;

        $buffer = '';

        if($this->id)
        {
            $note = "<p class=\"backoffice-info\">"._("ATTENZIONE: l'aggiunta o l'eliminazione anche soltanto di un blocco può comportare la necessità di rimettere mano alle classi del <b>CSS</b>, in quanto cambia 
            la sequenza dei blocchi e quindi il nome di riferimento alla classe del CSS.")."</p>";
            $buffer .= $note;
        }
        for($i=1; $i<$blocks_number+1; $i++) {

            if($this->id)
            {
                $name_select = 'addblocks_'.$i;
                $div_id = 'addblocks_form'.$i;
                $url = $this->_registry->router->link('layout', 'manageLayout', array(), "block=template&action=addblocks");
                $onchange = "onchange=\"gino.ajaxRequest('post', '$url', 'id=$this->id&ref=$i&$name_select='+$(this).value, '$div_id', {'load':'$div_id'});\"";
                $test_add = \Gino\Input::select_label($name_select, '', array(1=>1, 2=>2), _('Numero blocchi da aggiungere'), array("js"=>$onchange));
                $buffer .= $test_add;

                $buffer .= "<div id=\"$div_id\">";
                $buffer .= $this->addBlockForm($i, $request);
                $buffer .= "</div>";
            }

            $buffer .= "<fieldset id=\"block$i\">";

            $moo = "
            var getStatus = $('block$i').getStyle('opacity');
            if(getStatus == '0.2') {
              $('block$i').setStyle('opacity', '1');
              $('block$i').setStyle('color', '#333');
              $('del$i').value = 0;
              $('block$i').getElements('input, select').each(function(el) { el.removeProperty('disabled'); });
            }
            else {
              $('block$i').setStyle('opacity', '0.2');
              $('block$i').setStyle('color', '#FFF');
              $('del$i').value = 1;
              $('block$i').getElements('input, select').each(function(el) { el.setProperty('disabled', 'disabled'); });
              $('id_$i').removeProperty('disabled');
              $('del$i').removeProperty('disabled');
            };";

            $text_block = "<legend>"._("Blocco")." $i <span onclick=\"$moo\" class=\"pull-right\" style=\"cursor: pointer\">".\Gino\icon('delete')."</span></legend>";

            if($this->id) {

                $text_block .= \Gino\Input::hidden('del'.$i, 0, array('id'=>'del'.$i));
                $buffer .= $text_block;

                $buffer .= \Gino\Input::hidden('id_'.$i, $this->_blocks_properties[$i]['id'], array('id'=>'id_'.$i));

                $width = $this->_blocks_properties[$i]['width'] ? $this->_blocks_properties[$i]['width'] : '';

                $um = " ".\Gino\Input::select('um_'.$i, $this->_blocks_properties[$i]['um'], $this->_um_dict, array());
                $buffer .= \Gino\Input::input_label('width_'.$i, 'text', $width, array(_("Larghezza"), _("Se non specificata occupa tutto lo spazio disponibile")), array("required"=>false, "size"=>4, "maxlength"=>4, "text_add"=>$um));

                $buffer .= \Gino\Input::select_label('align_'.$i, $this->_blocks_properties[$i]['align'], $this->_align_dict, _("Allineamento"), array());

                $buffer .= \Gino\Input::input_label('rows_'.$i, 'text', $this->_blocks_properties[$i]['rows'], _("Numero righe"), array("required"=>true, "size"=>2, "maxlength"=>2));

                $buffer .= \Gino\Input::input_label('cols_'.$i, 'text', $this->_blocks_properties[$i]['cols'], _("Numero colonne"), array("required"=>true, "size"=>2, "maxlength"=>2));
            }
            else {

                $buffer .= $text_block;

                $um = " ".\Gino\Input::select('um_'.$i, '', $this->_um_dict, array());
                $buffer .= \Gino\Input::input_label('width_'.$i, 'text', '', array(_("Larghezza"), _("Se non specificata occupa tutto lo spazio disponibile")), array("required"=>false, "size"=>4, "maxlength"=>4, "text_add"=>$um));
                $buffer .= \Gino\Input::select_label('align_'.$i, '', $this->_align_dict, _("Allineamento"), array());
                $buffer .= \Gino\Input::input_label('rows_'.$i, 'text', '', _("Numero righe"), array("required"=>true, "size"=>2, "maxlength"=>2));
                $buffer .= \Gino\Input::input_label('cols_'.$i, 'text', '', _("Numero colonne"), array("required"=>true, "size"=>2, "maxlength"=>2));
            }
            $buffer .= "</fieldset>";
        }

        return $buffer;
    }

    /**
     * @brief Form di aggiunta blocchi
     * 
     * @param integer $ref numero del blocco nella sequenza corretta, default null
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request, default null
     * @return html
     */
    public function addBlockForm($ref = null, $request = null) {

        if(is_null($ref)) $ref = cleanVar($request->POST, 'ref', 'int', '');
        if(!$ref) return null;

        $gform = Loader::load('Form', array());

        $buffer = '';

        $add_num = is_null($request) ? cleanVar($_POST, 'addblocks_'.$ref, 'int', '') : cleanVar($request->POST, 'addblocks_'.$ref, 'int', '');
        $buffer .= \Gino\Input::hidden('addblocks_'.$ref, $add_num);

        for($i=1; $i<$add_num+1; $i++) {

            $ref_name = $ref.'_'.$i;
            $buffer .= "<fieldset>";
            $buffer .= "<legend>"._('Nuovo blocco')."</legend>";
            $um = " ".\Gino\Input::select('um_add'.$ref_name, '', $this->_um_dict, array());
            $buffer .= \Gino\Input::input_label('width_add'.$ref_name, 'text', '', array(_("Larghezza"), _("Se non specificata occupa tutto lo spazio disponibile")), array("required"=>false, "size"=>4, "maxlength"=>4, "text_add"=>$um));
            $buffer .= \Gino\Input::select_label('align_add'.$ref_name, '', $this->_align_dict, _("Allineamento"), array());
            $buffer .= \Gino\Input::input_label('rows_add'.$ref_name, 'text', '', _("Numero righe"), array("required"=>true, "size"=>2, "maxlength"=>2));
            $buffer .= \Gino\Input::input_label('cols_add'.$ref_name, 'text', '', _("Numero colonne"), array("required"=>true, "size"=>2, "maxlength"=>2));
            $buffer .= "</fieldset>";
        }

        return $buffer;
    }

    /**
     * @brief Form di eliminazione di un template
     *
     * @return html, form
     */
    public function formDelTemplate() {

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $buffer = "<p class=\"backoffice-info\">"._("L'eliminazione di un template determina l'eliminazione del template dalle skin che lo contengono!")."</p>";
        
        $buffer .= $gform->open($this->_registry->router->link($this->_interface, 'actionDelTemplate'), '', '', array('form_id'=>'gform'));
        $buffer .= \Gino\Input::hidden('id', $this->id);
        $buffer .= \Gino\Input::input_label('submit_action', 'submit', _("elimina"), _("Sicuro di voler procedere?"), array("classField"=>"submit"));
        $buffer .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => sprintf(_('Elimina template "%s"'), htmlChars($this->label)),
            'class' => 'admin',
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

    /**
     * @brief Schema e modifica interattiva del template a blocchi
     *
     * La creazione e la ricostruzione del template sono i due casi in cui si creano e si modificano i blocchi.
     * Il metodo che lavora sui blocchi è createTemplate(); nel caso della modifica del template viene letto direttamente il file.
     *
     * @see self::renderNave()
     * @param \Gino\Css $css istanza di Gino.Css
     * @param integer $tpl_id valore ID del template
     * @return html, interfaccia di modifica interativa del template
     */
    public function manageTemplate($css, $tpl_id=0) {

        $request = \Gino\Http\Request::instance();

        $gform = Loader::load('Form', array());	// array("tblLayout"=>false)
        $gform->load('dataform');

        $modTpl = cleanVar($request->POST, 'modTpl', 'int');    // parametro di ricostruzione del template
        $label = cleanVar($request->POST, 'label', 'string', '');
        $filename = cleanVar($request->POST, 'filename', 'string', '');
        $description = cleanVar($request->POST, 'description', 'string', '');
        $blocks_number = cleanVar($request->POST, 'blocks_number', 'int', '');

        if($this->id) {
            $template = $this->filename;
            $template = file_get_contents(TPL_DIR.OS.$template);

            if($modTpl)
                $template = $this->createTemplate($blocks_number, $template);
        }
        else $template = $this->createTemplate($blocks_number);    // ricostruzione del template

        $buffer = "<!DOCTYPE html>";
        $buffer .= "<html lang=\"".LANG."\">";
        $buffer .= "<head>\n";
        $buffer .= "<meta charset=\"utf-8\" />";
        $buffer .= "<base href=\"".$this->_registry->request->root_absolute_url."\" />\n";
        $buffer .= "<title>Template</title>\n";

        $buffer .= "<link rel=\"stylesheet\" href=\"".CSS_WWW."/styles.css\" type=\"text/css\" />\n";
        $buffer .= "<link rel=\"stylesheet\" href=\"".SITE_APP.OS."layout".OS."layout.css\" type=\"text/css\" />\n";
        if($css->id)
            $buffer .= "<link rel=\"stylesheet\" href=\"".CSS_WWW."/$css->filename\" type=\"text/css\" />\n";

        $buffer .= "<script type=\"text/javascript\" src=\"".SITE_JS."/MooTools-More-1.6.0-compressed.js\"></script>\n";
        $buffer .= "<script type=\"text/javascript\" src=\"".SITE_JS."/gino-min.js\"></script>\n";
        $buffer .= "<script type=\"text/javascript\" src=\"".SITE_APP."/layout/layout.js\"></script>\n";
        $buffer .= "</head>\n";

        $buffer .= "<body>\n";
        $buffer .= "<p class=\"title\">$label</p>";

        $regexp = "/(<div(?:.*?)(id=\"(nav_.*?)\")(?:.*?)>)\n?([^<>]*?)\n?(<\/div>)/";
        $render = preg_replace_callback($regexp, array($this, "renderNave"), $template);
        $buffer .= $render;

        // Form
        $formaction = $this->_registry->router->link($this->_interface, 'actionTemplate');
        
        $buffer .= $gform->open($formaction, '', '', array('form_id'=>'tplform', 'validation'=>false));
        $buffer .= \Gino\Input::hidden('id', $this->id);
        $buffer .= \Gino\Input::hidden('label', htmlInput($label));
        $buffer .= \Gino\Input::hidden('description', htmlInput($description));
        $buffer .= \Gino\Input::hidden('filename', $filename);
        $buffer .= \Gino\Input::hidden('selMdlTitle', _("Selezione modulo"), array("id"=>"selMdlTitle"));
        $buffer .= \Gino\Input::hidden('tplform_text', '', array("id"=>"tplform_text"));

        if(!$this->id || ($this->id && $modTpl))
        {
            if($modTpl)
                $buffer .= \Gino\Input::hidden('modTpl', $modTpl);

            $blocks_del = array();
            $num = 1;
            for($i=1; $i<=$blocks_number; $i++)
            {
                // Blocchi da aggiungere
            	$add_form = cleanVar($request->POST, 'addblocks_'.$i, 'int', '');
                for($y=1; $y<=$add_form; $y++) {

                    $ref_name = $i.'_'.$y;

                    $buffer .= \Gino\Input::hidden('id_'.$num, 0);
                    $buffer .= \Gino\Input::hidden('width_'.$num, cleanVar($request->POST, 'width_add'.$ref_name, 'int', ''));
                    $buffer .= \Gino\Input::hidden('um_'.$num, cleanVar($request->POST, 'um_add'.$ref_name, 'int', ''));
                    $buffer .= \Gino\Input::hidden('align_'.$num, cleanVar($request->POST, 'align_add'.$ref_name, 'int', ''));
                    $buffer .= \Gino\Input::hidden('rows_'.$num, cleanVar($request->POST, 'rows_add'.$ref_name, 'int', ''));
                    $buffer .= \Gino\Input::hidden('cols_'.$num, cleanVar($request->POST, 'cols_add'.$ref_name, 'int', ''));
                    $num++;
                }
                // /End

                $id_block = cleanVar($_POST, 'id_'.$i, 'int', '');
                $del_block = cleanVar($_POST, 'del'.$i, 'int', '');
                
                if($del_block == 1)
                {
                	$blocks_del[$id_block] = $i;
                }
                else
                {
                	$buffer .= \Gino\Input::hidden('id_'.$num, $id_block);
                	$buffer .= \Gino\Input::hidden('width_'.$num, cleanVar($request->POST, 'width_'.$i, 'int', ''));
                	$buffer .= \Gino\Input::hidden('um_'.$num, cleanVar($request->POST, 'um_'.$i, 'int', ''));
                	$buffer .= \Gino\Input::hidden('align_'.$num, cleanVar($request->POST, 'align_'.$i, 'int', ''));
                	$buffer .= \Gino\Input::hidden('rows_'.$num, cleanVar($request->POST, 'rows_'.$i, 'int', ''));
                	$buffer .= \Gino\Input::hidden('cols_'.$num, cleanVar($request->POST, 'cols_'.$i, 'int', ''));
                	
                	$num++;
                }
            }
            
            $buffer .= \Gino\Input::hidden('blocks_number', $num-1);
            $buffer .= \Gino\Input::hidden('blocks_del', base64_encode(json_encode($blocks_del)));
        }
        $buffer .= \Gino\Input::input('back', 'button', _("indietro"), array("classField"=>"generic", "js"=>"onclick=\"history.go(-1)\""));
        $buffer .= " ".\Gino\Input::input('save', 'button', _("salva template"), array("classField"=>"submit", "js"=>"onclick=\"saveTemplate();\""));
        $buffer .= $gform->close();

        $buffer .= "</div>\n";

        $buffer .= "</body>\n";
        $buffer .= "</html>\n";

        return $buffer;
    }

    /**
     * @brief Creazione template interfaccia interattiva
     * @param int $blocks_number numero blocchi
     * @paqram string $template
     * @return html
     */
    private function createTemplate($blocks_number, $template='') {

        $request = \Gino\Http\Request::instance();

        $buffer = '';
        $num = 1;
        for($i=1; $i<=$blocks_number; $i++) {

            $add_form = cleanVar($request->POST, 'addblocks_'.$i, 'int');
            for($y=1; $y<=$add_form; $y++) {

                $ref_name = $i.'_'.$y;

                $width_add = cleanVar($request->POST, 'width_add'.$ref_name, 'int', '');
                $um_add = cleanVar($request->POST, 'um_add'.$ref_name, 'int', '');
                $align_add = cleanVar($request->POST, 'align_add'.$ref_name, 'int', '');
                $rows_add = cleanVar($request->POST, 'rows_add'.$ref_name, 'int', '');
                $cols_add = cleanVar($request->POST, 'cols_add'.$ref_name, 'int', '');

                if($rows_add > 0 && $cols_add > 0)
                {
                    $buffer .= $this->printBlock($num, $align_add, $rows_add, $cols_add, $um_add, $width_add);
                    $num++;
                }
            }

            $delete = cleanVar($request->POST, 'del'.$i, 'int', '');
            $align = cleanVar($request->POST, 'align_'.$i, 'int', ''); 
            $rows = cleanVar($request->POST, 'rows_'.$i, 'int', '');
            $cols = cleanVar($request->POST, 'cols_'.$i, 'int', '');
            $um = cleanVar($request->POST, 'um_'.$i, 'int', '');
            $width = cleanVar($request->POST, 'width_'.$i, 'int', '');

            if($rows > 0 && $cols > 0 && $delete != 1)
            {
                $pos = $template ? $i : 0;
                $buffer .= $this->printBlock($num, $align, $rows, $cols, $um, $width, $pos, $template);
                $num++;
            }
        }

        return $buffer;
    }

    /**
     * @brief Creazione blocco nell'interfaccia interattiva di gestione template a blocchi
     * @param int $num numero blocco
     * @param string $align allineamento (1: sinistra, 2: centrato, 3: destra)
     * @param int $rows numero righe
     * @param int $cols numero colonne
     * @param string $um unita di misura (1: px, 2: %)
     * @param int $width larghezza
     * @param int $pos posizione
     * @param string $template
     * @return html
     */
    private function printBlock($num, $align, $rows, $cols, $um, $width, $pos=0, $template='') {

        if($align==2) $margin = "margin: auto;";
        elseif($align==3) $margin = "float: right;";
        else $margin = '';

        $um = $um == 1 ? 'px' : '%';
        $block_style_width = $width ? "width:".$width.$um.";" : '';

        if($um == 'px' && $width) $nav_style = "width:".floor($width/$cols)."px".($cols>1 ? ";float:left;" : "");
        else $nav_style = "width:".floor(100/$cols)."%".($cols>1 ? ";float:left;" : "");

        $old = false;
        if($pos && $template)
        {
            $db = Db::instance();
            $res = $db->select('rows, cols', self::$table_block, "tpl='".$this->id."' AND position='".$pos."'");
            if($res and count($res)) {
                $old = true;
                $rows = $res[0]['rows'];
                $cols = $res[0]['cols'];
            }
        }

        $buffer = "<div id=\"block_$num\" style=\"$block_style_width$margin\">\n";

        for($ii=1; $ii<$rows+1; $ii++) {
            for($iii=1; $iii<$cols+1; $iii++) {
                $module = '';
                if($old)
                {
                    $ref_nav = "nav_".$pos."_".$ii."_".$iii;
                    $pattern = '#<div id="'.$ref_nav.'" style="([a-zA-Z0-9 ":;%=]+)">[\r\n ]*(\{[a-zA-Z0-9= \{\}\r\n]+\})?[\r\n ]*<\/div>#';
                    if(preg_match($pattern, $template, $matches))
                    {
                        if($matches[0])
                        {
                            $nav_style = $matches[1];
                            if(array_key_exists(2, $matches)) $module = $matches[2];
                        }
                    }
                }
                $buffer .= "<div id=\"nav_".$num."_".$ii."_".$iii."\" style=\"".$nav_style."\">";
                $buffer .= $module;
                $buffer .= "</div>";
            }
            $buffer .= "<div class=\"null\"></div>";
        }

        $buffer .= "</div>";
        $buffer .= "<div class=\"null\"></div>";

        return $buffer;
    }

    /**
     * @brief Crea una navata nell'interfaccia interattiva di gestione template a blocchi
     *
     * @param array $matches
     *   - $matches[0] complete matching 
     *   - $matches[1] match open tag, es. <div id="nav_1_1" style="float:left;width:200px">
     *   - $matches[3] match div id, es. nav_1_1
     *   - $matches[4] match div content, es. {module classid=20 func=blockList}
     *   - $matches[5] match close tag, es. </div>
     * @return html
     */
    private function renderNave($matches) {

        Loader::import('page', 'PageEntry');
        Loader::import('sysClass', 'ModuleApp');
        Loader::import('module', 'ModuleInstance');

        $buffer = $matches[1];
        $buffer .= $this->cellCtrl($matches[3]);
        $buffer .= "<div id=\"sortables_".$matches[3]."\">";
        $count = 0;
        foreach(explode("\n", $matches[4]) as $mdlMarker) {
            if(preg_match("#module#", $mdlMarker)) {
                $mdlMarker = preg_replace("#[\r\n]#", "", $mdlMarker);
                preg_match("#\s(\w+)id=([0-9]+)\s*(\w+=(\w+))?#", $mdlMarker, $m);
                $mdlId = (!empty($m[2]))? $m[2]:null;
                $mdlType = (!empty($m[1]))? $m[1]:null;

                if($mdlType=='page') {
                    $page = new \Gino\App\Page\PageEntry($mdlId);
                    $title = $page->title;
                    $jsurl = $page->getUrl();
                }
                elseif($mdlType=='class' || $mdlType=='class') {
                    $module = new \Gino\App\Module\ModuleInstance($mdlId);
                    $classname = $module->className();
                    $title = $module->label;
                    $mdlFunc = $m[4];
                    $output_functions = (method_exists($module->classNameNs(), 'outputFunctions')) ? call_user_func(array($module->classNameNs(), 'outputFunctions')):array();
                    $title .= " - ".$output_functions[$mdlFunc]['label'];
                    $jsurl = $this->_registry->router->link($module->name, $mdlFunc);
                }
                elseif($mdlType=='class' || $mdlType=='sysclass') {
                    $module_app = new \Gino\App\SysClass\ModuleApp($mdlId);
                    $classname = $module_app->className();
                    $title = $module_app->label;
                    $mdlFunc = $m[4];
                    $output_functions = (method_exists($module_app->classNameNs(), 'outputFunctions'))? call_user_func(array($module_app->classNameNs(), 'outputFunctions')):array();
                    $title .= " - ".$output_functions[$mdlFunc]['label'];
                    $jsurl = $this->_registry->router->link($classname, $mdlFunc);
                }
                elseif($mdlType=='' && $mdlId == 0) {
                    $title = _("Modulo da url");
                    $jsurl = null;
                }
                else throw new \Exception(_("Tipo di modulo sconosciuto"));

                $buffer .= "<div id=\"mdlContainer_".$matches[3]."_$count\">";
                $buffer .= "<div class=\"mdlContainerCtrl\">";
                $buffer .= "<div class=\"disposeMdl\"></div>";
                $buffer .= "<div class=\"sortMdl\"></div>";
                //$buffer .= "<div class=\"toggleMdl\"></div>";
                $buffer .= "<div class=\"null\"></div>";
                $buffer .= "</div>";
                $buffer .= "<div id=\"refillable_".$matches[3]."_$count\" class=\"refillableFilled\">";
                $buffer .= "<input type=\"hidden\" name=\"navElement\" value=\"".$mdlMarker."\" />";
                $buffer .= "<div>".htmlChars($title)."</div>";
                $buffer .= "</div>";
                $buffer .= "<div id=\"fill_".$matches[3]."_$count\" style=\"display:none;\"></div>";
                $buffer .= "</div>";

                //if($jsurl) {
                    //$buffer .= "<script>gino.ajaxRequest('post', '$jsurl', '', 'fill_".$matches[3]."_$count', {'script':true})</script>";
                //}
                $count++;
            }
        }

        $buffer .= "<div id=\"mdlContainer_".$matches[3]."_$count\">";
        $buffer .= "<div class=\"mdlContainerCtrl\">";
        $buffer .= "<div class=\"disposeMdlDisabled\"></div>";
        $buffer .= "<div class=\"sortMdlDisabled\"></div>";
        //$buffer .= "<div class=\"toggleMdlDisabled\"></div>";
        $buffer .= "<div class=\"null\"></div>";
        $buffer .= "</div>";
        $buffer .= "<div id=\"refillable_".$matches[3]."_$count\" class=\"refillable\">";
        $buffer .= "</div>";
        $buffer .= "<div id=\"fill_".$matches[3]."_$count\" style=\"display:none;\"></div>";
        $buffer .= "</div>";

        $buffer .= "</div>";
        $buffer .= "<div class=\"navSizeCtrl\"> &nbsp; <div class=\"widthCtrl\"></div></div>";
        $buffer .= $matches[5];

        return $buffer;
    }

    /**
     * @brief Controlli di una cella nell'interfaccia interattiva di gestione template a blocchi
     * @param int $id id cella
     * @return html
     */
    private function cellCtrl($id) {

        $buffer = "<div class=\"navCtrl\">";
        $buffer .= "<div class=\"left\">$id &#160;</div>";
        $buffer .= "<div class=\"left\"><span class=\"navWidth\"></span></div>";
        $buffer .= "<div class=\"right\">";
        $buffer .= "<div class=\"fineMoreWidthCtrl\" title=\""._("aumenta larghezza")."\"></div>";
        $buffer .= "<div class=\"fineLessWidthCtrl\" title=\""._("diminuisci larghezza")."\"></div>";
        $buffer .= "<div class=\"floatCtrl\" title=\""._("modifica proprietà float")."\"></div>";
        $buffer .= "<div class=\"disposeCtrl\" title=\""._("elimina navata")."\"></div>";
        $buffer .= "</div>";
        $buffer .= "<div class=\"null\"></div>";

        $buffer .= "</div>";    

        return $buffer;
    }

    /**
     * @brief Processa il form di inserimento/modifica template
     * 
     * @see formTemplate()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function actionTemplate($request) {

        $tplContent = $request->POST['tplform_text'];
        if(get_magic_quotes_gpc()) $tplContent = stripslashes($tplContent);    // magic_quotes_gpc = On

        $this->free = 0;
        $this->label = cleanVar($request->POST, 'label', 'string', '');
        $this->description = cleanVar($request->POST, 'description', 'string', '');
        $tplFilename = cleanVar($request->POST, 'filename', 'string', '');
        if($tplFilename) $this->filename = $tplFilename.".tpl";
        $modTpl = cleanVar($request->POST, 'modTpl', 'int', '');
        
        $action = ($this->id) ? "modify":"insert";

        $link_error = $this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'template', 'action' => $action));

        if(!$this->id && is_file(TPL_DIR.OS.$this->filename.".tpl")) 
            return Error::errorMessage(array('error'=>_("Nome file già presente")), $link_error);

        if($fp = @fopen(TPL_DIR.OS.$this->filename, "wb")) {
        	if(!fwrite($fp, $tplContent)) {
        		return Error::errorMessage(array('error'=>_("Impossibile scrivere il file")), $link_error);
            }

            fclose($fp);
        }
        else {
        	return Error::errorMessage(array('error'=>_("Impossibile creare il file"), 'hint'=>_("Controllare i permessi in scrittura all'interno della cartella ".TPL_DIR.OS)), $link_error);
        }

        $this->save();

        //if(($this->id && $modTpl == 1) || !$this->id)
        if($this->id)
        {
            $blocks_number = cleanVar($request->POST, 'blocks_number', 'int', '');
            $blocks_del = cleanVar($request->POST, 'blocks_del', 'string', '');
            $blocks_del = json_decode(base64_decode($blocks_del));

            if(sizeof($blocks_del) > 0)
            {
                foreach($blocks_del AS $key=>$value)
                {
                    $this->_db->delete(self::$table_block, "id='$key'");
                }
            }

            for($i=1; $i<=$blocks_number; $i++) {

                $bid = cleanVar($request->POST, 'id_'.$i, 'int', '');
                $width = cleanVar($request->POST, 'width_'.$i, 'int', '');
                $um = cleanVar($request->POST, 'um_'.$i, 'int', '');
                $align = cleanVar($request->POST, 'align_'.$i, 'int', '');
                $rows = cleanVar($request->POST, 'rows_'.$i, 'int', '');
                $cols = cleanVar($request->POST, 'cols_'.$i, 'int', '');
                
                // Queste forzature vengono effettuate per mantenere invariata la struttura dei campi width, um e align della tabella sys_layout_tpl_block (NOT NULL)
                // @todo occorrerebbe cambiare i campi width, um e align in NULL e verificare il funzionamento dei template, visto che si aspettano il valore 0
                if($width === null) {
                	$width = 0;
                }
                if($um === null) {
                	$um = 0;
                }
                if($align === null) {
                	$align = 0;
                }
                // /End

                if($width == 0) $um = 0;
                if($rows > 0 && $cols > 0) {
                    $this->saveBlock($bid, $i, $width, $um, $align, $rows, $cols);
                }
            }
        }

        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), 'block=template'));
    }

    /**
     * @brief Salvataggio di un blocco
     * @param int $id id blocco
     * @param int $position posizione
     * @param int $width larghezza
     * @param int $um unita di misura (1: px, 2: %)
     * @param int $align allineamento (1: sinistra, 2: centrato, 3: destra)
     * @param int $rows numero righe
     * @param int $cols numero colonne
     * @return risultato, bool
     */
    private function saveBlock($id, $position, $width, $um, $align, $rows, $cols) {

        if($id)
        {
            $cnt = $this->_db->getNumRecords(self::$table_block, "id='$id' AND position='$position'");
            if($cnt)
            {
                $res = $this->_db->update(array(
                'width' => $width,
                'um' => $um,
                'align' => $align,
                'rows' => $rows,
                'cols' => $cols
                ), self::$table_block, "id='$id'");
                return $res;
            }
            else
            {
                $this->_db->delete(self::$table_block, "id='$id'");
                $res = $this->_db->insert(array(
                'tpl' => $this->id,
                'position' => $position,
                'width' => $width,
                'um' => $um,
                'align' => $align,
                'rows' => $rows,
                'cols' => $cols
                ), self::$table_block);
                return $res;
            }
        }
        else
        {
            $res = $this->_db->insert(array(
            'tpl' => $this->id,
            'position' => $position,
            'width' => $width,
            'um' => $um,
            'align' => $align,
            'rows' => $rows,
            'cols' => $cols
            ), self::$table_block);
            
            return $res;
        }
    }

    /**
     * @brief Eliminazione blocchi template
     * @return risultato operazione, bool
     */
    private function deleteBlocks() {

        return $this->_db->delete(self::$table_block, "tpl='".$this->id."'");
    }

    /**
     * @brief Processa il form di duplicazione template a blocchi
     * @see self::formCopyTemplate()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function actionCopyTemplate(\Gino\Http\Request $request) {

        $gform = Loader::load('Form', array());
        //$gform->setValidation(false);
        $gform->saveSession('dataform');
        $req_error = $gform->checkRequired();

        $ref = cleanVar($request->POST, 'ref', 'int', '');
        $label = cleanVar($request->POST, 'label', 'string', '');
        $filename = cleanVar($request->POST, 'filename', 'string', '');
        $description = cleanVar($request->POST, 'description', 'string', '');

        if($filename) $filename = $filename.'.tpl';

        $link_error = $this->_registry->router->link($this->_interface, 'manageLayout', array(), 'block=template&id=$ref&action=copy');

        if($req_error > 0) 
            return Error::errorMessage(array('error'=>1), $link_error);

        // Valori del template da duplicare
        $obj = new Template($ref);

        if(is_file(TPL_DIR.OS.$filename)) {
            return Error::errorMessage(array('error'=>_("Nome file già presente")), $link_error);
        }
        else {
            if(!copy(TPL_DIR.OS.$obj->filename, TPL_DIR.OS.$filename)) {
            	return Error::errorMessage(array('error'=>_("Impossibile creare il file").' '.$filename, 'hint'=>_("Controllare i permessi in scrittura all'interno della cartella ".TPL_DIR.OS)), $link_error);
            }
        }

        $db = Db::instance();
        $db->insert(array(
            'filename' => $filename,
            'label' => $label,
            'description' => $description
        ), self::$table);
        $id = $db->getlastid(self::$table);

        $rows = $db->select('*', self::$table_block, "tpl='$ref'");
        if($rows and count($rows))
        {
            foreach($rows AS $row)
            {
                $db->insert(array(
                'tpl' => $id,
                'position' => $row['position'],
                'width' => $row['width'],
                'um' => $row['um'],
                'align' => $row['align'],
                'rows' => $row['rows'],
                'cols' => $row['cols']
                ), self::$table_block);
            }
        }

        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), 'block=template'));
    }
}

Template::$columns=Template::columns();