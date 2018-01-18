<?php
/**
 * @file class.Loader.php
 * @brief Contiene la definizione e l'implementazione della classe Gino.Loader ed il metodo magic __autoload.
 *
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace {
    /**
     * @function dft_autoload
     * @brief Carica in maniera automatica le classi di tipo Gino.Controller
     * @param string $class nome classe con o senza namespace
     * @return void
     */
    function dft_autoload($class) {

    	// Model
    	if(preg_match("#\\\#", $class))
    	{
    		$cut = strlen('\Gino\App\\');
    		$len = strlen($class)-$cut+1;
    		$ns = substr($class, -$len);
    		
    		$ns = explode("\\", $ns);
    		$path_to_model = APP_DIR.'/'.strtolower($ns[0]).'/'.'class.'.ucfirst($ns[1]).'.php';
    		if(is_file($path_to_model)) {
    			require_once($path_to_model);
    			return null;
    		}
    	}
    	
    	if(preg_match("#\\\#", $class))
        {
            $class_name = get_name_class($class);
        }
        else $class_name = $class;

        if(is_dir(APP_DIR.OS.$class_name)) {
            $dir = APP_DIR.OS.$class_name.OS;
            $path_to_controller = $dir.CONTROLLER_CLASS_PREFIX.$class_name.'.php';
            if(is_file($path_to_controller)) {
            	require_once($path_to_controller);
            }
            if (!class_exists(get_app_name_class_ns($class_name), false)) {
                trigger_error(sprintf(_("Unable to load the controller: %s"), $class), E_USER_WARNING);
            }
        }
    }

	spl_autoload_register('dft_autoload');
}

namespace Gino {

    /**
     * @brief Loader di classi di tipo Gino.Model e classi di sistema
     *
     * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
     * @author marco guidotti guidottim@gmail.com
     * @author abidibo abidibo@gmail.com
     */
    class Loader {

        /**
         * @brief Importa le classi di sistema o i modelli specificati
         * @description Le classi di sistema sono quelle che risiedono nella directory lib/classes,
         *              le classi model sono proprie delle app.
         *              Per importare classi di sistema, l'argomento $app deve avere la forma [class | class/subdir] dove subdir è 
         *              la subdir di lib/classes in cui si trova la classe da importare,
         *              mentre le classi da importare, argomento $classes, devono essere complete di namespace.
         *              Per importare classi di tipo Gino.Model invece l'argomento $app deve essere il nome del Controller 
         *              (ed anche della directory della app), mentre le classi nell'argomento 
         *              $classes devono essere specificate senza namespace.
         * @param string $app nome dell'app dei models oppure stringa 'class[/subdir]' per le classi di sistema
         * @param array|string $classes la classe o le classi da importare (comprensive di eventuale namespace)
         * @return void
         * 
         * @example importare il file di una classe fondamentale per il funzionamento del sistema
         * \Gino\Loader::import('class', '\Gino\AdminTable');
         * @example importare un file presente in una sottodirectory di app
         * \Gino\Loader::import('sysClass', 'ModuleApp');
         */
        public static function import($app, $classes) {

            if(!is_array($classes)) {
                $classes = array($classes);
            }

            // classi core
            if(preg_match("#^class/?(.*)#", $app, $matches)) {
                $dir = CLASSES_DIR.OS;
                if(isset($matches[1]) and $matches[1]) {
                    $subdir = preg_replace('#/#', OS, $matches[1]);
                    $dir = $dir . $subdir . OS;
                }

                foreach($classes as $class) {

                    $class_name = get_name_class($class);
                    require_once $dir.CORE_CLASS_PREFIX.$class_name.'.php';

                    if(!class_exists($class, FALSE)) {
                        throw new \Exception(_("Unable to load the system class: %s"), $class);
                    }
                }
            }
            // models
            else {
                $dir = APP_DIR.OS.$app.OS;
                foreach($classes as $class_name) {
                    require_once $dir.MODEL_CLASS_PREFIX.$class_name.'.php';
                    if(!class_exists(get_model_app_name_class_ns($app, $class_name), FALSE)) {
                        throw new \Exception(_("Unable to load the model class: %s"), $class_name);
                    }
                }
            }

        }

        /**
         * @brief Restituisce un'istanza della classe di sistema richiesta
         *
         * @param string $class nome della classe di sistema da istanziare (senza namespace)
         * @param array $args argomenti del costruttore
         * @param string $namespace nome del namespace (default Gino)
         * @return object
         * 
         * @example Loader::load('http/ResponseJson', array([...]), '\Gino\Http\\');
         * @example \Gino\Loader::load('Options', array($this));
         * @example Loader::load('Form', array());
         */
        public static function load($class, $args = array(), $namespace='\Gino\\') {

            $dir = CLASSES_DIR.OS;
            if(preg_match("#.*?/.*#", $class, $matches)) {

                $subdir = preg_replace("#/#", OS, substr($matches[0], 0, strrpos($matches[0], '/')));
                $dir = $dir.$subdir.OS;
                $class = substr($class, strrpos($matches[0], '/') + 1);
            }
            require_once $dir.CORE_CLASS_PREFIX.$class.'.php';

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
         * @brief Restituisce l'istanza Singleton di una classe
         * 
         * @param string $class nome della classe comprensivo di eventuale namespace
         * @return object
         */
        public static function singleton($class) {

            $class_name = get_name_class($class);

            $dir = CLASSES_DIR.OS;
            require_once $dir.CORE_CLASS_PREFIX.$class_name.'.php';
            return $class::instance();
        }
    }
}
