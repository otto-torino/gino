<?php
/**
 * @file cache.php
 * @brief Contiene le classi cache, outputCache, dataCache
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria che fornisce gli strumenti alle classi outputCache() e dataCache()
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class cache {

	protected $_ds, $_fld, $_prefix;
	protected $_grp, $id, $_tc;
	protected $_enabled;

	function __construct() {

		$this->_ds = OS;
		$this->_fld = CACHE_DIR;
		$this->_prefix = 'cache_';
		$this->_enabled = pub::variable('enable_cache');
	}

	protected function write($data) {

		$filename = $this->getFilename();

		if($fp = @fopen($filename, "xb")) {
			if(flock($fp, LOCK_EX)) fwrite($fp, $data);
			fclose($fp);
			touch($filename, time());
		}
	}

	protected function read() {
		
		return file_get_contents($this->getFilename());
	}

	protected function getFilename() {

		return $this->_fld . $this->_ds . $this->_prefix . $this->_grp ."_". md5($this->_id);
	}

	protected function isCached() {

		$filename = $this->getFilename();
		if($this->_enabled && file_exists($filename) && time() < (filemtime($filename) + $this->_tc)) return true; 
		else @unlink($filename);
			
		return false;
	}
}

/**
 * @brief Memorizza gli output (text, html, xml) scrivendo su file
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 *
 * Esempio di utilizzo
 * @code
 * $GINO = "previous text-";
 * $cache = new outputCache($GINO);
 * if($cache->start("group_name", "id", 3600)) {
 *	
 *	$buffer = "some content-";
 *
 *	$cache->stop($buffer);
 *
 * }
 * $GINO .= "next text";
 * @endcode
 *
 * --> result: $GINO = "previous text-somec content-next text";
 *
 * if the content is cached the if statement is skipped and the content is concatenated to $GINO
 * if content is not cached the if statemet runs, the content is prepared and then saved in cache and added to $GINO (through stop method)
 */
class outputCache extends cache {

	function __construct(&$buffer, $enable = true) {

		parent::__construct();
		$this->_buffer = &$buffer;
		$this->_enabled = $enable;
	}

	public function start($grp, $id, $tc) {
	
		$this->_grp = $grp;
		$this->_id = $id;
		$this->_tc = $tc;

		if($this->isCached()) {
			$this->_buffer .= $this->read();
			return false;
		}
		
		return true;
	}

	public function stop($data) {
		
		if($this->_enabled) $this->write($data);
		$this->_buffer .= $data;
	}
}

/**
 * @brief Memorizza le strutture dati scrivendo su file
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 *
 * Esempio di utilizzo
 * @code
 * $cache = new dataCache();
 * if(!$data = $cache->get('group_name', 'id', 3600)) {
 *   $data = someCalculations();
 *   $cache->save($data);
 * }
 * @endcode
 * 
 * if data is stored it's returned by get method and if statement is not processed, otherwise data is calculated and saved in cache
 */
class dataCache extends cache {

	function __construct($enable = true) {

		parent::__construct();
		
		$this->_enabled = $enable;
	}

	public function get($grp, $id, $tc) {
	
		$this->_grp = $grp;
		$this->_id = $id;
		$this->_tc = $tc;

		if($this->isCached()) return unserialize($this->read());
		return false;
	}

	public function save($data) {
		
		if($this->_enabled) $this->write(serialize($data));
	}
}
?>
