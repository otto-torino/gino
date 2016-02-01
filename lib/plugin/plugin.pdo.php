<?php
/**
 * @file plugin.pdo.php
 * @brief Contiene la classe pdo
 * 
 * @copyright 2015-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Plugin
 * @description Namespace che comprende classi di tipo plugin
 */
namespace Gino\Plugin;

require_once(PLUGIN_DIR.OS."plugin.phpfastcache.php");

/**
 * @brief Libreria di connessione ai database
 * 
 * @copyright 2015-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * DRIVER
 * ---------------
 * L'interfaccia PDO attualmente è implementata da 12 driver (http://php.net/manual/en/pdo.drivers.php) che supportano diversi tipi di database. \n
 * Ogni driver deve essere gestito da una classe che estende la classe Gino.Plugin.pdo e che definisce alcune specificità di un database. \n
 * Il nome della classe del driver deve essere "pdo_[valore della costante DBMS]" mentre il nome del file "plugin.[nome della classe].php".
 * 
 * CACHE QUERY
 * ---------------
 * La proprietà self::$_query_cache indica se è stata abilita la cache delle query. \n
 * Le query che vengono salvate in cache sono quelle che passano dal metodo select() ed execCustomQuery(), e non riguardano quindi le query di struttura, 
 * quali quelle presenti nei metodi:
 *   - fieldInformations()
 * 
 * Qualora non si desideri caricare in cache una determinata query è sufficiente passare l'opzione @a cache=false ai metodi select() e execCustomQuery(). \n
 * La cache delle query viene svuotata ogni volta che viene eseguita una query di tipo @a action (@see queryResults()).
 * 
 * INFORMAZIONI SULLE QUERY
 * ---------------
 * La proprietà @a self::$_show_stats attiva la raccolta di informazioni sulle prestazioni delle chiamate al database. \n
 * Le query di tipo select alimentano i contatori self::$_cnt e self::$_time_queries.
 */
class pdo implements \Gino\DbManager {

	protected $_dbms, $_db_host, $_db_name, $_db_user, $_db_password, $_db_charset;
	
	/**
	 * Numero di righe risultanti da una select query
	 * 
	 * @var integer
	 */
	protected $_numberrows;
	
	/**
	 * Numero di righe interessate da una istruzione INSERT, UPDATE o DELETE
	 * 
	 * @var integer
	 */
	protected $_affected;
	
	/**
	 * Numero di colonne in una istruzione select
	 * 
	 * @var integer
	 */
	protected $_numbercols;
	
	/**
	 * Stato della connessione al database
	 * 
	 * @var boolean
	 */
	protected $_connection;
	
	/**
	 * PDO object
	 *
	 * @var object
	 */
	protected $_pdo;
	
	/**
	 * PDOStatement object
	 * 
	 * @var object
	 */
	protected $_result;
	
	/**
	 * Attiva le statische sulle query
	 * 
	 * @var boolean
	 */
	protected $_show_stats;
	
	/**
	 * Informazioni sulle query eseguite
	 *
	 * @var array
	 */
	protected $_info_queries;
	
	/**
	 * Contatore di query
	 * 
	 * @var integer
	 */
	protected $_cnt;
	
	/**
	 * Tempo totale di esecuzione delle query
	 * 
	 * @var float
	 */
	protected $_time_queries;
	
	/**
	 * Query cache
	 * 
	 * @var boolean
	 */
	protected $_query_cache;
	
	/**
	 * Tempo di durata della query cache
	 *
	 * @var integer
	 */
	protected $_query_cache_time;
	
	/**
	 * Oggetto plugin_phpfastcache
	 * 
	 * @var object
	 */
	protected $_cache;
	
