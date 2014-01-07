<?php
/**
 * @file loader.class.php
 * @brief Contiene la definizione e l'implementazione della classe loader ed il metodo magic __autoload.
 *
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * Le classi controller sono caricate in maniera automatica
 */
function __autoload($class) {
  if(is_dir(APP_DIR.OS.$class)) {
    $dir = APP_DIR.OS.$class.OS;
    require_once($dir.CONTROLLER_CLASS_PREFIX.$class.'.php');
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
class loader {

  /**
   * @brief Importa le classi core o i modelli specificati
   * @description Le classi core sono quelle che risiedono nella directory lib/classes,
   *              le classi model sono proprie delle app
   * @param string $app nome dell'app dei models oppure stringa 'class[/subdir]' per le classi core
   * @param array|string $classes la classe o le classi da importare
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
        require_once($dir.CORE_CLASS_PREFIX.$class.'.php');
        if(!class_exists($class, false)) {
          trigger_error(sprintf(_("Unable to load the core class: %s"), $class), E_USER_WARNING);
        }
      }
    }
    // models
    else {
      $dir = APP_DIR.OS.$app.OS;
      foreach($classes as $class) {
        require_once($dir.MODEL_CLASS_PREFIX.$class.'.php');
        if(!class_exists($class, false)) {
          trigger_error(sprintf(_("Unable to load the model class: %s"), $class), E_USER_WARNING);
        }
      }
    }
  }

  /**
   * @brief Restituisce un'istanza della classe
   * @param string $class la classe core da istanziare
   * @param array $args argomenti del costruttore
   */
  public static function load($class, $args = array()) {
    $dir = CLASSES_DIR.OS;
    if(preg_match("#.*?/.*#", $class, $matches)) {
      $subdir = preg_replace("#/#", OS, substr($matches[0], 0, strrpos($matches[0], '/')));
      $dir = $dir.$subdir.OS;
      $class = substr($class, strrpos($matches[0], '/') + 1);
    }
    require_once($dir.CORE_CLASS_PREFIX.$class.'.php');
    if(count($args) == 0) {
      $obj = new $class;
    }
    else {
      $r = new ReflectionClass($class);
      $obj = $r->newInstanceArgs($args);
    }

    return $obj;

  }

  public static function singleton($class) {

    $dir = CLASSES_DIR.OS;
    require_once($dir.CORE_CLASS_PREFIX.$class.'.php');
    return $class::instance();

  }
}
