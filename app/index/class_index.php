<?php
/**
 * @file class_index.php
 * @brief Contiene la classe index
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief 
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class index extends Controller{

  private $_page;
  
  function __construct(){

    parent::__construct();

  }

  /**
   * Elenco dei metodi che possono essere richiamati dal menu e dal template
   * 
   * @return array
   */
  public static function outputFunctions() {

    $list = array(
      "admin_page" => array("label"=>_("Home page amministrazione"), "permissions"=>array('core.is_staff'))
    );

    return $list;
  }

  /**
   * Pagina di autenticazione
   * 
   * @see sysfunc::tableLogin()
   * @return string
   */
  public function auth_page(){

    $registration = cleanVar($_GET, 'reg', 'int', '');
    
    if($registration == 1) $control = true; else $control = false;
    
    $GINO = "<div id=\"section_indexAuth\" class=\"section\">";

    $GINO .= "<p>"._("Per procedere è necessario autenticarsi.")."</p>";
    
    $GINO .= "</div>";
    
    return $GINO;
  }

  /**
   * Home page amministrazione
   * 
   * @return string
   */
  public function admin_page(){

    if(!$this->_registry->user->hasPerm('core', 'is_staff')) {
      $this->_session->auth_redirect = "$this->_home?evt[".$this->_class_name."-admin_page]";
      Link::HttpCall($this->_home, $this->_class_name.'-auth_page', '');
    }

    $buffer = '';
    $sysMdls = $this->sysModulesManageArray();
    $mdls = $this->modulesManageArray();
    if(count($sysMdls)) {
      $GINO = "<table class=\"table table-striped table-hover table-bordered\">";
      foreach($sysMdls as $sm) {
        $GINO .= "<tr>";
        $GINO .= "<th><a href=\"$this->_home?evt[".$sm['name']."-manage".ucfirst($sm['name'])."]\">".htmlChars($sm['label'])."</a></th>";
        $GINO .= "<td class=\"mdlDescription\">".htmlChars($sm['description'])."</td>";
        $GINO .= "</tr>";
      }
      $GINO .= "</table>\n";

      $view = new View();
      $view->setViewTpl('section');
      $view->assign('class', 'admin');
      $view->assign('title', _("Amministrazione sistema"));
      $view->assign('content', $GINO);

      $buffer .= $view->render();
    }
    if(count($mdls)) {
    
      $GINO = "<table class=\"table table-striped table-hover table-bordered\">";
      foreach($mdls as $m) {
        $GINO .= "<tr>";
        $GINO .= "<th><a href=\"$this->_home?evt[".$m['name']."-manageDoc]\">".htmlChars($m['label'])."</a></th>";
        $GINO .= "<td>".htmlChars($m['description'])."</td>";
        $GINO .= "</tr>";
      }
      $GINO .= "</table>\n";

      $view = new View();
      $view->setViewTpl('section');
      $view->assign('class', 'admin');
      $view->assign('title', _("Amministrazione moduli istanziabili"));
      $view->assign('content', $GINO);

      $buffer .= $view->render();

    }
    return $buffer;
  }

  /**
   * Elenco dei moduli di sistema visualizzabili nell'area amministrativa
   * 
   * @return array
   */
  public function sysModulesManageArray() {

    loader::import('sysClass', 'ModuleApp');

    if(!$this->_registry->user->hasPerm('core', 'is_staff')) {
      return array();
    }

    $list = array();
    $modules_app = ModuleApp::get(array('where' => "active='1'"));
    if(count($modules_app)) {
      foreach($modules_app as $module_app) {
        if($this->_registry->user->hasAdminPerm($module_app->name) and method_exists($module_app->name, 'manage'.ucfirst($module_app->name))) {
          $list[$module_app->id] = array("label"=>$module_app->ml('label'), "name"=>$module_app->name, "description"=>$module_app->ml('description'));
        }
      }
    }

    return $list;
  }
  
  /**
   * Elenco dei moduli non di sistema visualizzabili nell'area amministrativa
   * 
   * @return array
   */
  public function modulesManageArray() {

    loader::import('module', 'ModuleInstance');

    if(!$this->_registry->user->hasPerm('core', 'is_staff')) {
      return array();
    }

    $list = array();
    $modules = ModuleInstance::get(array('where' => "active='1'"));
    if(count($modules)) {
      foreach($modules as $module) {
        if($this->_registry->user->hasAdminPerm($module->className(), $module->id) and method_exists($module->className(), 'manageDoc')) {
          $list[$module->id] = array("label"=>$module->ml('label'), "name"=>$module->name, "class"=>$module->className(), "description"=>$module->ml('description'), $module->id);
        }
      }
    }

    return $list;
  }
}
?>
