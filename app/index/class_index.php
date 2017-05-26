<?php
/**
 * @file class_index.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Index.index
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Index
 * @description Namespace dell'applicazione Index, che gestisce la home amministrativa
 */
namespace Gino\App\Index;

use \Gino\Http\Response;
use \Gino\Document;
use \Gino\View;
use \Gino\Loader;

/**
 * @brief Classe di tipo Gino.Controller del modulo che gestisce la home amministrativa
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class index extends \Gino\Controller{

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Index.index
     */
    function __construct(){
        parent::__construct();
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
            "admin_page" => array("label" => _("Home page amministrazione"), "permissions"=>array('core.is_staff')),
        	'sidenav' => array("label" => _("Menu amministrativo laterale"), "permissions"=>array('core.is_staff')),
        );

        return $list;
    }
    
    /**
     * @brief Barra laterale con l'elenco delle applicazioni
     * @return NULL|string
     */
    public function sidenav() {
    	
    	$request = \Gino\Http\Request::instance();
    	
    	if(!$request->user->hasPerm('core', 'is_staff')) {
    		return null;
    	}
    	
    	$this->_registry->addCss($this->_class_www."/index.css");
    	
    	$sysMdls = $this->sysModulesManageArray($request);
    	$mdls = $this->modulesManageArray($request);
    	
    	$view = new view($this->_view_dir, 'sidenav');
    	$dict = array(
    		'sysmdls' => $sysMdls,
    		'mdls' => $mdls,
    		'ctrl' => $this,
    		'fas' => unserialize(INSTALLED_APPS),
    		'hide' => unserialize(HIDDEN_APPS),
    		'openclose' => OPEN_CLOSE_SIDENAV
    	);
    	
    	return $view->render($dict);
    }

    /**
     * @brief Home page amministrazione
     * @param \Gino\HttpRequest $request istanza di Gino.Http.Request
     * @return Gino.Http.Response home page amministrazione
     */
    public function admin_page(\Gino\Http\Request $request){

        if(!$request->user->hasPerm('core', 'is_staff')) {
            $request->session->auth_redirect = $this->link($this->_class_name, 'admin_page');
            return new \Gino\Http\Redirect($this->link('auth', 'login'));
        }
        
        $this->_registry->addCss($this->_class_www."/index.css");

        $sysMdls = $this->sysModulesManageArray($request);
        $mdls = $this->modulesManageArray($request);

        $view = new view($this->_view_dir, 'admin_page');
        $dict = array(
            'sysmdls' => $sysMdls,
            'mdls' => $mdls,
            'ctrl' => $this,
            'fas' => unserialize(INSTALLED_APPS),
            'hide' => unserialize(HIDDEN_APPS),
        	'view_hidden_apps' => VIEW_HIDDEN_APPS,
        );

        $document = new \Gino\Document($view->render($dict));
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
