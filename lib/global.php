<?php
/**
 * @file global.php
 * @brief Include automaticamente (se richiesti) i file dei moduli presenti nella directory @a app
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace {

	if(!extension_loaded('gettext'))
	{
		function _($str){
			return $str;
		}
	}
	else
	{
		$domain='messages';
		bindtextdomain($domain, "./languages");
		bind_textdomain_codeset($domain, 'UTF-8');
		textdomain($domain);
	}
	
	/**
	 * Recupera il nome della classe senza namespace
	 * 
	 * @param object|string $class oggetto o nome completo della classe
	 * @return string
	 */
	function get_name_class($class) {
		
		if(!$class) return null;
		
		if(is_object($class)) $class = get_class($class);
		
		if(substr($class, -1) == "\\")
		{
			$class = substr_replace($class, '', -1, 1);
		}
		
		$a_class = explode('\\', $class);
		$last_item = count($a_class)-1;
		$class = $a_class[$last_item];
		
		return $class;
	}

	/**
	 * Imposta il nome della classe col suo namespace
	 * 
	 * @param string $class nome della classe
	 * @param string $namespace nome del namespace
	 * @return string
	 */
	function set_name_class($class, $namespace='\Gino\\') {
		
		if(!$namespace) return $class;
		
		if(substr($namespace, -1) != "\\")
		{
			$namespace = $namespace."\\";
    	}
    	
    	return $namespace.$class;
	}

	/**
	 * Ritorna il nome del namespace di una classe di tipo application 
	 * 
	 * @param string $controller_name nome della classe controller
	 * @return string
	 */
	function get_app_namespace($controller_name) {
		
		$ns = '\Gino\App\\'.ucfirst($controller_name);
		return $ns;
	}

	/**
	 * Ritorna il nome della classe di tipo application con namespace completo
	 * 
	 * @param string $controller_name nome della classe controller
	 * @return string
	 */
	function get_app_name_class_ns($controller_name) {
		
		return get_app_namespace($controller_name).'\\'.$controller_name;
	}

    /**
	 * Ritorna il nome della classe di tipo application con namespace completo
	 * 
	 * @param string $controller_name nome della classe controller
	 * @return string
	 */
	function get_model_app_name_class_ns($controller_name, $model_name) {
		return get_app_namespace($controller_name).'\\'.$model_name;
	}
}
?>
