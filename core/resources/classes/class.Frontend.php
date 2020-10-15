<?php
/**
 * @file class.Frontend.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Frontend
 */

namespace Gino;

/**
 * @brief Libreria per la gestione dei file di front-end dei singoli moduli (css e viste)
 *
 * Possono essere selezionati e modificati i file con le seguenti caratteristiche: \n
 *   - i file css definiti nel metodo getClassElements() della classe del modulo
 *   - i file delle viste presenti nella directory @a views presente nella directory dell'applicazione
 *
 */
class Frontend {

    private $_registry, $_class, $_module_id;
    private $_module;
    private $_css_list, $_view_list;
    private $_mdlLink;

    /**
     * @brief Costruttore
     *
     * @param \Gino\Controller $controller istanza della classe di tipo Gino.Controller
     * @return void
     */
    function __construct($controller) {

        $db = Db::instance();
        $this->_registry = Registry::instance();

        Loader::import('sysClass', 'ModuleApp');
        Loader::import('module', 'ModuleInstance');

        $this->_class_name = $controller->getClassName();
        $this->_class = get_class($controller);
        $this->_module_id = $controller->getInstance();

        if($this->_module_id) {
            $this->_module = new \Gino\App\Module\ModuleInstance($this->_module_id);
        }
        else {
            $this->_module = \Gino\App\SysClass\ModuleApp::getFromName($this->_class_name);
        }

        $this->setLists();

        $method = $this->_module_id ? 'manageDoc' : 'manage'.ucfirst($this->_class_name);
        $this->_mdlLink = $this->_registry->router->link($this->_module->name, $method, array(), array('block' => 'frontend'));

    }

    /**
     * @brief Imposta le liste di file css e viste
     * @return void
     */
    private function setLists() {

        $this->_css_list = array();
        $this->_view_list = array();

        $classElements = call_user_func(array($this->_class, 'getClassElements'));

        if(isset($classElements['css'])) {
            foreach($classElements['css'] as $file) {
                $this->_css_list[] = array(
                    'filename' => $file,
                    'description' => ''
                );
            }
        }

        if(isset($classElements['views'])) {
            foreach($classElements['views'] as $file => $description) {
                $this->_view_list[] = array(
                    'filename' => $file,
                    'description' => $description
                );
            }
        }

    }

    /**
     * @brief Nome del file
     *
     * @description I Css e le viste vengono copiate quando si crea una nuova istanza di un modulo.
     *              Il nome viene modificato per comprendere anche il nome dell'istanza.
     *
     * @param string $file nome del file
     * @param string $ext estensione del file
     * @return string
     */
    private function fileName($file, $ext) {

        $name = $this->_module_id ? baseFileName($file)."_".$this->_module->name.".".$ext : $file;
        return $name;
    }

    /**
     * @brief Percorso assoluto della directory dei file di front-end
     *
     * @param string $code
     * @return string
     */
    private function pathToFile($code) {

        $dir = get_app_dir($this->_class_name).OS;

        if($code == 'css')
        {
            // stessa dir
        }
        elseif($code == 'view')
        {
            $dir .= 'views'.OS;
        }

        return $dir;
    }

