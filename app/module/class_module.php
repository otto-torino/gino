<?php
/**
 * @file class_module.php
 * @brief Contiene la classe module
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

require_once('class.ModuleInstance.php');

/**
 * @brief Libreria per la gestione dei moduli
 * 
 * Modifica, installazione e rimozione dei moduli di classi istanziate e moduli funzione
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class module extends Controller {

  private $_action;

  function __construct(){

    parent::__construct();

    $this->_action = cleanVar($_REQUEST, 'action', 'string', '');

  }

  /**
   * Interfaccia amministrativa per la gestione dei moduli
   * 
   * @see $_access_2
   * @return string
   */
  public function manageModule(){

    $this->requirePerm('can_admin');

    $id = cleanVar($_GET, 'id', 'int', '');
    $block = cleanVar($_GET, 'block', 'string', null);

    $module = new ModuleInstance($id);

    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageModule]\">"._("Gestione istanze")."</a>";
    $sel_link = $link_dft;

    $action = cleanVar($_GET, 'action', 'string', null);
    if(isset($_GET['trnsl']) and $_GET['trnsl'] == '1') {
      if(isset($_GET['save']) and $_GET['save'] == '1') {
        $this->_trd->actionTranslation();
      }
      else {
        $this->_trd->formTranslation();
      }
    }
    elseif($action == 'insert') {
      $GINO = $this->formModule($module);
    }
    elseif($action == 'modify') {
      $GINO = $this->formModule($module);
      $GINO .= $this->formActivateModule($module);

    }
    elseif($action == 'delete') {
      $GINO = $this->formRemoveModule($module);
    }
    else {
      $GINO = $this->listModule();
    }
 
    $view = new view();
    $view->setViewTpl('tab');
    $dict = array(
      'title' => _('Moduli istanziabili'),
      'links' => $link_dft,
      'selected_link' => $sel_link,
      'content' => $GINO
    );

    return $view->render($dict);

  }

  /**
   * Elenco dei moduli
   * 
   * @param integer $sel_id valore ID del modulo selezionato
   * @return string
   */
  private function listModule(){

    $link_1 = '';

    $link_insert = "<a href=\"$this->_home?evt[$this->_class_name-manageModule]&amp;action=insert\">".pub::icon('insert', array('text' => _("nuova istanza"), 'scale' => 2))."</a>";

    $view_table = new view();
    $view_table->setViewTpl('table');

    $modules = ModuleInstance::get(array('order' => 'label'));

    if(count($modules)) {
      loader::import('sysClass', 'ModuleApp');
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
        $link_modify = "<a href=\"$this->_home?evt[$this->_class_name-manageModule]&id=".$module->id."&action=modify\">".pub::icon('modify')."</a>";
        $link_delete = "<a href=\"$this->_home?evt[$this->_class_name-manageModule]&id=".$module->id."&action=delete\">".pub::icon('delete')."</a>";
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

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Elenco moduli installati'),
      'class' => 'admin',
      'header_links' => $link_insert,
      'content' => $GINO
    );

    return $view->render($dict);
  }

  /**
   * Form di eliminazione di un modulo
   * 
   * @param ModuleInstance $module istanza di ModuleInstance
   * @return string
   */
  private function formRemoveModule($module) {
    
    $gform = Loader::load('Form', array('gform', 'post', true));
    $gform->load('dataform');

    $GINO = "<p class=\"lead\">"._("Attenzione! L'eliminazione del modulo comporta l'eliminazione di tutti i dati!")."</p>\n";

    $required = '';
    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionRemoveModule]", '', $required);
    $GINO .= $gform->hidden('id', $module->id);
    $GINO .= $gform->cinput('submit_action', 'submit', _("elimina"), _("sicuro di voler procedere?"), array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => sprintf(_('Eliminazione istanza "%s"'), $module->label),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }
  
  /**
   * Eliminazione di un modulo
   * 
   * @see $_access_2
   */
  public function actionRemoveModule() {

    $this->requirePerm('can_admin');

    $id = cleanVar($_POST, 'id', 'int', '');
    $module = new ModuleInstance($id);

    $class = $module->className();
    $obj = new $class($id);
    // obj shoul delete table records
    $obj->deleteInstance();
    // this class deletes permissions assoc, css, views and contents
    $this->deleteModuleInstance($module);

    $module->deleteDbData();
    $this->_trd->deleteTranslations(TBL_MODULE, $id);

    Link::HttpCall($this->_home, $this->_class_name.'-manageModule', '');

  }

  /**
   * Elimina automaticamente associazioni con permessi, css, viste e contenuti di un'istanza di modulo
   */
  private function deleteModuleInstance($module) {
    // delete user perm assoc
    $this->_db->delete(TBL_USER_PERMISSION, "instance=\"".$module->id."\"");
    // delete group perm assoc
    $this->_db->delete(TBL_GROUP_PERMISSION, "instance=\"".$module->id."\"");

    $class = $module->className();
    $class_elements = $class::getClassElements();
    // delete css
    foreach($class_elements['css'] as $css) {
      @unlink(APP_DIR.OS.$class.OS.baseFileName($css)."_".$module->name.".css");
    }
    // delete views
    foreach($class_elements['views'] as $view => $description) {
      @unlink(APP_DIR.OS.$class.OS.'views'.OS.baseFileName($view)."_".$module->name.".php");
    }
    // delete contents
    foreach($class_elements['folderStructure'] as $fld=>$fldStructure) {
      $this->_registry->pub->deleteFileDir($fld.OS.$module->name, true);
    }
  }

  /**
   * Form di inserimento di un modulo - scelta del tipo di modulo
   * @param ModuleInstance $module istanza di ModuleInstance
   * @see $_mdlTypes
   * @see formModule()
   */
  private function formModule($module) {

    loader::import('sysClass', 'ModuleApp');
    $module_app = $module->moduleApp();
    $modules_app = ModuleApp::get(array('where' => "instantiable='1' AND active='1'", 'order' => 'label'));

    $gform = loader::load('Form', array('gform', 'post', true, array("trnsl_table"=>TBL_MODULE, "trnsl_id"=>$module->id)));
    $gform->load('dataform');

    $required = 'name,label';
    $GINO = $gform->open($this->_home."?evt[".$this->_class_name."-actionModule]", '', $required);
    $GINO .= $gform->hidden('id', $module->id);
    $GINO .= $gform->cselect('module_app', $gform->retvar('module_app', $module_app->id), ModuleApp::getSelectOptionsFromObjects($modules_app), _("Modulo"), array("required"=>true, 'other' => $module->id ? 'disabled' : ''));
    $GINO .= $gform->cinput('name', 'text', $gform->retvar('name', $module->name), array(_("Nome"), _("Deve contenere solamente caratteri alfanumerici o il carattere '_'")), array("required"=>true, "size"=>40, "maxlength"=>200, "pattern"=>"^[\w\d_]*$", "hint"=>_("solo caratteri alfanumerici o underscore"), 'other' => $module->id ? 'disabled' : ''));
    $GINO .= $gform->cinput('label', 'text', $gform->retvar('label', $module->label), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"label"));
    $GINO .= $gform->ctextarea('description', $gform->retvar('description', $module->description), _("Descrizione"), array("cols"=>45, "rows"=>4, "trnsl"=>true, "field"=>"description"));
    $GINO .= $gform->cinput('submit_action', 'submit', _("salva"), '', array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => $module->id ? sprintf(_('Modifica istanza "%s"'), $module->label) : _('Nuova istanza'),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }

  public function actionModule() {

    $this->requirePerm('can_admin');
    $id = cleanVar($_POST, 'id', 'int', null);
    $module = new ModuleInstance($id);

    if($module->id) {
      $this->actionEditModule();
    }
    else {
      $this->actionInsertModule();
    }

    Link::HttpCall($this->_home, $this->_class_name.'-manageModule', '');

  }

  /**
   * Inserimento di un nuovo modulo
   * 
   * @see $_access_2
   */
  private function actionInsertModule() {

    loader::import('sysClass', 'ModuleApp');

    $gform = loader::load('Form', array('gform','post', true));
    $gform->save('dataform');
    $req_error = $gform->arequired();

    $name = cleanVar($_POST, 'name', 'string', '');
    $module_app_id = cleanVar($_POST, 'module_app', 'string', '');
    $label = cleanVar($_POST, 'label', 'string', '');
    $description = cleanVar($_POST, 'description', 'string', '');

    $module = new ModuleInstance(null);
    $module_app = new ModuleApp($module_app_id);

    $link_error = $this->_home."?evt[$this->_class_name-manageModule]&action=insert";

    if($req_error > 0) {
      exit(error::errorMessage(array('error'=>1), $link_error));
    }

    // check name
    if(ModuleInstance::getFromName($name)) {
      exit(error::errorMessage(array('error'=>_("è già presente un modulo con lo stesso nome")), $link_error));
    }

    if(preg_match("/[^\w]/", $name)) {
      exit(error::errorMessage(array('error'=>_("il nome del modulo contiene caratteri non permessi")), $link_error));
    }

    $class = $module_app->name;
    $class_elements = call_user_func(array($class, 'getClassElements'));
    /*
     * create css files
     */
    $css_files = $class_elements['css'];
    foreach($css_files as $css_file) {

      $css_content = file_get_contents(APP_DIR.OS.$class.OS.$css_file);

      $base_css_name = baseFileName($css_file);

      if(!($fo = @fopen(APP_DIR.OS.$class.OS.$base_css_name.'_'.$name.'.css', 'wb'))) {
        exit(error::errorMessage(array('error'=>_("impossibile creare i file di stile"), 'hint'=>_("controllare i permessi in scrittura")), $link_error));
      }

      $reg_exp = "/#(.*?)".$class." /";
      $replace = "#$1".$class."_".$name." ";
      $content = preg_replace($reg_exp, $replace, $css_content);

      fwrite($fo, $content);
      fclose($fo);
    }
    /*
     * create view files
     */
    $view_files = $class_elements['views'];
    foreach($view_files as $view_file => $description) {

      $view_content = file_get_contents(APP_DIR.OS.$class.OS.'views'.OS.$view_file);

      $base_view_name = baseFileName($view_file);

      if(!($fo = @fopen(APP_DIR.OS.$class.OS.'views'.OS.$base_view_name.'_'.$name.'.php', 'wb'))) {
        exit(error::errorMessage(array('error'=>_("impossibile creare i file delle viste"), 'hint'=>_("controllare i permessi in scrittura")), $link_error));
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
        mkdir($k.OS.$name);
        $this->createMdlFolders($k.OS.$name, $v);
      }
    }

    $module->label = $label;
    $module->name = $name;
    $module->module_app = $module_app_id;
    $module->active = 1;
    $module->description = $description;

    return $module->updateDbData();

  }

  private function createMdlFolders($pdir, $nsdir) {
  
    // if next structure is null break
    if(!$nsdir) return true;
    elseif(is_array($nsdir)) {
      foreach($nsdir as $k=>$v) {
        mkdir($pdir.OS.$k);
        $this->createMdlFolders($pdir.OS.$k, $v);
      }
    }
    else return true;
  }

  /**
   * Modifica di un modulo
   * 
   * @see $_access_2
   */
  private function actionEditModule() {

    $gform = Loader::load('Form', array('gform', 'post', true));
    $gform->save('dataform');

    $id = cleanVar($_POST, 'id', 'int', '');
    $module = new ModuleInstance($id);

    $link_error = $this->_home."?evt[$this->_class_name-manageModule]&id=$id&action=modify";

    $label = cleanVar($_POST, 'label', 'string', '');
    if(!$label) {
      exit(error::errorMessage(array('error'=>1), $link_error));
    }

    $module->label = $label;
    $module->description = cleanVar($_POST, 'description', 'string', '');

    return $module->updateDbData();

  }

  /**
   * Form di attivazione e disattivazione di un modulo
   * 
   * @param ModuleInstance $module istanza di ModuleInstance
   * @return string
   */
  private function formActivateModule($module) {
    
    $gform = loader::load('Form', array('gform', 'post', true));
    $gform->load('dataform');

    $GINO = '';
    if($module->active) {
      $GINO .= "<p>"._('Prima di disattivare un modulo assicurarsi di aver rimosso ogni suo output da tutti i template.')."</p>";
    }

    $required = '';
    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionEditModuleActive]", '', $required);
    $GINO .= $gform->hidden('id', $module->id);
    $GINO .= $gform->cinput('submit_action', 'submit', $module->active ? _("disattiva") : _('attiva'), _('Sicuro di voler procedere?'), array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => $module->active ? _('Disattivazione') : _('Attivazione'),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }

  /**
   * Attivazione e disattivazione di un modulo
   */
  public function actionEditModuleActive() {

    $this->require_pem('can_admin');

    $id = cleanVar($_POST, 'id', 'int', '');

    $module = new ModuleInstance($id);

    $module->active = $module->active ? 0 : 1;
    $module->updateDbData();

    Link::HttpCall($this->_home, $this->_class_name.'-manageModule', '');
  }
}
?>
