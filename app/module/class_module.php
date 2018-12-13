<?php
/**
 * @file class_module.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Module.module
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Module
 * @description Namespace dell'applicazione Module, che gestisce la creazione/rimozione di istanze di moduli di sistema
 */
namespace Gino\App\Module;

use \Gino\View;
use \Gino\Document;
use \Gino\Error;
use \Gino\Http\Response;
use \Gino\Http\Redirect;

require_once 'class.ModuleInstance.php';

/**
 * @brief Classe di tipo Gino.Controller per la gestione di istanze di moduli di sistema
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class module extends \Gino\Controller {

    /**
     * @brief Costruttore
     * @return void, istanza di Gino.App.Module.module
     */
    function __construct(){

        parent::__construct();
    }

    /**
     * @brief Interfaccia amministrativa per la gestione dei moduli
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageModule(\Gino\Http\Request $request){

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->GET, 'id', 'int', '');
        $block = \Gino\cleanVar($request->GET, 'block', 'string', null);

        $module = new ModuleInstance($id);

        $link_dft = ['link' => $this->linkAdmin(), 'label' => _("Gestione istanze")];
        $sel_link = $link_dft;

        $action = \Gino\cleanVar($request->GET, 'action', 'string', null);

        if($request->checkGETKey('trnsl', '1')) {
        	return $this->_trd->manageTranslation($request);
        }
        elseif($action == 'insert') {
            $backend = $this->formModule($module);
        }
        elseif($action == 'modify') {
            $backend = $this->formModule($module);
            $backend .= $this->formActivateModule($module);

        }
        elseif($action == 'delete') {
            $backend = $this->formRemoveModule($module);
        }
        else {
            $backend = $this->listModule();
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $view = new View();
        $view->setViewTpl('tabs');
        $dict = array(
            'title' => _('Moduli istanziabili'),
            'links' => [$link_dft],
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Elenco dei moduli
     *
     * @param integer $sel_id valore ID del modulo selezionato
     * @return string, lista moduli
     */
    private function listModule(){

        $link_1 = '';

        $link_insert = "<a href=\"".$this->linkAdmin(array(), array('action' => 'insert'))."\">".\Gino\icon('insert', array('text' => _("nuova istanza"), 'scale' => 2))."</a>";

        $view_table = new View();
        $view_table->setViewTpl('table');

        $modules = ModuleInstance::objects(null, array('order' => 'label'));

        if(count($modules)) {
            \Gino\Loader::import('sysClass', 'ModuleApp');
            $GINO = "<p class=\"backoffice-info\">"._('Di seguito l\'elenco di tutte le istanze di moduli presenti nel sistema. Cliccare l\'icona di modifica per cambiare l\'etichetta e la descrizione del modulo. In caso di eliminazione tutti i dati ed i file verrano cancellati definitivamente.')."</p>";
            $heads = array(
                _('id'),
                _('etichetta'),
                _('modulo di sistema'),
                _('attivo'),
                '',
            );
            $tbl_rows = array();
            foreach($modules as $module) {
                $link_modify = "<a href=\"".$this->link($this->_class_name, 'manageModule', array(), "id=".$module->id."&action=modify")."\">".\Gino\icon('modify')."</a>";
				$link_delete = "<a href=\"".$this->link($this->_class_name, 'manageModule', array(), "id=".$module->id."&action=delete")."\">".\Gino\icon('delete')."</a>";
                $module_app = $module->moduleApp();
                $tbl_rows[] = array(
                    $module->id,
                    $module->ml('label'),
                    $module_app->label,
                    $module->active ? _('si') : _('no'),
                    implode(' &#160; ', array($link_modify, $link_delete))
                );
            }
            $dict = array(
                'class' => 'table table-striped table-hover',
                'heads' => $heads,
                'rows' => $tbl_rows
            );
            $GINO .= $view_table->render($dict);
        }
        else {
            $GINO = _('Non risultano istanze di moduli di sistema.');
        }

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Elenco moduli installati'),
            'class' => 'gino-admin',
            'header_class' => 'gino-admin-header',
            'header_links' => $link_insert,
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Form di eliminazione di un modulo
     * 
     * @param \Gino\App\Module\ModuleInstance $module istanza di Gino.App.Module.ModuleInstance
     * @return string, form
     */
    private function formRemoveModule($module) {

        $gform = \Gino\Loader::load('Form', array());
        $gform->load('dataform');

        $GINO = "<p class=\"lead\">"._("Attenzione! L'eliminazione del modulo comporta l'eliminazione di tutti i dati!")."</p>\n";

        $GINO .= $gform->open($this->link($this->_class_name, 'actionRemoveModule'), '', '');
        $GINO .= \Gino\Input::hidden('id', $module->id);
        
        $submit = \Gino\Input::submit('submit_action', _("elimina"), []);
        $GINO .= \Gino\Input::placeholderRow(_("sicuro di voler procedere?"), $submit);
        $GINO .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => sprintf(_('Eliminazione istanza "%s"'), $module->label),
            'class' => null,
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di eliminazione di un modulo
     * @see self::formRemoveModule()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionRemoveModule(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        $module = new ModuleInstance($id);

        $class = $module->classNameNs();
        $obj = new $class($id);
        // obj shoul delete table records
        $obj->deleteInstance();
        // this class deletes permissions assoc, css, views and contents
        $this->deleteModuleInstance($module);

        $module->deleteDbData();
        $this->_trd->deleteTranslations(TBL_MODULE, $id);

        return new Redirect($this->linkAdmin());
    }

    /**
     * @brief Elimina automaticamente associazioni con permessi, css, viste e contenuti di un'istanza di modulo
     * @param \Gino\App\Module\ModuleInstance $module istanza di Gino.App.Module.ModuleInstance
     * @return void
     */
    private function deleteModuleInstance($module) {
        // delete user perm assoc
        $this->_db->delete(TBL_USER_PERMISSION, "instance=\"".$module->id."\"");
        // delete group perm assoc
        $this->_db->delete(TBL_GROUP_PERMISSION, "instance=\"".$module->id."\"");

        $class = $module->classNameNs();
        $class_name = $module->className();
        $class_elements = $class::getClassElements();
        // delete css
        foreach($class_elements['css'] as $css) {
            @unlink(APP_DIR.OS.$class_name.OS.\Gino\baseFileName($css)."_".$module->name.".css");
        }
        // delete views
        $class_views = isset($class_elements['views']) ? $class_elements['views']: array();
        foreach($class_views as $view => $description) {
            @unlink(APP_DIR.OS.$class_name.OS.'views'.OS.\Gino\baseFileName($view)."_".$module->name.".php");
        }
        // delete contents
        $class_fs = isset($class_elements['folderStructure']) ? $class_elements['folderStructure']: array();
        foreach($class_fs as $fld=>$fldStructure) {
            \Gino\deleteFileDir($fld.OS.$module->name, TRUE);
        }
    }

    /**
     * @brief Form di inserimento/modifica di un modulo
     * @param \Gino\App\Module\ModuleInstance $module istanza di Gino.App.Module.ModuleInstance
     * @return string, form
     */
    private function formModule($module) {

        \Gino\Loader::import('sysClass', 'ModuleApp');
        $module_app = $module->moduleApp();
        $modules_app = \Gino\App\SysClass\ModuleApp::objects(null, array('where' => "instantiable='1' AND active='1'", 'order' => 'label'));

        $gform = \Gino\Loader::load('Form', array());
        $gform->load('dataform');

        $GINO = $gform->open($this->link($this->_class_name, 'actionModule'), '', 'name,label');
        $GINO .= \Gino\Input::hidden('id', $module->id);
        $GINO .= \Gino\Input::select_label('module_app', $gform->retvar('module_app', $module_app->id), \Gino\App\SysClass\ModuleApp::getSelectOptionsFromObjects($modules_app), _("Modulo"), array("required"=>true, 'other' => $module->id ? 'disabled' : ''));
        $GINO .= \Gino\Input::input_label('name', 'text', $gform->retvar('name', $module->name), array(_("Nome"), _("Deve contenere solamente caratteri alfanumerici o il carattere '_'")), array("required"=>true, "size"=>40, "maxlength"=>200, "pattern"=>"^[\w\d_]*$", "hint"=>_("solo caratteri alfanumerici o underscore"), 'other' => $module->id ? 'disabled' : ''));
        $GINO .= \Gino\Input::input_label('label', 'text', $gform->retvar('label', $module->label), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>TBL_MODULE, "trnsl_id"=>$module->id));
        $GINO .= \Gino\Input::textarea_label('description', $gform->retvar('description', $module->description), _("Descrizione"), array("cols"=>45, "rows"=>4, "trnsl"=>true, "trnsl_table"=>TBL_MODULE, "trnsl_id"=>$module->id));
        
        $submit = \Gino\Input::submit('submit_action', _("salva"), []);
        $GINO .= \Gino\Input::placeholderRow(null, $submit);
        $GINO .= $gform->close();
        
        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $module->id ? sprintf(_('Modifica istanza "%s"'), $module->label) : _('Nuova istanza'),
            'class' => null,
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di inserimento/modifica modulo
     * @see self::formModule()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionModule(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', null);
        $module = new ModuleInstance($id);

        if($module->id) {
            return $this->actionEditModule($request);
        }
        else {
            return $this->actionInsertModule($request);
        }

    }

    /**
     * @brief Processa il form di inserimento di un modulo
     * @see self::formModule()
     * @see self::actionModule()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    private function actionInsertModule(\Gino\Http\Request $request) {

        \Gino\Loader::import('sysClass', 'ModuleApp');

        $gform = \Gino\Loader::load('Form', array('gform','post', true));
        $gform->saveSession('dataform');
        $req_error = $gform->checkRequired();

        $name = \Gino\cleanVar($request->POST, 'name', 'string', '');
        $module_app_id = \Gino\cleanVar($request->POST, 'module_app', 'string', '');
        $label = \Gino\cleanVar($request->POST, 'label', 'string', '');
        $description = \Gino\cleanVar($request->POST, 'description', 'string', '');

        $module = new ModuleInstance(null);
        $module_app = new \Gino\App\SysClass\ModuleApp($module_app_id);

        $link_error = $this->linkAdmin(array(), array('action' => 'insert'));

        if($req_error > 0) {
            return error::errorMessage(array('error'=>1), $link_error);
        }

        // check name
        if(ModuleInstance::getFromName($name)) {
            return Error::errorMessage(array('error'=>_("è già presente un modulo con lo stesso nome")), $link_error);
        }

        if(preg_match("/[^\w]/", $name)) {
            return Error::errorMessage(array('error'=>_("il nome del modulo contiene caratteri non permessi")), $link_error);
        }

        $class = $module_app->classNameNs();
        $class_name = $module_app->className();
        $class_elements = call_user_func(array($class, 'getClassElements'));
        /*
         * create css files
         */
        $css_files = $class_elements['css'];
        foreach($css_files as $css_file) {

            $css_content = file_get_contents(APP_DIR.OS.$class_name.OS.$css_file);

            $base_css_name = \Gino\baseFileName($css_file);

            if(!($fo = @fopen(APP_DIR.OS.$class_name.OS.$base_css_name.'_'.$name.'.css', 'wb'))) {
                return Error::errorMessage(array('error'=>_("impossibile creare i file di stile"), 'hint'=>_("controllare i permessi in scrittura")), $link_error);
            }

            $reg_exp = "/#(.*?)".$class_name." /";
            $replace = "#$1".$class_name."-".$name." ";
            $content = preg_replace($reg_exp, $replace, $css_content);

            fwrite($fo, $content);
            fclose($fo);
        }
        /*
         * create view files
         */
        $view_files = isset($class_elements['views']) ? $class_elements['views'] : array();
        foreach($view_files as $view_file => $vdescription) {

            $view_content = file_get_contents(APP_DIR.OS.$class_name.OS.'views'.OS.$view_file);

            $base_view_name = \Gino\baseFileName($view_file);

            if(!($fo = @fopen(APP_DIR.OS.$class_name.OS.'views'.OS.$base_view_name.'_'.$name.'.php', 'wb'))) {
                return Error::errorMessage(array('error'=>_("impossibile creare i file delle viste"), 'hint'=>_("controllare i permessi in scrittura")), $link_error);
            }

            $content = $view_content;

            fwrite($fo, $content);
            fclose($fo);
        }
        /*
         * create folder structure
         */
        $folder_structure = (isset($class_elements['folderStructure'])) ? $class_elements['folderStructure'] : array();
        if(count($folder_structure)) {
            foreach($folder_structure as $k=>$v) {
                $dir = $k.OS.$name;
            	if(!is_dir($dir)) {
                	mkdir($dir);
                	$this->createMdlFolders($dir, $v);
                }
            }
        }

        $module->label = $label;
        $module->name = $name;
        $module->module_app = $module_app_id;
        $module->active = 1;
        $module->description = $description;

        $module->save();
        
        return new Redirect($this->linkAdmin());
    }

    /**
     * @brief Creazione cartelle contenuti modulo
     * @param string $pdir directory di riferimento
     * @param array $nsdir array di sottodirectory della direcotry di riferimento
     * @return TRUE
     */
    private function createMdlFolders($pdir, $nsdir) {

        // if next structure is null break
        if(!$nsdir) return TRUE;
        elseif(is_array($nsdir)) {
            foreach($nsdir as $k=>$v) {
                mkdir($pdir.OS.$k);
                $this->createMdlFolders($pdir.OS.$k, $v);
            }
        }
        return TRUE;
    }

    /**
     * @brief Processa il form di modifica di un modulo
     * @see self::formModule()
     * @see self::actionModule()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    private function actionEditModule(\Gino\Http\Request $request) {

        $gform = \Gino\Loader::load('Form', array('gform', 'post', true));
        $gform->saveSession('dataform');

        $id = \Gino\cleanVar($request->POST, 'id', 'int');
        $module = new ModuleInstance($id);

        $link_error = $this->link($this->_class_name, 'manageModule', array(), "id=$id&action=modify");

        $label = \Gino\cleanVar($request->POST, 'label', 'string', '');
        if(!$label) {
            return Error::errorMessage(array('error'=>1), $link_error);
        }

        $module->label = $label;
        $module->description = \Gino\cleanVar($request->POST, 'description', 'string', '');

        $module->save();

        return new Redirect($this->linkAdmin());

    }

    /**
     * @brief Form di attivazione e disattivazione di un modulo
     *
     * @param \Gino\App\Module\ModuleInstance $module istanza di Gino.App.Module.ModuleInstance
     * @return string, form
     */
    private function formActivateModule($module) {

        $gform = \Gino\Loader::load('Form', array());
        $gform->load('dataform');

        $GINO = '';
        if($module->active) {
            $GINO .= "<p>"._('Prima di disattivare un modulo assicurarsi di aver rimosso ogni suo output da tutti i template.')."</p>";
        }

        $GINO .= $gform->open($this->link($this->_class_name, 'actionEditModuleActive'), '', '');
        $GINO .= \Gino\Input::hidden('id', $module->id);
        
        $submit = \Gino\Input::submit('submit_action', $module->active ? _('disattiva') : _('attiva'), []);
        $GINO .= \Gino\Input::placeholderRow(_('Sicuro di voler procedere?'), $submit);
        $GINO .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $module->active ? _('Disattivazione') : _('Attivazione'),
            'class' => null,
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di attivazione e disattivazione di un modulo
     * @see self::formActivateModule()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionEditModuleActive(\Gino\Http\Request $request) {

        $this->require_pem('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');

        $module = new ModuleInstance($id);

        $module->active = $module->active ? 0 : 1;
        $module->save();

        return new Redirect($this->linkAdmin());
    }
}
