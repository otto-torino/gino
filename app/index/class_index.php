<?php
/**
 * @file class_index.php
 * @brief Contiene la classe index
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Index;

use \Gino\HttpResponse;

/**
 * @brief 
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class index extends \Gino\Controller{

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
   * Home page amministrazione
   * @param \Gino\HttpRequest $request
   * 
   * @return string
   */
  public function admin_page(\Gino\Http\Request $request){

    if(!$request->user->hasPerm('core', 'is_staff')) {
      $this->_session->auth_redirect = "$this->_home?evt[".$this->_class_name."-admin_page]";
      $this->_registry->plink->redirect('auth', 'login');
    }

    $buffer = '';
    $sysMdls = $this->sysModulesManageArray($request);
    $mdls = $this->modulesManageArray($request);
    if(count($sysMdls)) {
      $GINO = "<table class=\"table table-striped table-hover table-bordered\">";
      foreach($sysMdls as $sm) {
        $GINO .= "<tr>";
        $GINO .= "<th><a href=\"$this->_home?evt[".$sm['name']."-manage".ucfirst($sm['name'])."]\">".\Gino\htmlChars($sm['label'])."</a></th>";
        $GINO .= "<td class=\"mdlDescription\">".\Gino\htmlChars($sm['description'])."</td>";
        $GINO .= "</tr>";
      }
      $GINO .= "</table>\n";

      $view = new \Gino\View();
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
        $GINO .= "<th><a href=\"$this->_home?evt[".$m['name']."-manageDoc]\">".\Gino\htmlChars($m['label'])."</a></th>";
        $GINO .= "<td>".\Gino\htmlChars($m['description'])."</td>";
        $GINO .= "</tr>";
      }
      $GINO .= "</table>\n";

      $view = new \Gino\View();
      $view->setViewTpl('section');
      $view->assign('class', 'admin');
      $view->assign('title', _("Amministrazione moduli istanziabili"));
      $view->assign('content', $GINO);

      $buffer .= $view->render();

    }
    return new \Gino\Http\Response($buffer);
  }

  /**
   * Elenco dei moduli di sistema visualizzabili nell'area amministrativa
   * 
   * @return array
   */
  public function sysModulesManageArray($request) {

    \Gino\Loader::import('sysClass', 'ModuleApp');

    if(!$request->user->hasPerm('core', 'is_staff')) {
      return array();
    }

    $list = array();
    $modules_app = \Gino\App\SysClass\ModuleApp::objects(null, array('where' => "active='1' AND instantiable='0'"));
    if(count($modules_app)) {
      foreach($modules_app as $module_app) {
        if($request->user->hasAdminPerm($module_app->name) and method_exists($module_app->classNameNs(), 'manage'.ucfirst($module_app->name))) {
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
  public function modulesManageArray($request) {

    \Gino\Loader::import('module', 'ModuleInstance');

    if(!$request->user->hasPerm('core', 'is_staff')) {
      return array();
    }

    $list = array();
    $modules = \Gino\App\Module\ModuleInstance::objects(null, array('where' => "active='1'"));
    if(count($modules)) {
      foreach($modules as $module) {
        if($request->user->hasAdminPerm($module->className(), $module->id) and method_exists($module->classNameNs(), 'manageDoc')) {
          $list[$module->id] = array("label"=>$module->ml('label'), "name"=>$module->name, "class"=>$module->className(), "description"=>$module->ml('description'), $module->id);
        }
      }
    }

    return $list;
  }
}
?>