	/**
	 * Costruttore
	 * 
	 * @param array $params parametri di connessione al database
	 *   - @b dbms (string): nome del database management system
	 *   - @b host (string): nome del server
	 *   - @b db_name (string): nome del database
	 *   - @b user (string): utente che si connette
	 *   - @b password (string): password dell'utente che si connette
	 *   - @b charset (string): encoding
	 *   - @b connect (boolean): attiva la connessione
	 * @return void
	 */
	function __construct($params) {
		
		$this->_dbms = $params["dbms"];
		$this->_db_host = $params["host"];
		$this->_db_name = $params["db_name"];
		$this->_db_user = $params["user"];
		$this->_db_password = $params["password"];
		$this->_db_charset = $params["charset"];
		
		$this->setNumberRows(0);
		$this->setAffectedRows(0);
		$this->setConnection(false);
		
		$this->_show_stats = (DEBUG && SHOW_STATS) ? true : false;
		
		$this->_cnt = 0;
		$this->_info_queries = array();
		$this->_time_queries = 0;
		
		$this->setParamsCache();
		if($this->_query_cache)
		{
			$this->_cache = new \Gino\Plugin\plugin_phpfastcache(
				array(
					'cache_time'=>$this->_query_cache_time, 
					'cache_path'=>QUERY_CACHE_PATH ? QUERY_CACHE_PATH : CACHE_DIR, 
					'cache_type'=>QUERY_CACHE_TYPE, 
					'cache_server'=>QUERY_CACHE_SERVER, 
					'cache_fallback'=>QUERY_CACHE_FALLBACK, 
				)
			);
		}
		
		if($params["connect"]===true) $this->openConnection();
	}
	
	/**
	 * Parametri inerenti la cache delle query presenti nelle Impostazioni di sistema
	 * 
	 * @return void
	 */
	private function setParamsCache() {
		
		$item = $this->select("query_cache, query_cache_time", TBL_SYS_CONF, "id='1'", array('cache'=>false));
		if(count($item))
		{
			$this->_query_cache = $item[0]['query_cache'] ? true : false;
			$this->_query_cache_time = $item[0]['query_cache_time'];
		}
		else
		{
			$this->_query_cache = false;
			$this->_query_cache_time = null;
		}
	}
	
	/**
	 * Ritorna il numero di righe risultanti da una select query
	 * 
	 * @return integer
	 */
	public function getNumberRows() {
		return $this->_numberrows;
	}

	/**
	 * Imposta il numero di righe risultanti da una select query
	 * 
	 * @param integer
	 */
	private function setNumberRows($numberresults) {
		$this->_numberrows = $numberresults;
	}
	
	/**
	 * Ritorna il numero di colonne richiamate in una istruzione SELECT
	 * 
	 * @return integer
	 */
	public function getNumberCols() {
		return $this->_numbercols;
	}
	
	/**
	 * Imposta il numero di colonne richiamate in una istruzione SELECT
	 *
	 * @param integer
	 */
	private function setNumberCols($number) {
		$this->_numbercols = $number;
	}
	
	private function setConnection($connection) {
		$this->_connection = $connection;
	}
	
	/**
	 * Ritorna il numero di righe interessate da una istruzione INSERT, UPDATE o DELETE
	 *
	 * @return integer
	 */
	public function getAffectedRows() {
		return $this->_affected;
	}
	
	/**
	 * Imposta il numero di righe interessate da una istruzione INSERT, UPDATE o DELETE
	 *
	 * @param integer $number
	 */
	private function setAffectedRows($number) {
		$this->_affected = $number;
	}
	
	/**
	 * Recupera il tipo di errore
	 * @return string
	 * 
	 * A prescindere dalla modalità che è impostato, c'è un codice di errore interno che viene impostato e che si può controllare 
	 * utilizzando i metodi errorCode() e errorInfo() degli oggetti POD e PDOStatement. \n
	 * errorCode() restituisce una stringa di 5 caratteri, come definito nella ANSI SQL-92. \n
	 * errorInfo() è generalmente più utile in quanto restituisce un array che comprende un messaggio di errore in aggiunta al codice a 5 caratteri.
	 */
	private function getError() {
		
		$error = $this->_pdo->errorInfo();
		if(!is_null($error[2])) {
			return $error[2];
		}
		else return null;
	}
	
	/**
	 * Verifica se una istruzione SELECT ha avuto esito postivo
	 * 
	 * @param object $result PDOStatement object
	 * @return integer (0 negative)
	 */
	protected function checkRowsFromSelect($result) {
		
		return $result->rowCount();
	}
	