    /**
     * @brief Interfaccia per la gestione dei file di front-end dei moduli
     *
     * @see self::moduleList()
     * @see self::formModuleFile()
     * @see self::actionModuleFile()
     * @return string
     */
    public function manageFrontend() {

        $request = \Gino\Http\Request::instance();
        $action = cleanVar($request->GET, 'action', 'string', '');
        $code = cleanVar($request->GET, 'code', 'string', '');

        if($action == 'modify') {
            $buffer = $this->formModuleFile($code, $request);
        }
        elseif($action == 'save') {
            return $this->actionModuleFile($code, $request);
        }
        else {
            $buffer = "<p class=\"backoffice-info\">"._('In questa sezione si può decidere l\'aspetto ed il modo di visualizzare le informazioni, modificando direttamente i fogli di stile e le viste utilizzati dal modulo.')."</p>";
            $buffer .= $this->moduleList('view');
            $buffer .= $this->moduleList('css');
        }

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => 'Frontend',
            'class' => 'admin',
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Tabella con lista elementi del modulo, css o viste
     *
     * @description Utilizza la libraria javascript CodeMirror
     * @param string $code 'css' o 'view'
     * @return string
     */
    private function moduleList($code) {

        if($code == 'css')
        {
            $items = $this->_css_list;
            $ext = 'css';
            $title = _("Fogli di stile");
        }
        elseif($code == 'view')
        {
            $items = $this->_view_list;
            $ext = 'php';
            $title = _("Viste");
        }
        
        $num_items = count($items);
        if($num_items) {
            $buffer = "<h2>".$title."</h2>";
            $view_table = new View(null, 'table');
            $view_table->assign('class', 'table table-striped table-hover');
            $tbl_rows = array();
            $tb_rows[] = array(
                'text' => 'Viste',
                'header' => true,
                'colspan' => 3
            );
            foreach($items as $k=>$v) {
                $filename = $this->fileName($v['filename'], $ext);
                $description = $v['description'];
                $link_modify = "<a href=\"$this->_mdlLink&key=$k&code=$code&action=modify\">".\Gino\icon('modify')."</a>";
                $tbl_rows[] = array(
                    $filename,
                    $description,
                    $link_modify
                );
            }
            $view_table->assign('rows', $tbl_rows);
            $buffer .= $view_table->render();
        }
        else {
            return '';
        }

        return $buffer;
    }

    /**
     * @brief Form di modifica file
     * @param string $code 'css' o 'view'
     * @param \Gino\Http\Request oggetto Gino.Http.Request
     * @return string
     */
    private function formModuleFile($code, $request) {

        $key = cleanVar($request->GET, 'key', 'int', '');
        
        $codemirror = \Gino\Loader::load('CodeMirror', array(['type' => $code]));
        
        $gform = Loader::load('Form', array(array('form_id'=>'gform')));
        $gform->load('dataform');
        
        if($code == 'css')
        {
            $list = $this->_css_list;
            $ext = 'css';
            $filename = $this->fileName($list[$key]['filename'], $ext);
            $title = sprintf(_("Modifica il foglio di stile \"%s\""), $filename);
        }
        elseif($code == 'view')
        {
            $list = $this->_view_list;
            $ext = 'php';
            $filename = $this->fileName($list[$key]['filename'], $ext);
            $title = sprintf(_("Modifica la vista \"%s\""), $filename);
        }
        
        $buffer = $gform->open($this->_mdlLink."&action=save&code=$code&key=$key", '', '');
        
        $contents = file_get_contents($this->pathToFile($code).$filename);

        $buffer .= $codemirror->inputText('file_content', $contents, ['classField' => null]);
        
        $buffer .= "<div class=\"form-group\">";
        $buffer .= \Gino\Input::submit('submit_action', _("salva"));
        $buffer .= \Gino\Input::submit('cancel_action', _("annulla"), ['onclick' => "location.href='$this->_mdlLink'"]);
        $buffer .= "</div>";
        
        $buffer .= $gform->close();

        $buffer .= $codemirror->renderScript();

        $view = new View(null, 'section');
        $dict = array(
            'title' => $title,
            'class' => null,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di modifica di un file
     * @param string $code 'css' o 'view'
     * @param \Gino\Http\Request oggetto Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    private function actionModuleFile($code, $request) {

        $key = cleanVar($request->GET, 'key', 'int', '');

        if($code == 'css')
        {
            $list = $this->_css_list;
            $ext = 'css';
        }
        elseif($code == 'view')
        {
            $list = $this->_view_list;
            $ext = 'php';
        }

        $filename = $this->fileName($list[$key]['filename'], $ext);

        $file_content = filter_input(INPUT_POST, 'file_content');
        $fo = fopen($this->pathToFile($code).$filename, 'wb');
        fwrite($fo, $file_content);
        fclose($fo);

        return new \Gino\Http\Redirect($this->_mdlLink);
    }
}
