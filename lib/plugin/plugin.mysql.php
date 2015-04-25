<?php
/**
 * @file plugin.mysql.php
 * @brief Contiene la classe mysql
 * 
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * @brief Libreria di connessione ai database MySQL
 * 
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * La cache delle query viene svuotata ogni volta che viene richiamato il metodo actionquery().
 * 
 * INFORMAZIONI SULLE QUERY
 * ---------------
 * La proprietà self::$_show_stats attiva la raccolta di informazioni sulle prestazioni delle chiamate al database. \n
 * Le query di tipo select alimentano i contatori self::$_cnt e self::$_time_queries.
 */
class mysql implements \Gino\DbManager {

	private $_db_host, $_db_name, $_db_user, $_db_password, $_db_charset, $_dbconn;
	private $_sql;
	private $_qry;	// results of query
	private $_numberrows;
	private $_connection;
	private $_rows;
	private $_affected;
	private $_lastid;
	private $_dbresults = array();
	
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
	 *   - @b host (string): nome del server
	 *   - @b db_name (string): nome del database
	 *   - @b user (string): utente che si connette
	 *   - @b password (string): password dell'utente che si connette
	 *   - @b charset (string): encoding
	 *   - @b connect (boolean): attiva la connessione
	 * @return void
	 */
	function __construct($params) {
		
		$this->_db_host = $params["host"];
		$this->_db_name = $params["db_name"];
		$this->_db_user = $params["user"];
		$this->_db_password = $params["password"];
		$this->_db_charset = $params["charset"];
		
		$this->_range = null;
		$this->_offset = null;
		
		$this->setnumberrows(0);
		$this->setconnection(false);
		
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
			$this->_query_cache = $item[0]['query_cache'];
			$this->_query_cache_time = $item[0]['query_cache_time'];
		}
		else
		{
			$this->_query_cache = false;
			$this->_query_cache_time = null;
		}
	}
	
	/**
	 * Imposta la query come proprietà
	 * 
	 * @param string $sql_query query
	 */
	private function setsql($sql_query) {
		$this->_sql = $sql_query;
	}

	private function setnumberrows($numberresults) {
		$this->_numberrows = $numberresults;
	}
	
	private function setconnection($connection) {
		$this->_connection = $connection;
	}
	
	/**
	 * @see DbManager::getInfoQuery()
	 */
	public function getInfoQuery() {
 		
 		if($this->_show_stats) {
 			$buffer = "<p>Number of db queries: ".$this->_cnt."</p>";
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
	 * Esegue la query
	 * 
	 * @param string $query
	 * @return array
	 */
	private function execQuery($query=null) {
		
		if(!$query) $query = $this->_sql;
		
		if($this->_show_stats) {
			$this->_cnt++;
			
			$msc = \Gino\getmicrotime();
			$exec = mysql_query($query);
			$msc = \Gino\getmicrotime()-$msc;
			$this->_time_queries += $msc;
			
			$this->_info_queries[] = array($query, $msc);
		}
		else {
			$exec = mysql_query($query);
		}
		return $exec;
 	}
 	
 	/**
	 * @see DbManager::openConnection()
	 */
	public function openConnection() {

		if($this->_dbconn = mysql_connect($this->_db_host, $this->_db_user, $this->_db_password)) {
			
			@mysql_select_db($this->_db_name, $this->_dbconn) OR die("ERROR DB: ".mysql_error());
			if($this->_db_charset=='utf-8') $this->setUtf8();
			$this->setconnection(TRUE);
			return TRUE;
		} else {
            throw new \Exception(_('Errore di connessione al database'));
		}
	}

	private function setUtf8() {
		
		$query = "SHOW VARIABLES LIKE 'character_set_database'";
		$db_charset = $this->execQuery($query);
		$charset_row = mysql_fetch_assoc($db_charset);
		
		$this->execQuery("SET NAMES '".$charset_row['Value']."'");
		
		unset($db_charset, $charset_row);
	}

	/**
	 * Chiude le connessioni non persistenti a un server MySQL
	 * 
	 * @see DbManager::closeConnection()
	 */
	public function closeConnection() {

		if($this->_connection){
			mysql_close($this->_dbconn);
		}
	}
	
	/**
	 * Per tabelle innodb
	 * 
	 * @see DbManager::begin()
	 */
	public function begin() {
		if (!$this->_connection){
			$this->openConnection();
		}
		$this->setsql("BEGIN");
		$this->_qry = $this->execQuery();
		if (!$this->_qry) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Per tabelle innodb
	 * 
	 * @see DbManager::rollback()
	 */
	public function rollback() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql("ROLLBACK");
		$this->_qry = $this->execQuery();
		if (!$this->_qry) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Per tabelle innodb
	 * 
	 * @see DbManager::commit()
	 */
	public function commit() {
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql("COMMIT");
		$this->_qry = $this->execQuery();
		if (!$this->qry) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * @see DbManager::actionquery()
	 */
	public function actionquery($qry) {
		
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);
		$this->_qry = $this->execQuery();

		if($this->_qry)
		{
			if($this->_query_cache)
			{
				$this->_cache->clean();
			}
			return true;
		}
		else return false;
	}

	/**
	 * @see DbManager::multiActionquery()
	 */
	public function multiActionquery($qry) {
	
		$conn = mysqli_connect($this->_db_host, $this->_db_user, $this->_db_password, $this->_db_name);
		$this->setsql($qry);
		$this->_qry = mysqli_multi_query($conn, $this->_sql);

		return $this->_qry ? true:false;
	}

	/**
	 * @see DbManager::selectquery()
	 */
	public function selectquery($qry) {

		if(!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);

		$this->_qry = $this->execQuery();
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$this->_dbresults = array();
			
			$this->setnumberrows(mysql_num_rows($this->_qry));
			if($this->_numberrows > 0){
				while($this->_rows=mysql_fetch_assoc($this->_qry))
				{
					$this->_dbresults[]=$this->_rows;
				}
			}
			$this->freeresult();
			
			return $this->_dbresults;
		}
	}
	
	/**
	 * @see DbManager::freeresult()
	 */
	public function freeresult($res=null){
	
		if(is_null($res)) $res = $this->_qry;
		mysql_free_result($res);
	}
	
	/**
	 * @see DbManager::resultselect()
	 */
	public function resultselect($qry)
	{
		if(!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);
		$this->_qry = $this->execQuery();
		if (!$this->_qry) {
			return false;
		} else {
			$this->setnumberrows(mysql_num_rows($this->_qry));
			return $this->_numberrows;
		}
	}
	
	/**
	 * @see DbManager::affected()
	 */
	public function affected() 
	{ 
		$this->_affected = mysql_affected_rows();
		return $this->_affected;
	}
	
	/**
	 * Il valore della funzione SQL LAST_INSERT_ID() di MySQL contiene sempre il più recente valore AUTO_INCREMENT generato e non è azzerato dalle query
	 * 
	 * @see DbManager::getlastid()
	 */
	public function getlastid($table)
	{ 
		if($this->affected() > 0)
		{
			$this->_lastid = mysql_insert_id();
		}
		else
		{
			$this->_lastid = false;
		}
		return $this->_lastid; 
	}
	
	/**
	 * Ottiene il valore del campo AUTO_INCREMENT
	 * 
	 * @see DbManager::autoIncValue()
	 */
	public function autoIncValue($table){

		$query = "SHOW TABLE STATUS LIKE '$table'";
		$a = $this->selectquery($query, false);
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
		$result = $this->execQuery($query);
		$data = mysql_num_rows($result);

		for ($i=0; $i<$data; $i++) {
			if(mysql_tablename($result, $i) == $table) return true;
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
		$this->setsql("SELECT * FROM ".$table." LIMIT 0,1");
		$this->_qry = $this->execQuery();
		
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$meta = array();
			$i = 0;
			while($i < mysql_num_fields($this->_qry)) {
				$meta[$i] = mysql_fetch_field($this->_qry, $i);
				$meta[$i]->length = mysql_field_len($this->_qry, $i);
				
				$meta[$i]->type = $this->conformType($meta[$i]->type);
				$i++;
			}
			$this->freeresult();
			return $meta;
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
		$tables = $this->execQuery($query);
		
		while ($td = mysql_fetch_array($tables)) {
			$table = $td[0];
			$r = mysql_query("SHOW CREATE TABLE `$table`");
			if ($r) {
				$insert_sql = "";
				$d = mysql_fetch_array($r);
				$d[1] .= ";";
				$SQL[] = str_replace("\n", "", $d[1]);
				$table_query = mysql_query("SELECT * FROM `$table`");
				$num_fields = mysql_num_fields($table_query);
				while ($fetch_row = mysql_fetch_array($table_query)) {
					$insert_sql .= "INSERT INTO $table VALUES(";
					for ($n=1;$n<=$num_fields;$n++) {
						$m = $n - 1;
						$insert_sql .= "'".mysql_real_escape_string($fetch_row[$m]).($n==$num_fields ? "" : "', ");
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
		$res = $this->execQuery($query);
		while($row = mysql_fetch_array($res)) {
			
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
		$res = $this->execQuery($query);
		while($row = mysql_fetch_assoc($res)) {
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
		
		$identity_keyword = \Gino\gOpt('identity_keyword', $options, null);
		$time_caching = \Gino\gOpt('time_caching', $options, null);
		
		if(!$identity_keyword) $identity_keyword = $query;
		
		$results = $this->_cache->get($identity_keyword);
		if($results == null) {
			
			$results = $this->selectquery($query);
			$this->_cache->set($results, array('time_caching'=>$time_caching));
		}
		return $results;
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
		$qwhere = $where ? "WHERE ".$where : "";
		$qgroup_by = $group_by ? "GROUP BY ".$group_by : "";
		$qorder = $order ? "ORDER BY $order" : "";
		
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
	 * @see DbManager::select()
	 */
	public function select($fields, $tables, $where=null, $options=array()) {
		
		$cache = \Gino\gOpt('cache', $options, true);
		
		$query = $this->query($fields, $tables, $where, $options);
		
		if($this->_query_cache && $cache)
		{
			$results = $this->queryCache($query, $options);
		}
		else
		{
			$results = $this->selectquery($query);
		}
		
		return $results;
	}
	
	/**
	 * @see DbManager::insert()
	 */
	public function insert($fields, $table, $debug=false) {

		if(is_array($fields) && count($fields) && $table)
		{
			$a_fields = array();
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
						  $a_fields[] = $field;
						  $a_values[] = "'$value'";	//@TODO ///// VERIFICARE
          			}
				}
			}
			
			$s_fields = "`".implode('`,`', $a_fields)."`";
			$s_values = implode(",", $a_values);
			
			$query = "INSERT INTO $table ($s_fields) VALUES ($s_values)";
			
			if($debug) echo $query;
			
			return $this->actionquery($query);
		}
		else return false;
	}
	
	/**
	 * @see DbManager::update()
	 */
	public function update($fields, $table, $where, $debug=false) {

		if(is_array($fields) && count($fields) && $table)
		{
			$a_fields = array();
			
			foreach($fields AS $field=>$value)
			{
				if(is_array($value))
				{
					if(array_key_exists('sql', $value))
						$a_fields[] = "`$field`=".$value['sql'];
				}
				else
				{
					//$a_fields[] = ($value == 'null') ? "`$field`=$value" : "`$field`='$value'";
					$a_fields[] = $value === null ? "`$field`=NULL" : "`$field`='$value'";
				}
			}
			
			$s_fields = implode(",", $a_fields);
			$s_where = $where ? " WHERE ".$where : "";
			
			$query = "UPDATE $table SET $s_fields".$s_where;
			
			if($debug) echo $query;
			
			return $this->actionquery($query);
		}
		else return false;
	}
	
	/**
	 * @see DbManager::delete()
	 */
	public function delete($table, $where, $debug=false) {

		if(!$table) return false;
		
		$s_where = $where ? " WHERE ".$where : '';
		
		$query = "DELETE FROM $table".$s_where;
		
		if($debug) echo $query;
		
		return $this->actionquery($query);
	}

	/**
	 * @see DbManager::drop()
	 */
	public function drop($table) {

		if(!$table) return false;
		
		$query = "DROP TABLE $table";
		
		return $this->actionquery($query);
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
		$instruction = \Gino\gOpt('instruction', $options, 'UNION');
		
		if(count($queries))
		{
			$query = implode(" $instruction ", $queries);
			
			if($debug) echo $query;
			
			return $this->selectquery($query);
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
		return $this->actionquery($query);
	}
	
	/**
	 * @see DbManager::dump()
	 * 
	 * Per poter effettuare questa operazione occorre: \n
	 *   - assegnare il permesso FILE all'utente del database: GRANT FILE ON *.* TO 'dbuser'@'localhost';
	 *   - la directory di salvataggio deve avere i permessi 777, oppure deve avere come proprietario l'utente di sistema mysql (gruppo mysql)
	 */
	public function dump($table, $filename, $options=array()) {
		
		$delim = \Gino\gOpt('delim', $options, ',');
		$enclosed = \Gino\gOpt('enclosed', $options, '"');
		
		$query = "SELECT * INTO OUTFILE '".$filename."' 
		FIELDS TERMINATED BY '".$delim."' ENCLOSED BY '".$enclosed."' 
		FROM $table";
		if($this->actionquery($query))
			return $filename;
		else
			return null;
	}
	
	/**
	 * @see DbManager::escapeString()
	 */
	public function escapeString($string) {
		
		return mysql_real_escape_string($string);
	}
}

?>
