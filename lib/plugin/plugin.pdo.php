<?php
/**
 * @file plugin.pdo.php
 * @brief Contiene la classe pdo
 * 
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * CACHE QUERY
 * ---------------
 * La proprietà self::$_query_cache indica se è stata abilita la cache delle query. \n
 * Le query che vengono salvate in cache sono quelle che passano dal metodo select(), e non riguardano quindi le query di struttura, quali quelle presenti nei metodi:
 *   - fieldInformations()
 *   - getTableStructure()
 *   - getFieldsName()
 * 
 * Qualora non si desideri caricare in cache una determinata query è sufficienete passare l'opzione @a cache=false al metodo select(). \n
 * La cache delle query viene svuotata ogni volta che viene eseguita una query di tipo @a action (metodo queryResults()).
 * 
 * INFORMAZIONI SULLE QUERY
 * ---------------
 * La proprietà self::$_show_stats attiva la raccolta di informazioni sulle prestazioni delle chiamate al database. \n
 * Le query di tipo select alimentano i contatori self::$_cnt e self::$_time_queries.
 */
class pdo implements \Gino\DbManager {

	private $_dbms, $_db_host, $_db_name, $_db_user, $_db_password, $_db_charset;
	private $_numberrows;
	private $_affected;
	
	/**
	 * Stato della connessione al database
	 * 
	 * @var boolean
	 */
	private $_connection;
	
	/**
	 * PDO object
	 *
	 * @var object
	 */
	private $_pdo;
	
	/**
	 * PDOStatement object
	 * 
	 * @var object
	 */
	private $_result;
	
	/**
	 * Attiva le statische sulle query
	 * 
	 * @var boolean
	 */
	private $_show_stats;
	
	/**
	 * Informazioni sulle query eseguite
	 *
	 * @var array
	 */
	private $_info_queries;
	
	/**
	 * Contatore di query
	 * 
	 * @var integer
	 */
	private $_cnt;
	
	/**
	 * Tempo totale di esecuzione delle query
	 * 
	 * @var float
	 */
	private $_time_queries;
	
	/**
	 * Query cache
	 * 
	 * @var boolean
	 */
	private $_query_cache;
	
	/**
	 * Tempo di durata della query cache
	 *
	 * @var integer
	 */
	private $_query_cache_time;
	
	/**
	 * Oggetto plugin_phpfastcache
	 * 
	 * @var object
	 */
	private $_cache;
	
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
		
		$this->_range = null;
		$this->_offset = null;
		
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
	 * Ritorna il numero di record risultanti da una select query
	 * @return integer
	 */
	public function getNumberRows() {
		return $this->_numberrows;
	}

	/**
	 * Imposta il numero di record risultanti da una select query
	 * 
	 * @param integer $numberresults
	 */
	private function setNumberRows($numberresults) {
		$this->_numberrows = $numberresults;
	}
	
	private function setConnection($connection) {
		$this->_connection = $connection;
	}
	
	/**
	 * Ritorna il numero di record interessati da una istruzione INSERT, UPDATE o DELETE
	 *
	 * @return integer
	 */
	public function getAffectedRows() {
		return $this->_affected;
	}
	
	/**
	 * Imposta il numero di record interessati da una istruzione INSERT, UPDATE o DELETE
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
	 * 
	 * A prescindere dalla modalità che è impostato, c'è un codice di errore interno che viene impostato e che si può controllare 
	 * utilizzando i metodi errorCode() e errorInfo() degli oggetti POD e PDOStatement. 
	 * errorCode() restituisce una stringa di 5 caratteri, come definito nella ANSI SQL-92. 
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
	 * @see DbManager::affected()
	 */
	public function affected() {
		return null;
	}
	