	/**
	 * @see Gino.DbManager::getInfoQuery()
	 */
	public function getInfoQuery() {
 		
 		if($this->_show_stats) {
 			$query_cache = $this->_query_cache ? 'On' : 'Off';
 			$buffer = "<p>Query cache: ".$query_cache."</p>";
 			$buffer .= "<p>Number of db queries: ".$this->_cnt."</p>";
			$buffer .= "<p>Time of db queries: ".$this->_time_queries." seconds</p>";
			$buffer .= "<table class=\"table table-bordered table-striped table-hover\">";
			$buffer .= "<tr><th>Query</th><th class=\"nowrap\">Execution time (ms)</th></tr>";
			foreach($this->_info_queries as $q) {
				$buffer .= "<tr>";
				$buffer .= "<td>".$q[0]."</td>";
				$buffer .= "<td>".$q[1]."</td>";
				$buffer .= "</tr>";
			}
			$buffer .= "</table>";
			return $buffer;
		}
		else return null;
	}
	
	/**
	 * Esegue la query e ne calcola i tempi di esecuzione
	 *
	 * @param string $query query da eseguire
	 * @param array $options array associativo di opzioni
	 *   - @b statement (string): tipologia di query
	 *     - @a select (default)
	 *     - @a action
	 *   - @b parameters (boolean): indica se la query è parametrizzata
	 *     - @a true, prima prepara e poi esegue la query
	 *     - @a false (default), esegue la query
	 *   - @b values (array): elenco dei valori da sostituire alle variabili parametrizzate
	 * @return PDOStatement object
	 */
	protected function queryResults($query, $options=array()) {
	
		if (!$this->_connection) {
			$this->openConnection();
		}
		
		$statement = \Gino\gOpt('statement', $options, 'select');
		$parameters = \Gino\gOpt('parameters', $options, false);
		$values = \Gino\gOpt('values', $options, null);
		
		if($this->_show_stats) {
			$this->_cnt++;
				
			$msc = \Gino\getmicrotime();
		}
		
		if($parameters)
		{
			$stmt = $this->_pdo->prepare($query);
			$res = $stmt->execute($values);
			
			if($statement == 'select')
			{
				$this->setNumberRows($stmt->rowCount());
				$this->setNumberCols($stmt->columnCount());
			}
			else
			{
				$this->setAffectedRows($stmt->rowCount());
			}
		}
		else
		{
			if($statement == 'select')
			{
				$res = $this->_pdo->query($query);
				
				if(!$res)
					throw new \Exception(_("Errore nella query").' '.$query);
				
				$rows = $this->checkRowsFromSelect($res);
				$this->setNumberRows($rows);
				$this->setNumberCols($res->columnCount());
			}
			else
			{
				$res = $this->_pdo->exec($query);
				$this->setAffectedRows($res);
			}
		}
		
		if($this->_show_stats) {
			$msc = \Gino\getmicrotime()-$msc;
			$this->_time_queries += $msc;
				
			$this->_info_queries[] = array($query, $msc);
		}
		
		if($statement != 'select')
		{
			if($res && $this->_query_cache)
			{
				$this->_cache->clean();
			}
		}
		
		return $res;
	}
	
	/**
 	 * Imposta la modalità di recupero dei risultati della query
 	 * 
 	 * @param object $results oggetto PDOStatement
 	 * @param array $options
 	 *   array associativo di opzioni
 	 *   - @b mode (string): modalità di recupero dei risultati della query
 	 *     - @a ASSOC (default)
 	 *     - @a NUM
 	 *     - @a OBJ
 	 *     - @a CLASS
 	 *   - @b classname (string): nome della classe da richiamare (modalità CLASS)
 	 *   - @b construct_args (array): argomenti da passare al costruttore della classe da richiamare (modalità CLASS)
 	 */
 	private function setFetchMode($results, $options=array()) {
 		
 		$mode = \Gino\gOpt('mode', $options, 'ASSOC');
 		
 		if($mode == 'ASSOC')
 			$pdo_mode = \PDO::FETCH_ASSOC;
 		elseif($mode == 'NUM')
 			$pdo_mode = \PDO::FETCH_NUM;
 		elseif($mode == 'OBJ')
 			$pdo_mode = \PDO::FETCH_OBJ;
 		elseif($mode == 'CLASS')
 			$pdo_mode = \PDO::FETCH_CLASS;
 		else
 			$pdo_mode = null;
 		
 		if($mode == 'CLASS')
 		{
 			$classname = \Gino\gOpt('classname', $options, null);
 			$construct_args = \Gino\gOpt('construct_args', $options, array());
 			
 			$results->setFetchMode($pdo_mode, $classname, $construct_args);
 		}
 		else
 		{
 			$results->setFetchMode($pdo_mode);
 		}
 		
 		return null;
 	}
 	
