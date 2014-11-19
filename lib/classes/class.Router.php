<?php
/**
 * @file class.Router.php
 * @brief Contiene la definizione ed implementazione della class \Gino\Router
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

use \Gino\Loader;
use \Gino\Singleton;
use \Gino\Registry;
use \Gino\Exception404;
use \Gino\App\SysClass\ModuleApp;
use \Gino\App\Module\ModuleInstance;

/**
 * @brief Gestisce il routing di una request HTTP, chiamando la classe e metodo che devono fornire risposta
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Router extends Singleton {

    private $_registry,
            $_url_class,
            $_url_instance,
            $_url_method,
            $_controller_view; // callable

    /**
     * @brief Costruttore
     * @description Esegue l'url rewriting quando si utilizzano permalinks e setta le variabili che 
     *              contengono le informazioni della classe e metodo chiamati da url
     */
    protected function __construct() {

        $this->_registry = Registry::instance();
        $this->urlRewrite();
        $this->setUrlParams();
    }

    /**
     * @brief Url rewriting
     * @description Se l'url non è nella forma permalink ritorna FALSE, altrimenti riscrive le proprietà dell'oggetto
     *              @ref \Gino\HttpRequest parserizzando l'url
     */
    private function urlRewrite() {

        if(preg_match("#^.*?".EVT_NAME."\[(.+)-(.+)\](.*)$#is", $this->_registry->request->path)) {
            return FALSE;
        }
        // rewrite
        else {
            return TRUE;
        }
    }

    /**
     * @brief Setta le proprietà che contengono le informazioni della classe e metodo chiamati da url
     * @description Se i parametri ricavati dall'url tentano di chiamare una callable (classe + metodo) non chiamabile
     *              per qualunque motivo, viene generata una \Gino\Exception404
     * @return TRUE
     */
    private function setUrlParams() {

        $evt_key = (isset($this->_registry->request->GET[EVT_NAME]) and is_array($this->_registry->request->GET[EVT_NAME]))
            ? key($this->_registry->request->GET[EVT_NAME])
            : false;

        if($evt_key === FALSE or preg_match('#^[^a-zA-Z0-9_-]+?#', $evt_key)) {
            $this->_url_class = null;
            $this->_url_method = null;
            $this->_controller_view = null;
        }
        else {
            list($mdl, $method) = explode("-", $evt_key);

            Loader::import('module', 'ModuleInstance');
            $module_app = ModuleApp::getFromName($mdl);
            $module = ModuleInstance::getFromName($mdl);

            // se da url non viene chiamato un modulo né un'istanza restituiamo un 404
            if(is_null($module_app) and is_null($module)) {
                throw new Exception404();
            }

            if(is_dir(APP_DIR.OS.$mdl) and class_exists(get_app_name_class_ns($mdl)) and $module_app and !$module_app->instantiable) {
                $class = $module_app->classNameNs();
                $class_name = $module_app->className();
                $module_instance = new $class();
            }
            elseif(class_exists($module->classNameNs())) {
                $mdl_id = $module->id;
                $class = $module->classNameNs();
                $class_name = $module->className();
                $module_instance = new $class($mdl_id);
            }
            else {
                throw new Exception404();
            }

            $method_check = parse_ini_file(APP_DIR.OS.$class_name.OS.$class_name.".ini", true);
            $public_method = @$method_check['PUBLIC_METHODS'][$method];

            if(isset($public_method)) {
                $this->_url_class = $class_name;
                $this->_url_instance = $mdl;
                $this->_url_method = $method;
                $this->_controller_view = array($module_instance, $this->_url_method);
            }
            else {
                throw new Exception404();
            }
        }
    }

    /**
     * @brief Esegue il route della request HTTP
     * @description Passa la \Gino\HttpRequest alla callable che deve gestirla e ritornare una \Gino\HttpResponse
     *              Se non è definita una callable, ritorna una \Gino\HttpResponse con contenuto vuoto
     * @return \Gino\HttpResponse
     */
    public function route() {
        if(!is_null($this->_controller_view)) {
            return call_user_func($this->_controller_view, $this->_registry->request);
        }
        else {
            return new HttpResponse('');
        }
    }

}
