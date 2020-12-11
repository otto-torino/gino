<?php
/**
 * @file plugin.phpfastcache.php
 * @brief Contiene la classe plugin_phpfastcache
 */

/**
 * @namespace Gino.Plugin
 * @description Namespace che comprende classi di tipo plugin
 */
namespace Gino\Plugin;

require_once LIBRARIES_DIR.OS."phpfastcache".OS."phpfastcache.php";

/**
 * @brief Interfaccia alla libreria phpfastcache
 * 
 * @see http://www.phpfastcache.com/
 */
class plugin_phpfastcache {

	/**
	 * Object phpFastCache
	 * 
	 * @var object
	 */
	private $_obj_cache;
	
	/**
	 * Content to be saved in cache
	 * 
	 * @var string
	 */
	private $_content;
	
	/**
	 * Identification code of a set of data in the cache
	 * 
	 * @var string
	 */
	private $_identity_keyword=null;
	
	/**
	 * Duration time of the cache
	 * 
	 * @var integer (seconds, 0 = never expired)
	 */
	private $_cache_time;
	
	/**
	 * Constructor
	 * 
	 * @param array $options
	 *   array associativo di opzioni che sovrascrivono i valori delle costanti definite nel file configuration.php
	 *   - @b cache_time (integer): tempo di durata della cache (default 30')
	 *   - @b cache_path (string): directory di salvataggio della cache
	 *   - @b cache_type (string): tipologia di cache (@see cacheType())
	 *   - @b cache_server (array): parametri di connessione necessari per alcune tipolgia di cache
	 *   - @b cache_fallback (string): tipologia di fallback della cache
	 * @return void
	 */
	function __construct($options=array()) {
		
		$time = \Gino\gOpt('cache_time', $options, null);
		$path = \Gino\gOpt('cache_path', $options, null);
		$type = \Gino\gOpt('cache_type', $options, null);
		$server = \Gino\gOpt('cache_server', $options, null);
		$fallback = \Gino\gOpt('cache_fallback', $options, null);
		
		$this->_cache_time = is_int($time) ? $time : 1800;
		
		if($type && in_array($type, self::cacheType())) {
			\phpFastCache::setup('storage', $type);
		}
		if($server) {
			\phpFastCache::setup('server', $server);
		}
		if($fallback) {
			\phpFastCache::setup('fallback', $fallback);
		}
		if($path) {
			\phpFastCache::setup('path', $path);
		}
		
		$this->_obj_cache = \phpFastCache();
	}
	
	/**
	 * Tipologie di cache supportate
	 * @return array
	 */
	private static function cacheType() {
		
		return array("auto","files","redis","cookie","sqlite","wincache","apc","memcache","memcached","xcache");
	}
	
	/**
	 * @brief Getter della proprietà identity_keyword
	 * @return string
	 */
	public function getIdentityKeyword() {
	
		return $this->_identity_keyword;
	}
	
	/**
	 * @brief Setter della proprietà identity_keyword
	 * @param string $value
	 * @return void
	 */
	public function setIdentityKeyword($value) {
	
		if(is_string($value) && $value) $this->_identity_keyword = md5($value);
	}
	
	/**
	 * Try to get from cache
	 * 
	 * @param string $identity_keyword
	 * @return mixed
	 */
	public function get($identity_keyword=null) {
		
		if($identity_keyword) $this->_identity_keyword = md5($identity_keyword);
		
		return $this->_obj_cache->get($this->_identity_keyword);
	}
	
	/**
	 * Set to cache
	 * 
	 * @param mixed $results
	 * @param array $options
	 */
	public function set($results, $options=array()) {
		
		$time_caching = \Gino\gOpt('time_caching', $options, $this->_cache_time);
		$identity_keyword = \Gino\gOpt('identity_keyword', $options, $this->_identity_keyword);
		
		$this->_obj_cache->set($identity_keyword, $results, $time_caching);
	}
	
	/**
	 * Delete from cache
	 */
	public function delete($identity_keyword) {
		
		if($identity_keyword)
			$this->_obj_cache->delete($identity_keyword);
	}
	
	/**
	 * Clean all from cache
	 */
	public function clean() {
	
		$this->_obj_cache->clean();
	}
	
	public function getInfo() {
		
		if($this->_identity_keyword)
		{
			$object = $this->_obj_cache->getInfo($this->_identity_keyword);
			print_r($object);
		}
		// Stats
		$array = $this->_obj_cache->stats();
		print_r($array);
	}
}
?>