 	/**
 	 * Prende e rende fruibili i risultati della query
 	 * 
 	 * @param object $results oggetto PDOStatement
 	 * @param array $options
 	 *   array associativo di opzioni
 	 *   - @b set_mode (boolean): indica se impostate il fetch mode (default true)
 	 *   - @b fetchAll (boolean): indica se utilizzare il metodo fetchAll per prendere i risultati (default false)
 	 *   - opzioni del metodo setFetchMode()
 	 * @return array (fetchAll=false) or object (fetchAll=true)
 	 */
 	protected function fetch($results=null, $options=array()) {
 	
 		$set_mode = \Gino\gOpt('set_mode', $options, true);
 		$fetchAll = \Gino\gOpt('fetchAll', $options, false);
 	
 		if(is_null($results)) $results = $this->_result;
 	
 		if($set_mode) $this->setFetchMode($results, $options);
 		
 		$method = $fetchAll ? 'fetchAll' : 'fetch';
 		
 		return $results->$method();
 	}
 	
 	/**
 	 * Elenco dei character set accettati
 	 * @return array
 	 */
 	protected static function getCharset() {
 		
 		$items = array('utf8');
 		return $items;
 	}
 	
 	/**
 	 * Imposta la stringa di connessione al database
 	 * 
 	 * @return string
 	 */
 	protected function setDSN() {
 	
 		return null;
 	}
 	
 	/**
 	 * Attributi da associare alla connessione
 	 * 
 	 * @return array
 	 */
 	protected function setAttribute() {
 	
 		return array();
 	}
 	
 	/**
	 * @see Gino.DbManager::openConnection()
	 * 
	 * @param array $opt
	 *   array associativo di opzioni
	 *   - @b persistent (boolean): connessione persistente (default true)
	 *   - @b get_attribute (boolean): recupera i valori degli attibuti della connessione (default false)
	 */
	public function openConnection($opt=array()) {

		$persistent = \Gino\gOpt('persistent', $opt, true);
		$get_attribute = \Gino\gOpt('get_attribute', $opt, false);
		
		$dsn = $this->setDSN();
		
		$options = array(
			\PDO::ATTR_PERSISTENT => $persistent
		);
		
		$attributes = $this->setAttribute();
		if(is_array($attributes) && count($attributes)) {
			$attr = array_merge($options, $attributes);
		}
		else $attr = $options;
		
		try {
			$this->_pdo = new \PDO($dsn, $this->_db_user, $this->_db_password, $attr);
			
			if($get_attribute)
				$this->getAttribute($this->_pdo);
			
			$this->setConnection(true);
			return true;
		}
		catch (PDOException $e) {
			throw new \Exception($e->getMessage());
		}
	}
	
	/**
	 * Stampa il valore degli attributi associati alla connessione
	 * 
	 * @param object $connection PDO object
	 */
	private function getAttribute($connection) {
		
		$attributes = array(
			"AUTOCOMMIT", "ERRMODE", "CASE", "CLIENT_VERSION", "CONNECTION_STATUS",
			"ORACLE_NULLS", "PERSISTENT", "PREFETCH", "SERVER_INFO", "SERVER_VERSION",
			"TIMEOUT", "EMULATE_PREPARES"
		);

		foreach ($attributes as $val) {
			
			echo "PDO::ATTR_$val: ";
			echo $connection->getAttribute(constant("PDO::ATTR_$val")) . "<br />";
		}
	}
		
	/**
	 * Imposta il character set del database
	 * 
	 * Nota: non è necessario se lo si specifica nella stringa della connessione
	 */
	protected function setCharacterSet() {
		
		return null;
	}
	
	/**
	 * @see Gino.DbManager::closeConnection()
	 */
	public function closeConnection() {
		if($this->_connection) {
			unset($this->_pdo);
		}
	}
	
