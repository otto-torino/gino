<?php
/**
 * @file singleton.php
 * @brief Contiene la classe singleton
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Singleton Design Pattern
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
abstract class singleton {

	protected static $_instances = array();

	protected function __construct() {

	}

	/**
	 * Metodo principale singleton
	 * 
	 * Ritorna sempre la stessa istanza
	 * 
	 * @return object
	 */
	public static function instance() {

		$class = get_called_class();
		if(array_key_exists($class, self::$_instances) === false) {
			self::$_instances[$class] = new static();
		}
		
		return self::$_instances[$class];
	}
	
	/**
	 * Metodo principale singleton per le istanze definite in riferimento a una classe passata come proprietà
	 * 
	 * Ritorna sempre la stessa istanza
	 * 
	 * @param string $main_class nome della classe che definisce l'istanza (ovvero la classe che istanzia la classe che richiama il metodo)
	 * @return object
	 */
	public static function instance_to_class($main_class) {

		$class = get_called_class().'_'.$main_class;
		if(array_key_exists($class, self::$_instances) === false) {
			self::$_instances[$class] = new static($main_class);
		}
		
		return self::$_instances[$class];
	}
	
	public function __clone() {
		Error::syserrorMessage('singleton', '__clone', __("CannotCloneSingleton"), __LINE__);
	}

	public function __sleep() {
		Error::syserrorMessage('singleton', '__sleep', __("CannotSerializeSingleton").get_called_class(), __LINE__);
	}

	public function __wakeup() {
		Error::syserrorMessage('singleton', '__wakeup', __("CannotSerializeSingleton"), __LINE__);
	}
}

?>