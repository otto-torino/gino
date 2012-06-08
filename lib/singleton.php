<?php
/**
 * @file singleton.php
 * @brief Contiene la classe singleton
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

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