	/**
	 * @see Gino.DbManager::begin()
	 */
	public function begin() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->_pdo->beginTransaction();
	}
	
	/**
	 * @see Gino.DbManager::rollback()
	 */
	public function rollback() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->_pdo->rollBack();
	}

	/**
	 * @see Gino.DbManager::commit()
	 */
	public function commit() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->_pdo->commit();
	}
	
	/**
	 * @see Gino.DbManager::multiActionQuery()
	 * @see driver
	 */
	public function multiActionQuery($queries) {
	
		return false;
	}

	/**
	 * @see Gino.DbManager::freeresult()
	 */
	public function freeresult($result=null) {
	
		if(is_null($result)) $result = $this->_result;
		$result->closeCursor();
	}
	
	/**
	 * @see Gino.DbManager::getLastId()
	 */
	public function getLastId($table) { 
		
		if($this->_affected > 0)
		{
			$lastid = $this->_pdo->lastInsertId();
		}
		else
		{
			$lastid = false;
		}
		return $lastid;
	}
	
	/**
	 * @see Gino.DbManager::autoIncValue()
	 * @see driver
	 */
	public function autoIncValue($table) {

		return null;
	}
	
	/**
	 * @see Gino.DbManager::getFieldFromId()
	 */
	public function getFieldFromId($table, $field, $field_id, $id, $options=array()) {
		
		$res = $this->select($field, $table, "$field_id='$id'", $options);
		if(!$res){
			return '';
		}
		else
		{
			foreach($res as $r) {
				return $r[$field];
			}
		}
	}
	
	/**
	 * @see Gino.DbManager::tableexists()
	 * @see driver
	 */
	public function tableexists($table){
		
		return null;
	}
	
	/**
	 * @see Gino.DbManager::fieldInformations()
	 * @see SQLForFieldInformations()
	 */
	public function fieldInformations($table) {
	
		if($this->_connection) {
			$this->openConnection();
		}
		
		$query = $this->SQLForFieldInformations($table);
		$this->_result = $this->queryResults($query);
		
		if(!$this->_result) {
			return false;
		} else {
			$column = array();
				
			for ($i=0; $i < $this->_numbercols; $i++) {
		
				$meta = $this->_result->getColumnMeta($i);
		
				$length = $meta['len'];
				$precision = $meta['precision'];
				
				$array_tmp = array(
						'name'=>$meta['name'],
						'type'=>$this->conformFieldType($meta),
						'length'=>$length
				);
		
				$column[$i] = \Gino\arrayToObject($array_tmp);
			}
				
			$this->freeresult();
			return $column;
		}
	}
	
	/**
	 * @see Gino.DbManager::conformFieldType()
	 * @see driver
	 */
	public function conformFieldType($meta) {
		
		return null;
	}
	
	/**
	 * @see Gino.DbManager::limit()
	 * @see driver
	 */
	public function limit($range, $offset) {
		
		return null;
	}
	
	/**
	 * @see Gino.DbManager::distinct()
	 * @see driver
	 */
	public function distinct($fields, $options=array()) {
		
		return null;
	}
	
	/**
	 * @see Gino.DbManager::concat()
	 * @see driver
	 */
	public function concat($sequence) {
		
		return null;
	}

	/**
	 * @see Gino.DbManager::dumpDatabase()
	 * @see driver
	 */
	public function dumpDatabase($file) {

		return null;
	}
	
	/**
	 * @see Gino.DbManager::changeFieldType()
	 */
	public function changeFieldType($data_type, $value) {
	
		return $value;
	}

	/**
	 * @see Gino.DbManager::getNumRecords()
	 */
	public function getNumRecords($table, $where=null, $field='id', $options=array()) {

		$tot = 0;
		
		$res = $this->select("COUNT($field) AS tot", $table, $where, $options);
		if($res) {
			$tot = $res[0]['tot'];
		}
		
		return (int) $tot;
	}
	
	/**
	 * @see Gino.DbManager::query()
	 * @see buildQuery()
	 */
	public function query($fields, $tables, $where=null, $options=array()) {
	
		$order = \Gino\gOpt('order', $options, null);
		$group_by = \Gino\gOpt('group_by', $options, null);
		$distinct = \Gino\gOpt('distinct', $options, null);
		$limit = \Gino\gOpt('limit', $options, null);
		$debug = \Gino\gOpt('debug', $options, false);
	
		$qfields = is_array($fields) ? implode(",", $fields) : $fields;
		$qtables = is_array($tables) ? implode(",", $tables) : $tables;
		$qgroup_by = $group_by ? "GROUP BY ".$group_by : "";
		$qorder = $order ? "ORDER BY $order" : "";
		
		if($where)
		{
			$qwhere = "WHERE ";
			$qwhere .= (is_array($where) && count($where)) ? $where[0] : $where;
		}
		else $qwhere = "";
	
		if($distinct) $qfields = $distinct.", ".$qfields;
		
		$query = $this->buildQuery(array(
			'fields'=>$qfields,
			'tables'=>$qtables,
			'where'=>$qwhere,
			'group_by'=>$qgroup_by,
			'order'=>$qorder,
			'limit'=>$limit, 
			'debug'=>$debug
		));
		
		if($debug) echo $query;
		
		if(is_null($query))
			throw new \Exception(_("Errore nella formattazione della query"));
	
		return $query;
	}
	
	/**
	 * Imposta la condizione where e i valori per le query con variabili parametrizzate
	 * 
	 * @param mixed $where
	 *   - @a string, condizione where non parametrizzata
	 *   - @a array, condizione where con variabili parametrizzate, nel formato
	 *     @code
	 *     array(
	 *       0 => (string) where condition with named parameters or ? character
	 *       1 => (array) binding parameters,  array(param=>value[,...]) or array(value[,...])
	 *     )
	 *     @endcode
	 * @return array(where_condition[string], parameter_values[array], parameter_query[bool])
	 * 
	 * Esempi di input where per una query con variabili parametrizzate:
	 * @code
	 * $where = array(
	 * 	 "username = :username AND email = :email AND last_login > :last_login", 
	 *   array(':username'=>'test', ':email'=>$mail, ':last_login'=>time() - 3600)
	 * )
	 * 
	 * $where = array(
	 * 	 "id=? AND name=?", 
	 *   array($id, $name)
	 * )
	 * @endcode
	 */
	private function setWhereItems($where) {
	
		$params = false;
		$values = null;
	
		if(is_array($where) && count($where))
		{
			$where_cond = $where[0];
	
			if(count($where)>=2)
			{
				$params = true;
				$values = $where[1];
			}
		}
		else
		{
			$where_cond = $where;
		}
		
		return array($where_cond, $values, $params);
	}
	
	/**
	 * @see Gino.DbManager::execCustomQuery()
	 */
	public function execCustomQuery($query, $options=array()) {
		
		$statement = \Gino\gOpt('statement', $options, 'select');
		
		if($statement == 'select')
		{
			$options['custom_query'] = $query;
			return $this->select(null, null, null, $options);
		}
		else
		{
			$this->_result = $this->queryResults($query, array('statement'=>'action'));
			if($this->_result)
				return true;
			else
				return false;
		}
	}
	
	/**
	 * @see Gino.DbManager::select()
	 * @see queryResults()
	 * @see fetch()
	 */
	public function select($fields, $tables, $where=null, $options=array()) {
		
		$custom_query = \Gino\gOpt('custom_query', $options, null);
		$cache = \Gino\gOpt('cache', $options, true);
		$identity_keyword = \Gino\gOpt('identity_keyword', $options, null);
		$time_caching = \Gino\gOpt('time_caching', $options, null);
		
		// Query Definition
		if($custom_query)
		{
			$parameters = false;
			$where_values = null;
			
			$query = $custom_query;
		}
		else
		{
			$w = $this->setWhereItems($where);
			$where_cond = $w[0];
			$where_values = $w[1];
			$parameters = $w[2];
			
			$query = $this->query($fields, $tables, $where_cond, $options);
		}
		
		if(!$identity_keyword) $identity_keyword = $query;
		
		if(!($this->_query_cache && $cache) OR is_null($results = $this->_cache->get($identity_keyword)))
		{
			$this->_result = $this->queryResults($query, array('parameters'=>$parameters, 'values'=>$where_values));
			if(!$this->_result)
			{
				$results = false;
			}
			else
			{
				$results = array();
				
				if($this->_numberrows > 0) {
					while($rows = $this->fetch($this->_result))
					{
						$results[] = $rows;
					}
				}
				$this->freeresult();
			}
			
			if($this->_query_cache && $cache)
			{
				$this->_cache->set($results, array('time_caching'=>$time_caching));
			}
		}
		
		return $results;
	}
	
	/**
	 * @see Gino.DbManager::insert()
	 */
	public function insert($fields, $table, $debug=false) {

		$placeholder = false;
		
		if(is_array($fields) && count($fields) && $table)
		{
			$a_fields = array();
			$a_params = array();
			$a_values = array();
			
			foreach($fields AS $field=>$value)
			{
				if(is_array($value))
				{
					if(array_key_exists('sql', $value))
						$a_fields[] = $this->setFieldName($field)."=".$value['sql'];
				}
				else
				{
					if($value !== null) {
						
						if($placeholder == false)
						{
							$a_fields[] = $field;
							$a_values[] = $this->setFieldValue($value);
						}
						else
						{
							$param = ':'.$field;
							
							$a_fields[] = $field;
							$a_params[] = $param;
							$a_values[$param] = $value;
						}
					}
				}
			}
			
			$s_fields = $this->arrayFieldsToString($a_fields);
			
			if($placeholder == false)
			{
				$s_values = implode(",", $a_values);
				$query = "INSERT INTO $table ($s_fields) VALUES ($s_values)";
				$parameters = false;
			}
			else
			{
				$s_params = implode(",", $a_params);
				$query = "INSERT INTO $table ($s_fields) VALUES ($s_params)";
				$parameters = true;
			}
			
			if($debug) echo $query;
			
			$this->_result = $this->queryResults($query, array('statement'=>'action', 'parameters'=>$parameters, 'values'=>$a_values));
			if($this->_result)
				return true;
			else
				return false;
		}
		else return false;
	}
	
	/**
	 * @see Gino.DbManager::update()
	 */
	public function update($fields, $table, $where, $debug=false) {

		$placeholder = false;
		
		if(is_array($fields) && count($fields) && $table)
		{
			$a_fields = array();
			$a_values = array();
			
			foreach($fields AS $field=>$value)
			{
				if(is_array($value))
				{
					if(array_key_exists('sql', $value))
						$a_fields[] = $this->setFieldName($field)."=".$value['sql'];
				}
				else
				{
					if($placeholder == false)
					{
						//$a_fields[] = ($value == 'null') ? "`$field`=$value" : "`$field`='$value'";
						$a_fields[] = $value === null ? $this->setFieldName($field)."=NULL" : $this->setFieldName($field)."=".$this->setFieldValue($value);
					}
					else
					{
						$param = ':'.$field;
						
						$a_fields[] = $this->setFieldName($field)."=$param";
						$a_values[$param] = $value === null ? "NULL" : $value;
					}
				}
			}
			
			// Where Condition
			$w = $this->setWhereItems($where);
			$where_cond = $w[0];
			$where_values = $w[1];
			
			$s_where = $where_cond ? " WHERE ".$where_cond : "";
			if(is_array($where_values) && count($where_values))
			{
				$a_values = array_merge($a_values, $where_values);
			}
			// End Where
			
			$s_fields = implode(",", $a_fields);
			
			$query = "UPDATE $table SET $s_fields".$s_where;
			
			if($debug) echo $query;
			
			$parameters = $placeholder ? true : false;
			
			$this->_result = $this->queryResults($query, array('statement'=>'action', 'parameters'=>$parameters, 'values'=>$a_values));
			if($this->_result)
				return true;
			else
				return false;
		}
		else return false;
	}
	
	/**
	 * @see Gino.DbManager::delete()
	 */
	public function delete($table, $where, $debug=false) {

		if(!$table) return false;
		
		// Where Condition
		$w = $this->setWhereItems($where);
		$where_cond = $w[0];
		$where_values = $w[1];
		$parameters = $w[2];
		
		$s_where = $where_cond ? " WHERE ".$where_cond : "";
		// End Where
		
		$query = "DELETE FROM $table".$s_where;
		
		if($debug) echo $query;
		
		$this->_result = $this->queryResults($query, array('statement'=>'action', 'parameters'=>$parameters, 'values'=>$where_values));
		if($this->_result)
			return true;
		else
			return false;
	}

	/**
	 * @see Gino.DbManager::drop()
	 */
	public function drop($table) {

		if(!$table) return false;
		
		$query = "DROP TABLE $table";
		
		$this->_result = $this->queryResults($query, array('statement'=>'action'));
		if($this->_result)
			return true;
		else
			return false;
	}

	/**
	 * @see Gino.DbManager::columnHasValue()
	 */
	public function columnHasValue($table, $field, $value, $options=array()) {
		
		$except_id = \Gino\gOpt('except_id', $options, null);
		
		$where = $field."='$value'";
		if($except_id) $where .= " AND id!='$except_id'";
		
		$rows = $this->select($field, $table, $where);
		return $rows and count($rows) ? true : false;
	}
	
	/**
	 * @see Gino.DbManager::join()
	 */
	public function join($table, $condition, $option) {
		
		$join = $table;
		if($condition) $join .= ' ON '.$condition;
		if($option) $join = strtoupper($option).' '.$join;
		
		return $join;
	}
	
	/**
	 * @see Gino.DbManager::union()
	 * 
	 * In mssql possono essere utilizzati gli operatori: \n
	 * - UNION, elimina le righe duplicate dai risultati combinati delle istruzioni SELECT \n
	 * - UNION ALL, mostra i record duplicati
	 */
	public function union($queries, $options=array()) {
		
		$debug = \Gino\gOpt('debug', $options, false);
		$cache = \Gino\gOpt('cache', $options, true);
		$instruction = \Gino\gOpt('instruction', $options, 'UNION');
		
		if(count($queries))
		{
			$query = implode(" $instruction ", $queries);
			
			if($debug) echo $query;
			
			return $this->select(null, null, null, array('custom_query'=>$query, 'cache'=>$cache));
		}
		return array();
	}
	
	/**
	 * @see Gino.DbManager::restore()
	 */
	public function restore($table, $filename, $options=array()) {      
	
		return null;
	}
	
	/**
	 * @see Gino.DbManager::dump()
	 * @see SQLForDump()
	 */
	public function dump($table, $path_to_file, $options=array()) {
		
		$where = \Gino\gOpt('where', $options, null);
		$delimiter = \Gino\gOpt('delim', $options, ',');
		$enclosed = \Gino\gOpt('enclosed', $options, null);
		
		$where = $where ? " WHERE $where" : '';
		$enclosed = $enclosed ? "ENCLOSED BY '".$enclosed."' " : '';
		
		$query = $this->SQLForDump($table, $path_to_file, $delimiter, $enclosed, $where);
		$this->_result = $this->queryResults($query, array('statement'=>'action'));
		if($this->_result)
			return $path_to_file;
		else
			return null;
	}
	
	/**
	 * @see Gino.DbManager::escapeString()
	 */
	public function escapeString($string) {
		
		$string = $this->_pdo->quote($string);
		
		$first = $string{0};
		$last = substr($string, -1);
		
		if($first == "'" && $last == "'")
		{
			$string = substr($string, 1, -1);
		}
		
		return $string;
	}
	
	/**
	 * Costruisce la query (personalizzata per ogni driver)
	 * 
	 * @see driver
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b statement (string): tipo di istruzione sql (default @a select)
	 *   - @b fields (string)
	 *   - @b tables (string)
	 *   - @b where (string)
	 *   - @b group_by (string)
	 *   - @b order (string)
	 *   - @b limit (array|string)
	 *   - @b debug (boolean)
	 * @return string
	 */
	protected function buildQuery($options=array()) {
		
		return null;
	}
	
	/**
	 * Imposta il nome del campo in una query
	 * 
	 * @param string $field nome del campo
	 * @return string
	 */
	protected function setFieldName($field) {
		
		return $field;
	}
	
	/**
	 * Imposta il valore del campo in una query
	 *
	 * @param mixed $value valore del campo
	 * @return string
	 */
	protected function setFieldValue($value) {
		
		return $value;
	}
	
	/**
	 * Formatta l'elenco dei campi in una istruzione insert
	 * 
	 * @param array $a_fields elenco dei campi
	 * @return string
	 */
	protected function arrayFieldsToString($a_fields) {
	
		$fields = implode(',', $a_fields);
		return $fields;
	}
	
	/**
	 * SQL code specifico del driver per il metodo fieldInformations()
	 *
	 * @param string $table nome della tabella
	 * @return string
	 */
	protected function SQLForFieldInformations($table) {
	
		return null;
	}
	
	/**
	 * SQL code specifico del driver per il metodo dump()
	 * 
	 * @param string $table nome della tabella
	 * @param string $path_to_file nome del file completo di percorso
	 * @param string $delimiter stringa che viene usata per separare tra loro i valori dei campi
	 * @param string $enclosed carattere utilizzato per racchiudere i valori di tipo stringa
	 * @param string $where condizioni della query
	 * @return string
	 */
	protected function SQLForDump($table, $path_to_file, $delimiter, $enclosed, $where) {
		
		return null;
	}
}

?>