	/**
	 * @see DbManager::getInfoQuery()
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
	private function queryResults($query, $options=array()) {
	
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
				$this->setNumberRows($res->rowCount());
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
 	private function fetch($results=null, $options=array()) {
 	
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
 	private static function getCharset() {
 		
 		$items = array('utf8');
 		return $items;
 	}
 	
 	/**
	 * @see DbManager::openConnection()
	 */
	public function openConnection() {

		$options = array(
			//\PDO::ATTR_PERSISTENT => true, 
			\PDO::ATTR_EMULATE_PREPARES => false,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		);
		
		$dsn = $this->_dbms.":host=".$this->_db_host.";dbname=".$this->_db_name;
		if($this->_db_charset && in_array($this->_db_charset, self::getCharset()))
		{
			$dsn .= ";charset=".$this->_db_charset;
		}
		
		try {
			$this->_pdo = new \PDO($dsn, $this->_db_user, $this->_db_password, $options);
			
			$this->setConnection(true);
			return true;
		}
		catch (PDOException $e) {
			throw new \Exception($e->getMessage());
		}
	}
	
	/**
	 * Imposta il character set del database
	 * 
	 * Nota: non è necessario se lo si specifica nella stringa della connessione
	 */
	private function setCharacterSet() {
		
		if($this->_db_charset && in_array($this->_db_charset, self::getCharset()))
		{
			$this->_pdo->exec("SET NAMES ".$this->_db_charset);
		}
	}
	
	/*
	private function setUtf8() {
		
		$query = "SHOW VARIABLES LIKE 'character_set_database'";
		$res_db_charset = $this->queryResults($query);
		$charset_row = $this->fetch($res_db_charset);
		
		$this->queryResults("SET NAMES '".$charset_row['Value']."'");
		
		unset($res_db_charset, $charset_row);
	}
	*/

	/**
	 * Chiude le connessioni non persistenti al database
	 * 
	 * @see DbManager::closeConnection()
	 */
	public function closeConnection() {

		if($this->_connection){
			$this->_pdo = null;
		}
	}
	
	/**
	 * @see DbManager::begin()
	 */
	public function begin() {
		if (!$this->_connection){
			$this->openConnection();
		}
		$this->_pdo->beginTransaction();
	}
	
