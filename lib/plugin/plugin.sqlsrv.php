<?php
/**
 * @file plugin.sqlsrv.php
 * @brief Contiene la classe sqlsrv
 * 
 * @copyright 2013-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\Plugin;

require_once(PLUGIN_DIR.OS."plugin.phpfastcache.php");

/**
 * @brief Libreria di connessione ai database SQL Server
 * 
 * @copyright 2013-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * GESTIONE CODIFICA UTF8
 * ---------------
 * In SQL Server occorre gestire la codifica UTF8 dei dati.
 * 
 * GESTIONE DATABASE/HTML
 * ---------------
 * ####Dal database alla visualizzazione
 * In questo caso si passa attraverso il metodo Gino.convertToHtml() richiamato dai metodi htmlChars, htmlCharsText e htmlInput, presenti nel file func.var.php.
 * 
 * ####Dal form al database
 * I dati passano attraverso il metodo Gino.convertToDatabase() (file func.var.php) richiamato direttamente dalle librerie di connessione al database.
 * 
 */
class sqlsrv implements \Gino\DbManager {

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
	 * Attiva le statistiche sulle query
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
		
		$this->setNumberRows(0);
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
	 * Imposta la query come proprietà
	 * 
	 * @param string $sql_query query
	 */
	private function setsql($sql_query) {
		$this->_sql = $sql_query;
	}

