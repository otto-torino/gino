<?php
/**
 * @file loader.class.php
 * @brief Contiene la definizione e l'implementazione della classe loader ed il metodo magic __autoload.
 *
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * Le classi controller sono caricate in maniera automatica
 */
function __autoload($class) {
	
	if(preg_match("#\\#", $class))
	{
		$class_name = get_name_class($class);
	}
	else $class_name = $class;
	
	if(is_dir(APP_DIR.OS.$class_name)) {
		$dir = APP_DIR.OS.$class_name.OS;
		require_once($dir.CONTROLLER_CLASS_PREFIX.$class_name.'.php');
		if (!class_exists($class, false)) {
			trigger_error(sprintf(_("Unable to load the controller: %s"), $class), E_USER_WARNING);
		}
	}
}

/**
 * @brief Loader di classi di tipo model e classi di sistema
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Loader {

	/**
	 * @brief Importa le classi core o i modelli specificati
	 * @description Le classi core sono quelle che risiedono nella directory lib/classes,
	 *              le classi model sono proprie delle app
	 * @param string $app nome dell'app dei models oppure stringa 'class[/subdir]' per le classi core
	 * @param array|string $classes la classe o le classi da importare (comprensive di eventuale namespace)
	 */
	public static function import($app, $classes) {

		if(!is_array($classes)) {
			$classes = array($classes);
		}
		
		// classi core
		if(preg_match("#^class/?(.*)#", $app, $matches)) {
			$dir = CLASSES_DIR.OS;
			if(isset($matches[1])) {
				$subdir = preg_replace('#/#', OS, $matches[1]);
				$dir = $dir . $subdir.OS;
			}
			
			foreach($classes as $class) {

				$class_name = get_name_class($class);
				require_once($dir.CORE_CLASS_PREFIX.$class_name.'.php');
				
				if(!class_exists($class, false)) {
					trigger_error(sprintf(_("Unable to load the core class: %s"), $class), E_USER_WARNING);
				}
			}
		}
		// models
		else {
			$dir = APP_DIR.OS.$app.OS;
			foreach($classes as $class) {
				
				$class_name = get_name_class($class);
				
				if(file_exists($dir.MODEL_CLASS_PREFIX.$class_name.'.php'))
					$file = $dir.MODEL_CLASS_PREFIX.$class_name.'.php';
				elseif(file_exists($dir.CONTROLLER_CLASS_PREFIX.$class_name.'.php'))
					$file = $dir.CONTROLLER_CLASS_PREFIX.$class_name.'.php';
				
				require_once($file);
				//require_once($dir.MODEL_CLASS_PREFIX.$class_name.'.php');
				
				if(!class_exists($class, false)) {
					trigger_error(sprintf(_("Unable to load the model class: %s"), $class), E_USER_WARNING);
				}
			}
		}
	}

	/**
	 * @brief Restituisce un'istanza della classe
	 * 
	 * @param string $class nome della classe core da istanziare (senza namespace)
	 * @param array $args argomenti del costruttore
	 * @param string $namespace nome del namespace (default Gino)
	 * @return object
	 */
	public static function load($class, $args = array(), $namespace='\Gino\\') {
		
		$dir = CLASSES_DIR.OS;
		if(preg_match("#.*?/.*#", $class, $matches)) {
			
			$subdir = preg_replace("#/#", OS, substr($matches[0], 0, strrpos($matches[0], '/')));
			$dir = $dir.$subdir.OS;
			$class = substr($class, strrpos($matches[0], '/') + 1);
		}
		require_once($dir.CORE_CLASS_PREFIX.$class.'.php');
    
		$class = set_name_class($class, $namespace);
		if(!$class) return null;
		
		if(count($args) == 0) {
			$obj = new $class;
		}
		else {
			$r = new \ReflectionClass($class);
			$obj = $r->newInstanceArgs($args);
		}

		return $obj;
	}

	/**
	 * Genera l'istanza Singleton di una classe
	 * 
	 * @param string $class nome della classe comprensivo di eventuale namespace
	 * @return object
	 */
	public static function singleton($class) {
		
		$class_name = get_name_class($class);
		
		$dir = CLASSES_DIR.OS;
		require_once($dir.CORE_CLASS_PREFIX.$class_name.'.php');
		return $class::instance();
	}
}
