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

if(isset($_REQUEST['pt'])) {
	
	$db = db::instance();

	$mypointer_array= $_REQUEST['pt'];
	$mypointer = key($mypointer_array);
	if(preg_match('#^[^a-zA-Z0-9_-]+?#', $mypointer)) return null;
	
	list($mdl, $function) = explode("-", key($_REQUEST['pt']));
	if(class_exists($mdl) && $db->getFieldFromId(TBL_MODULE_APP, 'instance', 'name', $mdl)!='yes') {$class=$mdl; $instance = new $mdl();}
	elseif(class_exists($db->getFieldFromId('sys_module', 'class', 'name', $mdl))) {
		$class = $db->getFieldFromId('sys_module', 'class', 'name', $mdl);
		$instance = new $class($db->getFieldFromId(TBL_MODULE, 'id', 'name', $mdl));
	}
	else exit(error::syserrorMessage("document", "modUrl", "Modulo sconosciuto", __LINE__));
	
	$methodCheck = parse_ini_file(APP_DIR.OS.$class.OS.$class.".ini", true);
	$publicMethod = @$methodCheck['PUBLIC_METHODS'][$function];

	if(isset($publicMethod)) {echo $instance->$function();exit();}

	exit("request error:"._("Errore! Il metodo richiamato non esiste"));
}
?>