	/**
	 * @see DbManager::rollback()
	 */
	public function rollback() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->_pdo->rollBack();
	}

	/**
	 * @see DbManager::commit()
	 */
	public function commit() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->_pdo->commit();
	}
	
	/**
	 * @see DbManager::actionquery()
	 * 
	 * Eliminare quando si elimina il richiamo a actionquery in class.Form.php
	 */
	public function actionquery($query) {
		
		if (!$this->_connection) {
			$this->openConnection();
		}
		
		$this->_result = $this->queryResults($query, array('statement'=>'action', 'parameters'=>false, 'values'=>null));
		if($this->_result)
			return true;
		else
			return false;
	}

	/**
	 * @see DbManager::multiActionquery()
	 */
	public function multiActionquery($queries) {
	
		$conn = mysqli_connect($this->_db_host, $this->_db_user, $this->_db_password, $this->_db_name);
		$this->_result = mysqli_multi_query($conn, $queries);

		return $this->_result ? true : false;
	}

	/**
	 * @see DbManager::selectquery()
	 */
	public function selectquery($qry) {

		return null;
	}
	
	/**
	 * @see DbManager::freeresult()
	 */
	public function freeresult($result=null) {
	
		if(is_null($result)) $result = $this->_result;
		$result->closeCursor();
	}
	
	/**
	 * @see DbManager::resultselect()
	 */
	public function resultselect($qry) {
		
		return null;
	}
	
	/**
	 * Il valore della funzione SQL LAST_INSERT_ID() di MySQL contiene sempre il più recente valore AUTO_INCREMENT generato e non è azzerato dalle query
	 * 
	 * @see DbManager::getlastid()
	 */
	public function getlastid($table) { 
		
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
	 * Ottiene il valore del campo AUTO_INCREMENT
	 * 
	 * @see DbManager::autoIncValue()
	 */
	public function autoIncValue($table) {

		$query = "SHOW TABLE STATUS LIKE '$table'";
		
		$a = $this->select(null, null, null, array('custom_query'=>$query, 'cache'=>false));
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$auto_increment = $b['Auto_increment'];
			}
		}
		else $auto_increment = 0;
		
		return $auto_increment;
	}
	
	/**
	 * @see DbManager::getFieldFromId()
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
	 * @see DbManager::tableexists()
	 */
	public function tableexists($table){
		
		$query = "SHOW TABLES FROM `".$this->_db_name."`";
		$res = $this->queryResults($query);
		
		while($row = $this->fetch($res, array('mode'=>'NUM'))) {
			
			if($row[0] == $table) return true;
		}
		return false;
	}
	
	/**
	 * @see DbManager::fieldInformations()
	 * @see conformType()
	 */
	public function fieldInformations($table) {
	
		if($this->_connection) {
			$this->openConnection();
		}
		
		$query = "SELECT * FROM ".$table." LIMIT 0,1";
		$this->_result = $this->queryResults($query);
		
		if(!$this->_result) {
			return false;
		} else {
			$column = array();
			
			for ($i=0; $i < $this->_numberrows; $i++) {
				
				$meta = $this->_result->getColumnMeta($i);
				
				$len = $meta['len'];
				$precision = $meta['precision'];
				$type = $this->mapPDOType($meta['native_type']);
				
				$array_tmp = array(
					'name'=>$meta['name'],
					'type'=>$this->conformType($type),
					'length'=>$len
				);
				
				$column[$i] = \Gino\arrayToObject($array_tmp);
			}
			
			$this->freeresult();
			return $column;
		}
	}
	
	private function mapPDOType($type) {
		
		$type = strtolower($type);
		switch ($type) {
			case 'tiny':
			case 'short':
			case 'long':
			case 'longlong';
			case 'int24':
				return 'int';
			case 'null':
				return null;
			case 'varchar':
			case 'var_string':
			case 'string':
				return 'string';
			case 'blob':
			case 'tiny_blob':
			case 'long_blob':
				return 'blob';
			default:
				return $type;
		}
	}
	
	/**
	 * @see DbManager::conformType()
	 * 
	 * @param string $type
	 * 
	 * Come tipo di dato di un campo la funzione mysql_fetch_field() rileva: int, blob, string, date
	 */
	public function conformType($type) {
		
		$type = $this->mapPDOType($type);
		
		if($type == 'string')
			$conform_type = 'char';
		elseif($type == 'blob')
			$conform_type = 'text';
		else
			$conform_type = $type;
		
		return $conform_type;
	}
	
	/**
	 * @see DbManager::limit()
	 */
	public function limit($range, $offset) {
		
		$limit = "LIMIT $offset, $range";
		return $limit;
	}
	
	/**
	 * @see DbManager::distinct()
	 */
	public function distinct($fields, $options=array()) {
		
		$alias = \Gino\gOpt('alias', $options, null);
		
		if(!$fields) return null;
		
		$data = "DISTINCT($fields)";
		if($alias) $data .= " AS $alias";
		
		return $data;
	}
	
	/**
	 * @see DbManager::concat()
	 */
	public function concat($sequence) {
		
		if(is_array($sequence))
		{
			if(sizeof($sequence) > 1)
			{
				$string = implode(',', $sequence);
				$concat = "CONCAT($string)";
			}
			else $concat = $sequence[0];
		}
		else $concat = $sequence;
		
		return $concat;
	}

	/**
	 * @see DbManager::dumpDatabase()
	 */
	public function dumpDatabase($file) {

		$query = "SHOW TABLES FROM ".$this->_db_name;
		$tables = $this->queryResults($query);
		
		while ($td = $this->fetch($tables, array('mode'=>'NUM'))) {

			$table = $td[0];
			
			$r = $this->queryResults("SHOW CREATE TABLE `$table`");
			if($r)
			{
				$insert_sql = "";
				$d = $this->fetch($r, array('mode'=>'NUM'));
				$d[1] .= ";";
				$SQL[] = str_replace("\n", "", $d[1]);
				
				$table_query = $this->queryResults("SELECT * FROM `$table`");
				
				$num_fields = $this->getNumberRows();
				while ($fetch_row = $this->fetch($table_query, array('mode'=>'NUM'))) {
					$insert_sql .= "INSERT INTO $table VALUES(";
					for ($n=1; $n<=$num_fields; $n++) {
						$m = $n - 1;
						$insert_sql .= "'".$this->_pdo->quote($fetch_row[$m]).($n==$num_fields ? "" : "', ");	//// VIRGOLETTE ????
					}
					$insert_sql .= ");\n";
				}
				if ($insert_sql!= "") {
					$SQL[] = $insert_sql;
				}
			}
		}

		if(!($fo = fopen($file, 'wb'))) return false;
		if(!fwrite($fo, implode("\r", $SQL))) return false;
		fclose($fo);

		return true;
	}
	
	/**
	 * @see DbManager::getTableStructure()
	 */
	public function getTableStructure($table) {

		$structure = array("primary_key"=>null, "keys"=>array());
		$fields = array();

		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$this->_db_name."' AND TABLE_NAME = '$table'";
		$res = $this->queryResults($query);
		
		while($row = $this->fetch($res)) {
			
			preg_match("#(\w+)\((\'[0-9a-zA-Z-_,.']+\')\)#", $row['COLUMN_TYPE'], $matches_enum);
			preg_match("#(\w+)\((\d+),?(\d+)?\)#", $row['COLUMN_TYPE'], $matches);
			$fields[$row['COLUMN_NAME']] = array(
				"order"=>$row['ORDINAL_POSITION'],
				"default"=>$row['COLUMN_DEFAULT'],
				"null"=>$row['IS_NULLABLE'],
				"type"=>$row['DATA_TYPE'],
				"max_length"=>$row['CHARACTER_MAXIMUM_LENGTH'],
				"n_int"=>isset($matches[2]) ? $matches[2] : 0,
				"n_precision"=>isset($matches[3]) ? $matches[3] : 0,
				"key"=>$row['COLUMN_KEY'],
				"extra"=>$row['EXTRA'],
				"enum"=>isset($matches_enum[2]) ? $matches_enum[2] : null
			);
			if($row['COLUMN_KEY']=='PRI') $structure['primary_key'] = $row['COLUMN_NAME'];
			if($row['COLUMN_KEY']!='') $structure['keys'][] = $row['COLUMN_NAME'];
		}
		$structure['fields'] = $fields;
		
		return $structure;
	}

	/**
	 * @see DbManager::getFieldsName()
	 * @see freeresult()
	 */
	public function getFieldsName($table) {

		$fields = array();
		$query = "SHOW COLUMNS FROM ".$table;
		$res = $this->queryResults($query);
		
		while($row = $this->fetch($res)) {
			$results[] = $row;
		}
		$this->freeresult($res);

		foreach($results as $r) {
			$fields[] = $r['Field'];
		}

		return $fields;
	}

	/**
	 * @see DbManager::getNumRecords()
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
	 * @see DbManager::queryCache()
	 */
	public function queryCache($query, $options=array()) {
		
		return null;
	}
	
	/**
	 * @see DbManager::query()
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
		
		if(is_array($limit) && count($limit))
		{
			$qlimit = $this->limit($limit[1],$limit[0]);
		}
		elseif(is_string($limit))
		{
			$qlimit = $limit;
		}
		else $qlimit = '';
		
		$query = "SELECT $qfields FROM $qtables $qwhere $qgroup_by $qorder $qlimit";
		
		if($debug) echo $query;
		
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
	 * @see DbManager::select()
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
	 * @see DbManager::insert()
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
						$a_fields[] = "`$field`=".$value['sql']; //@TODO VERIFICARE
				}
				else
				{
					if($value !== null) {
						
						if($placeholder == false)
						{
							$a_fields[] = $field;
							$a_values[] = "'$value'";	//@TODO ///// VERIFICARE
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
			
			$s_fields = "`".implode('`,`', $a_fields)."`";
			
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
	 * @see DbManager::update()
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
						$a_fields[] = "`$field`=".$value['sql'];
				}
				else
				{
					if($placeholder == false)
					{
						//$a_fields[] = ($value == 'null') ? "`$field`=$value" : "`$field`='$value'";
						$a_fields[] = $value === null ? "`$field`=NULL" : "`$field`='$value'";
					}
					else
					{
						$param = ':'.$field;
						
						$a_fields[] = "`$field`=$param";
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
	 * @see DbManager::delete()
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
	 * @see DbManager::drop()
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
	 * @see DbManager::columnHasValue()
	 */
	public function columnHasValue($table, $field, $value, $options=array()) {
		
		$except_id = \Gino\gOpt('except_id', $options, null);
		
		$where = $field."='$value'";
		if($except_id) $where .= " AND id!='$except_id'";
		
		$rows = $this->select($field, $table, $where);
		return $rows and count($rows) ? true : false;
	}
	
	/**
	 * @see DbManager::join()
	 */
	public function join($table, $condition, $option) {
		
		$join = $table;
		if($condition) $join .= ' ON '.$condition;
		if($option) $join = strtoupper($option).' '.$join;
		
		return $join;
	}
	
	/**
	 * @see DbManager::union()
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
	 * @see DbManager::restore()
	 */
	public function restore($table, $filename, $options=array()) {      
	
		$fields = \Gino\gOpt('fields', $options, null);
		$delim = \Gino\gOpt('delim', $options, ',');
		$enclosed = \Gino\gOpt('enclosed', $options, '"');
		$escaped = \Gino\gOpt('escaped', $options, '\\');
		$lineend = \Gino\gOpt('lineend', $options, '\\r\\n');
		$hasheader = \Gino\gOpt('hasheader', $options, false);
		
		$ignore = $hasheader ? "IGNORE 1 LINES " : "";
		if($fields) $fields = "(".implode(',', $fields).")";
		
		$query = 
		"LOAD DATA INFILE '".$filename."' INTO TABLE ".$table." ".
		"FIELDS TERMINATED BY '".$delim."' ENCLOSED BY '".$enclosed."' ".
		"ESCAPED BY '".$escaped."' ".
		"LINES TERMINATED BY '".$lineend."' ".$ignore.$fields;
		
		$this->_result = $this->queryResults($query, array('statement'=>'action'));
		if($this->_result)
			return true;
		else
			return false;
	}
	
	/**
	 * @see DbManager::dump()
	 * 
	 * Per poter effettuare questa operazione occorre: \n
	 *   - assegnare il permesso FILE all'utente del database: GRANT FILE ON *.* TO 'dbuser'@'localhost';
	 *   - la directory di salvataggio deve avere i permessi read/write/execute, oppure deve avere come proprietario l'utente di sistema del database
	 */
	public function dump($table, $filename, $options=array()) {
		
		$delim = \Gino\gOpt('delim', $options, ',');
		$enclosed = \Gino\gOpt('enclosed', $options, '"');
		
		$query = "SELECT * INTO OUTFILE '".$filename."' 
		FIELDS TERMINATED BY '".$delim."' ENCLOSED BY '".$enclosed."' 
		FROM $table";
		
		$this->_result = $this->queryResults($query, array('statement'=>'action'));
		if($this->_result)
			return $filename;
		else
			return null;
	}
	
	/**
	 * @see DbManager::escapeString()
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
}

?>
