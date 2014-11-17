<?php
/**
 * @file methodPointer.php
 * @brief Prende in carico le richieste ajax, controllando se esistono la classe e il metodo richiesti
 * 
 * Una richiesta ajax è valida se
 *   - la request ha come nome @a pt
 *   - il metodo è definito nel file @a .ini
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\App\SysClass\ModuleApp;
use \Gino\App\Module\ModuleInstance;

if(isset($_REQUEST['pt'])) {

	Loader::import('sysClass', 'ModuleApp');
	Loader::import('module', 'ModuleInstance');
	
	$db = db::instance();

	$mypointer_array= $_REQUEST['pt'];
	$mypointer = key($mypointer_array);
	if(preg_match('#^[^a-zA-Z0-9_-]+?#', $mypointer)) return null;
	
	list($mdl, $function) = explode("-", key($_REQUEST['pt']));
	$module_app = ModuleApp::getFromName($mdl);
	if($module_app && !$module_app->instantiable) {
        $class_name = $mdl;
        $class = get_app_name_class_ns($mdl);
		$instance = new $class();
	}
	elseif($module = ModuleInstance::getFromName($mdl)) {
		$class_name = $module->className();
        $module_app = new ModuleApp($module->module_app);
        $class = $module_app->classNameNs();
		$instance = new $class($module->id);
	}
	else {
		exit(error::syserrorMessage("methodPointer", "none", "Modulo sconosciuto", __LINE__));
	}

	$methodCheck = parse_ini_file(APP_DIR.OS.$class_name.OS.$class_name.".ini", true);
	$publicMethod = @$methodCheck['PUBLIC_METHODS'][$function];

	if(isset($publicMethod)) {echo $instance->$function();exit();}

	exit("request error:".sprintf(_("Errore! Il metodo %s non esiste"), $function));
}
?>