	private function setNumberRows($numberresults) {
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
	 * @return mixed
	 * 
	 * @see http://msdn.microsoft.com/en-us/library/hh487160.aspx
	 */
	private function queryResults($query, $options=array()) {
		
		if (!$this->_connection) {
			$this->openConnection();
		}
		
		$statement = \Gino\gOpt('statement', $options, 'select');
		
		if($this->_show_stats) {
			$this->_cnt++;
				
			$msc = \Gino\getmicrotime();
		}
		
		$res = sqlsrv_query($this->_dbconn, $query, array(), array('Scrollable'=>SQLSRV_CURSOR_KEYSET));
		
		//$this->_affected = sqlsrv_rows_affected($this->_qry);
		
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
	 * @see DbManager::openConnection()
	 * 
	 * L'estensione SQLSRV restituisce oggetti DateTime. \n
	 * È possibile disabilitare la funzione utilizzando l'opzione di connessione ReturnDatesAsStrings. \n
	 * @see https://msdn.microsoft.com/en-us/library/ff628167.aspx
	 */
	public function openConnection() {

		$connectionInfo = array(
			"Database"=>$this->_db_name, 
			"UID"=>$this->_db_user, 
			"PWD"=>$this->_db_password, 
			"ReturnDatesAsStrings"=>true, 
			"CharacterSet"=>"UTF-8"
		);
		
		if($this->_dbconn = sqlsrv_connect($this->_db_host, $connectionInfo)) {
			
			$this->setconnection(true);
			return true;
		} else {
			die("ERROR DB: verify connection parameters");	// debug -> die("ERROR SQLServer: ".var_dump(sqlsrv_errors()));
		}
	}

	/**
	 * @see DbManager::closeConnection()
	 */
	public function closeConnection() {

		if($this->_connection){
			sqlsrv_close($this->_dbconn);
		}
	}
	
	/**
	 * @see DbManager::begin()
	 */
	public function begin() {
		if (!$this->_connection){
			$this->openConnection();
		}
		$this->setsql("BEGIN");
		
		$this->_qry = $this->queryResults($this->_sql);
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
		$this->_qry = $this->queryResults($this->_sql);
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
		$this->_qry = $this->queryResults($this->_sql);
		if (!$this->qry) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * @see Gino.DbManager::multiActionQuery()
	 * @see Gino.SqlParse::getQueries()
	 */
	public function multiActionQuery($file_content) {
	
		$result = true;
		$queries = \Gino\SqlParse::getQueries(array('content_schema'=>$file_content));
		if(count($queries))
		{
			$debug = false;
				
			foreach($queries AS $query)
			{
				$res = $this->execCustomQuery($query, array('statement'=>'action'));
				if(!$res) $result = false;
	
				if($debug)
				{
					printf("%s\n", $query);
					printf("-----------------\n");
				}
			}
		}
	
		return $this->_result ? true : false;
	}
		
	/**
	 * @see DbManager::freeresult()
	 */
	public function freeresult($res=null) {
	
		if(is_null($res)) $res = $this->_qry;
		sqlsrv_free_stmt($res);
	}
	
	/**
	 * @see DbManager::getLastId()
	 */
	public function getLastId($table) {
		
		$id = 0;
		$res = $this->queryResults("SELECT IDENT_CURRENT('$table') AS id");	// SCOPE_IDENTITY()
    	if($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) { 
        	$id = $row["id"];
    	}
    	$this->_lastid = $id;
    	
		return $this->_lastid; 
	}
	
	/**
	 * Ottiene il valore del campo AUTO_INCREMENT
	 * 
	 * @see DbManager::autoIncValue()
	 */
	public function autoIncValue($table){

		$query = "SELECT IDENT_CURRENT('$table') AS NextId";
		$a = $this->select(null, null, null, array('custom_query'=>$query, 'cache'=>false));
		if($a && isset($a[0]))
		{
			$auto_increment = $a[0]['NextId'];
		}
		else $auto_increment = 0;
		
		$auto_increment++;
		
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
		
		$query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".DB_SCHEMA."' AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME='$table'";
		$res = $this->queryResults($query);
		if($res)
			return true;
		else
			return false;
	}
	
	/**
	 * @see DbManager::fieldInformations()
	 * @see conformFieldType()
	 * 
	 * La funzione sqlsrv_field_metadata() ritorna i riferimenti:
	 * <table>
	 * <tr><td>Name</td><td>The name of the field.</td></tr>
	 * <tr><td>Type</td><td>The numeric value for the SQL type.</td></tr>
	 * <tr><td>Size</td><td>The number of characters for fields of character type, the number of bytes for fields of binary type, or NULL for other types.</td></tr>
	 * <tr><td>Precision</td><td>The precision for types of variable precision, NULL for other types.</td></tr>
	 * <tr><td>Scale</td><td>The scale for types of variable scale, NULL for other types.</td></tr>
	 * <tr><td>Nullable</td><td>An enumeration indicating whether the column is nullable, not nullable, or if it is not known.</td></tr>
	 * </table>
	 */
	public function fieldInformations($table) {
	
		if($this->_connection) {
			$this->openConnection();
		}
		$query = "SELECT TOP 1 * FROM ".$table;
		$this->_qry = $this->queryResults($query);
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$meta = array();
			$field_metadata = sqlsrv_field_metadata($this->_qry);
			
			$i = 0;
			while($i < sqlsrv_num_fields($this->_qry)) {
				
				$meta_type = $field_metadata[$i]['Type'];
				$meta_size = $field_metadata[$i]['Size'];
				$meta_precision = $field_metadata[$i]['Precision'];
				
				if(is_null($meta_size))
					$size = $meta_precision;
				else 
					$size = $meta_size;
				
				$array_tmp = array(
					'name'=>$field_metadata[$i]['Name'],
					'type'=>$this->conformFieldType($meta_type),
					'length'=>$size
				);
				
				$meta[$i] = arrayToObject($array_tmp);
				$i++;
			}
			$this->freeresult();
			return $meta;
		}
	}
	
	/**
	 * Uniforma i tipi di dato dei campi
	 * 
	 * @param integer $type
	 * @return string
	 * 
	 * Tipi di dato riportati dalla funzione sqlsrv_field_metadata()
	 * 
	 * Per l'elenco fare riferimento alla documentazione ufficiale Microsoft: \n
	 * http://msdn.microsoft.com/en-us/library/cc296197.aspx
	 * 
	 * <table>
	 * <tr><td>bigint</td><td>-5</td></tr>
	 * <tr><td>binary</td><td>-2</td></tr>
	 * <tr><td>bit</td><td>-7</td></tr>
	 * <tr><td>char</td><td>1</td></tr>
	 * <tr><td>date</td><td>91</td></tr>
	 * <tr><td>datetime</td><td>93</td></tr>
	 * <tr><td>datetime2</td><td>93</td></tr>
	 * <tr><td>datetimeoffset</td><td>-155</td></tr>
	 * <tr><td>decimal</td><td>3</td></tr>
	 * <tr><td>float</td><td>6</td></tr>
	 * <tr><td>image</td><td>-4</td></tr>
	 * <tr><td>int</td><td>4</td></tr>
	 * <tr><td>money</td><td>3</td></tr>
	 * <tr><td>nchar</td><td>-8</td></tr>
	 * <tr><td>ntext</td><td>-10</td></tr>
	 * <tr><td>numeric</td><td>2</td></tr>
	 * <tr><td>nvarchar</td><td>-9</td></tr>
	 * <tr><td>real</td><td>7</td></tr>
	 * <tr><td>smalldatetime</td><td>93</td></tr>
	 * <tr><td>smallint</td><td>5</td></tr>
	 * <tr><td>Smallmoney</td><td>3</td></tr>
	 * <tr><td>text</td><td>-1</td></tr>
	 * <tr><td>time</td><td>-154</td></tr>
	 * <tr><td>timestamp</td><td>-2</td></tr>
	 * <tr><td>tinyint</td><td>-6</td></tr>
	 * <tr><td>udt</td><td>-151</td></tr>
	 * <tr><td>uniqueidentifier</td><td>-11</td></tr>
	 * <tr><td>varbinary</td><td>-3</td></tr>
	 * <tr><td>varchar</td><td>12</td></tr>
	 * <tr><td>xml</td><td>-152</td></tr>
	 * </table>
	 * 
	 */
	public function conformFieldType($type) {
		
		$data = array(
			'bigint' => -5, 
			'binary' => -2, 
			'bit' => -7, 
			'char' => 1, 
			'date' => 91, 
			'datetime' => 93, 
			'datetime2' => 93, 
			'datetimeoffset' => -155, 
			'decimal' => 3, 
			'float' => 6, 
			'image' => -4, 
			'int' => 4, 
			'money' => 3, 
			'nchar' => -8, 
			'ntext' => -10, 
			'numeric' => 2, 
			'nvarchar' => -9, 
			'real' => 7, 
			'smalldatetime' => 93, 
			'smallint' => 5, 
			'Smallmoney' => 3, 
			'text' => -1, 
			'time' => -154, 
			'timestamp' => -2, 
			'tinyint' => -6, 
			'udt' => -151, 
			'uniqueidentifier' => -11, 
			'varbinary' => -3, 
			'varchar' => 12, 
			'xml' => -152
		);
		
		if($type == 4 || $type == 5)
			$conform_type = 'int';
		elseif($type == -6)
			$conform_type = 'bool';
		elseif($type == -9 || $type == -8 || $type == 12 || $type == 1)
			$conform_type = 'char';
		else
			$conform_type = array_search($type, $data);
		
		return $conform_type;
	}
	
	/**
	 * @see Gino.DbManager::limit()
	 * 
	 * Examples
	 * @code
	 * //Returning the first 100 rows from a table called employee:
	 * select top 100 * from employee
	 * //Returning the top 20% of rows from a table called employee:
	 * select top 20 percent * from employee 
	 * @endcode
	 */
	public function limit($range, $offset=0){
		
		$limit = "TOP $range";
		
		return $limit;
	}
	
	/**
	 * @see DbManager::distinct()
	 */
	public function distinct($fields, $options=array()) {
		
		$alias = \Gino\gOpt('alias', $options, null);
		$remove_table = \Gino\gOpt('remove_table', $options, true);
		
		if(!$fields) return null;
		
		if($remove_table)
		{
			$a_fields = explode(',', $fields);
			$a_data = array();
			foreach($a_fields AS $field)
			{
				$a_value = explode('.', trim($field));
				$a_data[] = $a_value[count($a_value)-1];
			}
			$fields = implode(', ', $a_data);
		}
		
		$data = "DISTINCT ($fields)";
		if($alias) $data .= " AS $alias";
		
		return $data;
	}
	
	/**
	 * @see DbManager::concat()
	 */
	public function concat($sequence){
		
		if(is_array($sequence))
		{
			if(sizeof($sequence) > 1)
			{
				/*$sequence2 = array();
				foreach($sequence AS $value)
				{
					if(is_int($value))
					{
						$len = strlen($value);
						$value = "CAST($value AS VARCHAR($len))";
					}
					
					$sequence2[] = $value;
				}*/
				$concat = implode(' + ', $sequence);
			}
			else $concat = $sequence[0];
		}
		else $concat = $sequence;
		
		return $concat;
	}

	/**
	 * @see DbManager::dumpDatabase()
	 * @see listTables()
	 */
	public function dumpDatabase($file) {

		$tables = $this->listTables();
		
		while ($td = sqlsrv_fetch_array($tables)) {
			$table = $td[0];
			$r = $this->queryResults("SHOW CREATE TABLE $table");
			//SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$table' ORDER BY ORDINAL_POSITION
			if($r) {
				$insert_sql = "";
				$d = sqlsrv_fetch_array($r);
				$d[1] .= ";";
				$SQL[] = str_replace("\n", "", $d[1]);
				
				$table_query = $this->queryResults("SELECT * FROM $table");
				$num_fields = sqlsrv_num_fields($table_query);
				while ($fetch_row = sqlsrv_fetch_array($table_query)) {
					$insert_sql .= "INSERT INTO $table VALUES (";
					for ($n=1; $n<=$num_fields; $n++) {
						$m = $n - 1;
						$insert_sql .= "'".$this->escapeString($fetch_row[$m]).($n==$num_fields ? "" : "', ");
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
	
	private function listTables() {
		
		$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG='".$this->_db_name."' AND TABLE_TYPE='BASE_TABLE'";
		$res = $this->queryResults($query);
		
		return $res;
	}
	
	/**
	 * @see Gino.DbManager::changeFieldType()
	 */
	public function changeFieldType($data_type, $value) {
	
		return $value;
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
	 * @see DbManager::query()
	 * @see limitQuery()
	 */
	public function query($fields, $tables, $where=null, $options=array()) {

		$order = \Gino\gOpt('order', $options, null);
		$group_by = \Gino\gOpt('group_by', $options, null);
		$limit = \Gino\gOpt('limit', $options, null);
		$debug = \Gino\gOpt('debug', $options, false);
		$distinct = \Gino\gOpt('distinct', $options, null);
		
		$qfields = is_array($fields) ? implode(",", $fields) : $fields;
		$qtables = is_array($tables) ? implode(",", $tables) : $tables;
		$qwhere = $where ? "WHERE ".$where : "";
		$qgroup_by = $group_by ? "GROUP BY ".$group_by : "";
		$qorder = $order ? "ORDER BY $order" : "";
		
		if($distinct) $qfields = $distinct.", ".$qfields;
		
		if(is_array($limit) && count($limit))	// Paginazione
		{
			return $this->limitQuery($fields, $qtables, $where, $options);
		}
		
		if(is_string($limit))
			$top = $limit;
		else $top = '';
		
		$query = "SELECT $top $qfields FROM $qtables $qwhere $qgroup_by $qorder";
		
		if($debug) echo $query;
		
		return $query;
	}
	
	private function limitQuery($fields, $tables, $where=null, $options=array()) {
		
		$order = \Gino\gOpt('order', $options, null);
		$limit = \Gino\gOpt('limit', $options, null);
		$debug = \Gino\gOpt('debug', $options, false);
		$distinct = \Gino\gOpt('distinct', $options, null);
		
		$qtables = is_array($tables) ? implode(",", $tables) : $tables;
		$qwhere = $where ? "WHERE ".$where : "";
		
		$offset = $limit[0];
		$range = $limit[1];
		settype($offset, 'int');
		
		if(is_string($fields)) $fields = explode(',', $fields);
		
		$clean_fields = array();	// solo i nomi dei campi
		$func_fields = array();		// nomi dei campi comprensivi delle eventuali funzioni (ad es. distinct)
		
		foreach($fields AS $f)
		{
			$field = trim($f);
			preg_match("#^([a-zA-Z ]+)\(([a-zA-Z0-9_]+)\)$#", $field, $matches);
			if(isset($matches[2]) && $matches[2])
			{
				$clean_fields[] = $matches[2];
			}
			else
			{
				$clean_fields[] = $field;
			}
			
			$func_fields[] = $field;
		}
		
		if($order)
		{
			$order_field = array();
			$split_order_field = explode(',', $order);	// per gestire i casi tipo: name ASC, descr DESC
			
			foreach($split_order_field AS $s)
			{
				$a_order = array();
				$split_field = explode(' ', $s);
				foreach($split_field AS $f)
				{
					if(preg_match("#\.#", $f))	// ricerco la ricorrenza [nome_tabella].[nome_campo]
					{
						$a_field = explode('.', $f);
						$field_name = trim($a_field[1]);
						$a_order[] = $field_name;
						
						// verifico se il campo di ordinamento è presente nell'elenco dei campi del select
						// in caso negativo lo aggiungo all'elenco dei nomi comprensivi delle eventuali funzioni (subquery)
						if(!in_array($field_name, $clean_fields))
							$func_fields[] = $field_name;
					}
					else $a_order[] = trim($f);
				}
				$order_field[] = implode(' ', $a_order);
			}
			
			$clean_order = "ORDER BY ".implode(', ', $order_field);
			$qorder = "ORDER BY ".$order;
		}
		else
		{
			$clean_order = $qorder = "ORDER BY id";
		}
		
		$clean_fields = implode(', ', $clean_fields);
		$func_fields = implode(', ', $func_fields);
		
		$query = "SELECT $clean_fields FROM ( 
			SELECT $func_fields, row_number () over ($qorder) - 1 as rn
			FROM $qtables $qwhere) rn_subquery 
		WHERE rn between $offset and ($offset+$range)-1 $clean_order";
		
		if($debug) echo $query;
		
		return $query;
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
			return $this->queryResults($query, array('statement'=>$statement));
		}
	}

	/**
	 * @see DbManager::select()
	 */
	public function select($fields, $tables, $where=null, $options=array()) {

		$custom_query = \Gino\gOpt('custom_query', $options, null);
		$cache = \Gino\gOpt('cache', $options, true);
		$identity_keyword = \Gino\gOpt('identity_keyword', $options, null);
		$time_caching = \Gino\gOpt('time_caching', $options, null);
		
		if($custom_query)
		{
			$query = $custom_query;
		}
		else
		{
			$query = $this->query($fields, $tables, $where, $options);
		}
		
		if(!$identity_keyword) $identity_keyword = $query;
		
		if(!($this->_query_cache && $cache) OR is_null($results = $this->_cache->get($identity_keyword)))
		{
			$this->_qry = $this->queryResults($query);
			if(!$this->_qry)
			{
				return false;
			}
			else
			{
				// initialize array results
				$results = array();
				
				$this->setNumberRows(sqlsrv_num_rows($this->_qry));
				if($this->_numberrows > 0) {
					while($rows = sqlsrv_fetch_array($this->_qry, SQLSRV_FETCH_ASSOC))
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

		if(is_array($fields) && count($fields) && $table)
		{
			$a_fields = array();
			$a_values = array();
			
			foreach($fields AS $field=>$value)
			{
				$a_fields[] = $field;
				
				if(is_array($value))
				{
					if(array_key_exists('sql', $value))
					{
						$mb_value = \Gino\convertToDatabase($value['sql'], 'CP1252');
						$a_fields[] = "[$field]=".$mb_value;
					}
				}
				else
				{
					$mb_value = \Gino\convertToDatabase($value, 'CP1252');
					$a_values[] = ($value !== null) ? "'$mb_value'" : 'null';	// verificare se crea problemi
				}
			}
			
			$s_fields = "[".implode('],[', $a_fields)."]";
			$s_values = implode(",", $a_values);
			
			$query = "INSERT INTO $table ($s_fields) VALUES ($s_values)";
			
			if($debug) echo $query;
			
			return $this->queryResults($query, array('statement'=>'action'));
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
					{
						$mb_value = \Gino\convertToDatabase($value['sql'], 'CP1252');
						$a_fields[] = "[$field]=".$mb_value;
					}
				}
				else
				{
					$mb_value = \Gino\convertToDatabase($value, 'CP1252');
					$a_fields[] = "[$field]='$mb_value'";	//$a_fields[] = ($value == 'null') ? "[$field]=$value" : "[$field]='$mb_value'";
				}
			}
			
			$s_fields = implode(",", $a_fields);
			$s_where = $where ? " WHERE ".$where : "";
			
			$query = "UPDATE $table SET $s_fields".$s_where;
			
			if($debug) echo $query;
			
			return $this->queryResults($query, array('statement'=>'action'));
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
		
		return $this->queryResults($query, array('statement'=>'action'));
	}

	/**
	 * @see DbManager::drop()
	 */
	public function drop($table) {

		if(!$table) return false;
		
		$query = "DROP $table";
		
		return $this->queryResults($query, array('statement'=>'action'));
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
	 * @see DbManager::restore()
	 */
	public function restore($table, $filename, $options=array()) {      
	
		$fields = \Gino\gOpt('fields', $options, null);
		$delim = \Gino\gOpt('delim', $options, ',');
		$enclosed = \Gino\gOpt('enclosed', $options, '"');
		$escaped = \Gino\gOpt('escaped', $options, '\\');
		$lineend = \Gino\gOpt('lineend', $options, '\\r\\n');
		$hasheader = \Gino\gOpt('hasheader', $options, false);
		
		if($fields) $fields = "(".implode(',', $fields).")";
		
		$query = "BULK INSERT ".$table." FROM '".$filename."' 
		WITH (
			FIELDTERMINATOR = '".$delim."',
			ROWTERMINATOR = '".$lineend."'
		)";
		return $this->queryResults($query, array('statement'=>'action'));
	}
	
	/**
	 * @see DbManager::dump()
	 */
	public function dump($table, $filename, $options=array()) {
		
		$where = \Gino\gOpt('where', $options, null);
		$delim = \Gino\gOpt('delim', $options, ',');
		$enclosed = \Gino\gOpt('enclosed', $options, null);
		
		$where = $where ? " WHERE $where" : '';
		$enclosed = $enclosed ? "ENCLOSED BY '".$enclosed."' " : '';
		
		$query = "SELECT * INTO OUTFILE '".$filename."' 
		FIELDS TERMINATED BY '".$delim."' 
		$enclosed
		LINES TERMINATED BY '\r\n' 
		FROM $table".$where;
		$res = $this->queryResults($query, array('statement'=>'action'));
		
		if($res)
			return $filename;
		else
			return null;
	}
	
	/**
	 * @see DbManager::escapeString()
	 */
	public function escapeString($string) {
		
		$string = str_replace("'", "''", $string);
		$string = str_replace("\0", "[NULL]", $string);
		return $string;
	}
}

?>
