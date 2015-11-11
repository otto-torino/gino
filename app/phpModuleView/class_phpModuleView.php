<?php
/**
 * @file class_phpModuleView.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.PhpModuleView.phpModuleView
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.PhpModuleView
 * @description Namespace dell'applicazione PhpModuleView, che gestisce output generati direttamente da codice php salvato du database
 */
namespace Gino\App\PhpModuleView;

use \Gino\View;
use \Gino\Document;

require_once('class.PhpModule.php');

/**
 * @brief Permette la creazione di moduli di classe in grado di eseguire codice php completamente personalizzabile
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##PROCEDURA
 * - si crea un modulo di classe selezionando la classe phpModuleView
 * - il modulo diventa visibile nella sezione moduli
 * - selezionando il modulo creato si accede alle funzionalità della classe phpModuleView
 * - nella sezione @a Contenuto è possibile scrivere direttamente il codice php
 * 
 * Per precauzione tutte le funzioni di php che permettono di eseguire programmi direttamente sulla macchina sono vietate.
 * Nel caso in cui venisse rilevata la presenza di una di queste funzioni il codice non verrebbe eseguito e l'output risultante sarebbe nullo.
 * 
 * Per una corretta integrazione dell'output prodotto all'interno del layout del sito, si consiglia di non utilizzare le funzioni per la stampa diretta @a echo e @a print, ma di immagazzinare tutto l'output all'interno della variabile @a $buffer, che verrà stampata all'interno del layout. 
 * Si consiglia di fare molta attenzione perché nonostante l'accesso alle funzionalità più pericolose del php sia proibito, si ha un controllo completo sulle variabili, ed in caso di cattivo uso del modulo si potrebbe seriamente compromettere la visualizzazione del modulo o dell'intero sito.
 * 
 * ##PERMESSI
 * - can_admin, amministrazione completa modulo
 * 
 */
class phpModuleView extends \Gino\Controller {

    private $_tbl_opt;
    private $_blackList;

    private $_title, $_title_visible;

    private $_options;
    public $_optionsLabels;

    function __construct($mdlId){

        parent::__construct($mdlId);

        $this->_tbl_opt = "php_module_opt";

        // options
        $this->_title = \Gino\htmlChars($this->setOption('title', true));
        $this->_title_visible = \Gino\htmlChars($this->setOption('title_vis'));

        // the second paramether will be the class instance
        $this->_options = \Gino\Loader::load('Options', array($this));
        $this->_optionsLabels = array(
            "title"=>_("Titolo"),
            "title_vis"=>_("Titolo visibile")
        );

        $this->_blackList = array("exec", "passthru", "proc_close", "proc_get_status", "proc_nice", "proc_open", "proc_terminate", "shell_exec", "system");
    }

    /**
     * @brief Restituisce alcune proprietà della classe
     * @return array associativo contenente le tabelle, viste e struttura directory contenuti
     */
    public static function getClassElements() {

        return array(
            "tables"=>array('php_module', 'php_module_opt'),
            "css"=>array('phpModule.css'),
            "folderStructure"=>array(
                CONTENT_DIR.OS.'phpModule'=> null
            )
        );
    }

    /**
     * @brief Eliminazione di una istanza
     *
     * @return risultato operazione, bool
     */
    public function deleteInstance() {

        $this->requirePerm('can_admin');

        $phpMdl = PhpModule::getFromInstance($this);
        $phpMdl->deleteDbData();

        /*
         * delete record and translation from table php_module_opt
         */
        $opt_id = $this->_registry->db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
        $this->_trd->deleteTranslations($this->_tbl_opt, $opt_id);

        $result = $this->_registry->db->delete($this->_tbl_opt, "instance='$this->_instance'");

        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            @unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }
        foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
            \Gino\deleteFileDir($fld.OS.$this->_instance_name, true);
        }

        return $result;
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (prende i metodi non presenti nel file ini) e dal motore di generazione di 
     * voci di menu (presenti nel file ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array associativo metodi pubblici metodo => array('label' => label, 'permissions' => permissions)
     */
    public static function outputFunctions() {

        $list = array(
            "viewList" => array("label"=>_("Visualizzazione modulo"), "permissions"=>array())
        );

        return $list;
    }

    /**
     * @brief Visualizzazione del modulo
     * @return Gino.Http.Response
     */
    public function viewList() {

        $registry = \Gino\registry::instance();

        $phpMdl = PhpModule::getFromInstance($this);
        $registry->addCss($this->_class_www."/phpModule_".$this->_instance_name.".css");

        $rexpf = array();
        foreach($this->_blackList as $fc) {
            $rexpf[] = $fc."\(.*?\)";
        }
        $rexp = "#".implode("|", $rexpf)."#";
        if(preg_match($rexp, $phpMdl->ml('content'))) {
            $buffer = '';
        }
        else eval($phpMdl->ml('content'));

        $view = new View();
        $view->setViewTpl('section');
        $view->assign('id', 'phpModuleView_'.$this->_instance_name);
        $view->assign('class', 'public');
        $view->assign('header', $this->_title);
        $view->assign('header_class', $this->_title_visible ? '' : 'hidden');
        $view->assign('content', $buffer);

        return $view->render();
    }

    /**
     *  @brief Interfaccia amministrativa per la gestione dei moduli di classe 'phpModuleView'
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageDoc(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $phpMdl = PhpModule::getFromInstance($this);

        if($request->checkGETKey('trnsl', '1'))
        {
            return $this->_trd->manageTranslation($request);
        }

        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_options = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=options'), _('Opzioni'));
        $link_edit = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'action=modify'), _('Contenuto'));
        $link_info = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Informazioni'));
        $sel_link = $link_info;

        $links_array = array($link_frontend, $link_options, $link_edit, $link_info);

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');
        $action = \Gino\cleanVar($request->GET, 'action', 'string', '');

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options') {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        else {

            if($action == 'save') {
                return $phpMdl->actionPhpModule($request);
            }

            if($action == 'modify') {
                $sel_link = $link_edit;
                $backend = $phpMdl->formPhpModule();
            }
            else {
                $backend = $this->info();
            }
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $view = new View();
        $view->setViewTpl('tab');
        $dict = array(
            'title' => $this->_instance_label,
            'links' => $links_array,
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Informazioni modulo
     * @return html, informazioni
     */
    private function info(){

        $buffer = "<p>"._("Il modulo permette di eseguire codice php completamente personalizzabile, e di visualizzare l'output prodotto. Per precauzione tutte le funzioni di php che permettono di eseguire programmi direttamente sulla macchina sono vietate. Nel caso in cui venisse rilevata la presenza di una di queste funzioni il codice non verrebbe eseguito e l'output risultante sarebbe nullo.")."</p>\n";
        $buffer .= "<p>"._("Per una corretta integrazione dell'output prodotto all'interno del layout del sito, si consiglia di <b>non</b> utilizzare le funzioni per la stampa diretta <b>echo</b> e <b>print</b>, ma di immagazzinare tutto l'output all'interno della variabile <b>\$buffer</b>, che verrà stampata all'interno del layout.")."</p>\n";
        $buffer .= "<p>"._("Si consiglia di fare molta attenzione perché nonostante l'accesso alle funzionalità più pericolose del php sia proibito, si ha un controllo completo sulle variabili, ed in caso di cattivo uso del modulo si potrebbe seriamente compromettere la visualizzazione del modulo o dell'intero sito.")."</p>\n";

        $view =     new View(null, 'section');
        $dict = array(
            'title' => _('Informazioni'),
            'class' => 'admin',
            'content' => $buffer
        );
        return $view->render($dict);
    }
}
