<?php
/**
 * @file class_index.php
 * @brief Contiene la definizione ed implementazione della classe index
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namaspace Gino\App\Index
 * @brief Namespace dell'app di sistema Index
 */
namespace Gino\App\Index;

use \Gino\Http\Response;
use \Gino\Document;
use \Gino\View;
use \Gino\Loader;


/**
 * @defgroup gino-index
 * @brief Modulo per la gestione della home amministrativa
 */

/**
 * @brief Classe Controller del modulo che gestisce la home amministrativa
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class index extends \Gino\Controller{

    /**
     * @brief Costruttore
     * @return \Gino\App\Index\index
     */
    function __construct(){
        parent::__construct();
    }

    /**
     * @brief Elenco dei metodi che possono essere richiamati dal menu e dal template
     * @return array di metodi pubblici
     */
    public static function outputFunctions() {

        $list = array(
            "admin_page" => array("label"=>_("Home page amministrazione"), "permissions"=>array('core.is_staff'))
        );

        return $list;
    }

    /**
     * @brief Home page amministrazione
     * @param \Gino\HttpRequest $request
     * @return home page amministrazione
     */
    public function admin_page(\Gino\Http\Request $request){

        if(!$request->user->hasPerm('core', 'is_staff')) {
            $request->session->auth_redirect = $this->link($this->_class_name, 'admin_page');
            return new \Gino\Http\Redirect($this->link('auth', 'login'));
        }

        $buffer = '';
        $sysMdls = $this->sysModulesManageArray($request);
        $mdls = $this->modulesManageArray($request);
        if(count($sysMdls)) {
            $GINO = "<table class=\"table table-striped table-hover table-bordered\">";
            foreach($sysMdls as $sm) {
                $GINO .= "<tr>";
                $GINO .= "<th><a href=\"".$this->link($sm['name'], 'manage'.ucfirst($sm['name']))."\">".\Gino\htmlChars($sm['label'])."</a></th>";
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
                $GINO .= "<th><a href=\"".$this->link($m['name'], 'manageDoc')."\">".\Gino\htmlChars($m['label'])."</a></th>";
                $GINO .= "<td>".\Gino\htmlChars($m['description'])."</td>";
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

        $document = new Document($buffer);
        return $document();
    }

    /**
     * @brief Elenco dei moduli di sistema visualizzabili nell'area amministrativa
     * @return array associativo contente informazioni sui moduli: <module_id> => array(<label>, <name>, <decription>)
     */
    public function sysModulesManageArray($request) {

        Loader::import('sysClass', 'ModuleApp');

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
     * @brief Elenco dei moduli non di sistema visualizzabili nell'area amministrativa
     * @return array associativo contente informazioni sui moduli: <module_id> => array(<label>, <name>, <class>, <decription>)
     */
    public function modulesManageArray($request) {

        Loader::import('module', 'ModuleInstance');

        if(!$request->user->hasPerm('core', 'is_staff')) {
            return array();
        }

        $list = array();
        $modules = \Gino\App\Module\ModuleInstance::objects(null, array('where' => "active='1'"));
        if(count($modules)) {
            foreach($modules as $module) {
                if($request->user->hasAdminPerm($module->className(), $module->id) and method_exists($module->classNameNs(), 'manageDoc')) {
                    $list[$module->id] = array("label"=>$module->ml('label'), "name"=>$module->name, "class"=>$module->className(), "description"=>$module->ml('description'));
                }
            }
        }

        return $list;
    }
}
