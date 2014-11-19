<?php
/**
 * @file plugin.odbc.php
 * @brief Contiene la classe odbc
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\Plugin;

/**
 * @brief Libreria di connessione ai database SQL Server
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Nel file configuration.php definire come valore della costante DB_HOST il nome del dsn.
 */
class odbc implements \Gino\DbManager {

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
	 * Abilita la cache 
	 * 
	 * @var boolean
	 */
	private $_enable_cache;
	
	/**
	 * Abilita il debug sulle query
	 * 
	 * @var boolean
	 */
	private $_debug;
	
	/**
	 * Contatore di query
	 * 
	 * @var integer
	 */
	private $_cnt;
	
	/**
	 * Contenitore delle query di tipo select
	 * 
	 * @var array(query=>results)
	 */
	private $_cache;
	
	/**
	 * Costruttore
	 * 
	 * @param array $params parametri di connessione al database
	 *   - @b host (string): nome del dsn 
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
		
		$this->setnumberrows(0);
		$this->setconnection(false);
		
		$this->_enable_cache = false;
		$this->_debug = false;
		$this->_cnt = 0;
		$this->_cache = array();
		
		if($params["connect"]===true) $this->openConnection();
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
 		
 		if($this->_debug)
 			return $this->_cnt;
 		else
 			return null;
	}
	
	/**
	 * Esegue la query
	 * 
	 * @param string $query
	 * @return array
	 */
	private function execQuery($query=null) {
		
		if(!$query) $query = $this->_sql;
		
		if($this->_debug) $this->_cnt++;
		
		$exec = odbc_exec($this->_dbconn, $query);
		return $exec;
	}
	
	/**
	 * @see DbManager::openConnection()
	 */
	public function openConnection() {

		if($this->_dbconn = odbc_connect(
			$this->_db_host, 
			$this->_db_user, 
			$this->_db_password, 
			SQL_CUR_USE_ODBC)
		) {	
			$this->setconnection(true);
			return true;
		} else {
			die("ERROR DB: verify connection parameters");	// debug -> die("ERROR SQLServer: ".odbc_error());
		}
	}

	/**
	 * @see DbManager::closeConnection()
	 */
	public function closeConnection() {

		if($this->_connection){
			odbc_close($this->_dbconn);
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

		return $this->_qry ? true:false;
	}

	/**
	 * @see DbManager::multiActionquery()
	 */
	public function multiActionquery($qry) {
	
		/*$conn = mysqli_connect($this->_db_host, $this->_db_user, $this->_db_password, $this->_db_name);
		$this->setsql($qry);
		$this->_qry = mysqli_multi_query($conn, $this->_sql);

		return $this->_qry ? true:false;*/
		return false;
	}

	/**
	 * @see DbManager::selectquery()
	 */
	public function selectquery($qry, $cache=true) {

		if(!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);
		
		if($this->_enable_cache and $cache and isset($this->_cache[$this->_sql])) {
			return $this->_cache[$this->_sql];
		}
		else
		{
			$this->_qry = $this->execQuery();
			if(!$this->_qry) {
				return false;
			} else {
				// initialize array results
				$this->_dbresults = array();
				
				$this->setnumberrows(odbc_num_rows($this->_qry));
				if($this->_numberrows > 0){
					while($this->_rows=odbc_fetch_array($this->_qry))
					{
						$this->_dbresults[]=$this->_rows;
					}
				}
				//$this->freeresult();
				
				if($this->_enable_cache and $cache) {
					$this->_cache[$this->_sql] = $this->_dbresults;
				}
				return $this->_dbresults;
			}
		}
	}
		
	/**
	 * @see DbManager::freeresult()
	 */
	public function freeresult($res=null){
	
		if(is_null($res)) $res = $this->_qry;
		odbc_free_result($res);
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
			$this->setnumberrows(odbc_num_rows($this->_qry));
			return $this->_numberrows;
		}
	}
	
	/**
	 * @see DbManager::affected()
	 */
	public function affected() 
	{ 
		$this->_affected = odbc_num_rows($this->_qry);
		
		return $this->_affected;
	}
	
	/**
	 * @see DbManager::getlastid()
	 */
	public function getlastid($table)
	{ 
		if($this->affected() > 0)
		{
			$id = 0;
    		$res = $this->execQuery("SELECT SCOPE_IDENTITY() AS id"); 
    		if($row = odbc_fetch_array($res)) { 
        		$id = $row["id"];
    		} 
    		$this->_lastid = $id;
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

		$res = $this->execQuery("SELECT IDENT_CURRENT('$table') AS NextId");
		$a = $this->selectquery($query, false);
		if($a && isset($a[0]))
		{
			$auto_increment = $a[0]['NextId'];
		}
		else $auto_increment = 0;
		
		return $auto_increment;
	}
	
	/**
	 * @see DbManager::getFieldFromId()
	 */
	public function getFieldFromId($table, $field, $field_id, $id) {
		
		$query = "SELECT $field FROM $table WHERE $field_id='$id'";
		$a = $this->selectquery($query);
		if(!$a){
			return '';
		}
		else
		{
			foreach($a as $b) {
				return $b[$field];
			}
		}
	}
	
	/**
	 * @see DbManager::tableexists()
	 */
	public function tableexists($table){
		
		$query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".DB_SCHEMA."' AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME='$table'";
		$a = $this->selectquery($query);
		if($a)
			return true;
		else
			return false;
	}
	
	/**
	 * @see DbManager::fieldInformations()
	 */
	public function fieldInformations($table) {
	
		if($this->_connection) {
			$this->openConnection();
		}
		$this->setsql("SELECT TOP 1 * FROM ".$table);
		$this->_qry = $this->execQuery();
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$meta = array();
			$i = 0;
			while($i < odbc_num_fields($this->_qry)) {
				$meta[$i] = odbc_fetch_object($this->_qry, $i);
				$meta[$i]->length = odbc_field_len($this->_qry, $i);
				
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
	 * Come tipo di dato di un campo, la funzione odbc_fetch_object() ritorna: ...
	 */
	public function conformType($type) {
		
		
	}
	
	/**
	 * @see DbManager::limit()
	 */
	public function limit($range, $offset){
		
		// SELECT TOP 20 * ...
		// BETWEEN @offset+1 AND @offset+@count;
		
		//$limit = "BETWEEN $offset AND $range";
		
		$limit = "TOP $range";
		
		return $limit;
	}
	
	/**
	 * @see DbManager::distinct()
	 */
	public function distinct($fields, $options=array()) {
		
		$alias = gOpt('alias', $options, null);
		$remove_table = gOpt('remove_table', $options, true);
		
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
		
		$data = "DISTINCT $fields";
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
	 */
	public function dumpDatabase($file) {

		$tables = $this->listTables();
		
		while ($td = odbc_fetch_array($tables)) {
			$table = $td[0];
			$r = $this->execQuery("SHOW CREATE TABLE `$table`");
			if ($r) {
				$insert_sql = "";
				$d = odbc_fetch_array($r);
				$d[1] .= ";";
				$SQL[] = str_replace("\n", "", $d[1]);
				$table_query = $this->execQuery("SELECT * FROM `$table`");
				$num_fields = odbc_num_fields($table_query);
				while ($fetch_row = odbc_fetch_array($table_query)) {
					$insert_sql .= "INSERT INTO $table VALUES(";
					for ($n=1;$n<=$num_fields;$n++) {
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
		$res = $this->execQuery($query);
		
		return $res;
	}
	
	/**
	 * @see DbManager::getTableStructure()
	 */
	public function getTableStructure($table) {

		$structure = array("primary_key"=>null, "keys"=>array());
		$fields = array();

		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_CATALOG='".$this->_db_name."' AND TABLE_NAME='$table'";
		$res = $this->execQuery($query);

		while($row = odbc_fetch_array($res)) {
			
			$column_name = $row['COLUMN_NAME'];
			
			$constraint_type = $this->getConstraintType($column_name, $table);
			$key = !is_null($constraint_type['key']) ? $constraint_type['key'] : '';
			
			$data_type = $row['DATA_TYPE'];
			if(($data_type == 'varchar' || $data_type == 'nvarchar') && $row['CHARACTER_MAXIMUM_LENGTH'] == '-1')
				$data_type = 'text';
			elseif($data_type == 'nchar' || $data_type == 'nvarchar' || $data_type == 'varchar')
				$data_type = 'char';
			
			//$extra = $column_name == 'id' ? 'auto_increment' : null;
			
			if($column_name == 'id' or (preg_match("#^[a-zA-Z0-9]+(_id)$#", $column_name) && $row['ORDINAL_POSITION'] == 1))
				$extra = 'auto_increment';
			else
				$extra = null;
			
			$enum = $this->getCheckConstraint($column_name, $table);
			
			$fields[$column_name] = array(
				"order"=>$row['ORDINAL_POSITION'],
				"default"=>$row['COLUMN_DEFAULT'],
				"null"=>$row['IS_NULLABLE'],
				"type"=>$data_type,
				"max_length"=>$row['CHARACTER_MAXIMUM_LENGTH'],
				
				//"n_int"=>isset($matches[2]) ? $matches[2] : 0,
				//"n_precision"=>isset($matches[3]) ? $matches[3] : 0,
				
				"n_int"=>$row['NUMERIC_PRECISION'],
				"n_precision"=>$row['NUMERIC_PRECISION_RADIX'],		// VERIFICARE FLOAT
				
				"key"=>$key,
				"extra"=>$extra,
				"enum"=>$enum
			);
			
			$primary = $this->getInformationKey($column_name, $table);
			
			if($primary) $structure['primary_key'] = $primary;
			if($key) $structure['keys'][] = $key;
		}
		$structure['fields'] = $fields;

		return $structure;
	}
	
	/**
	 * Verifica se una colonna è una chiave
	 * 
	 * @param string $column
	 * @param string $table
	 * @return array(key, name)
	 * 
	 * Recupera il nome della chiave: \n
	 *   - @a PRIMARY KEY
	 *   - @a UNIQUE
	 *   - @a FOREIGN KEY
	 * e il suo nome (ad es. PK__page_ent__3213E83FAC2C67F3, UQ__page_ent__32DD1E4C35F37AB2)
	 */
	private function getConstraintType($column, $table) {
		
		$query = "
		SELECT C.CONSTRAINT_TYPE, K.CONSTRAINT_NAME
		FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS C
		JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS K
		ON C.TABLE_NAME = K.TABLE_NAME
		AND K.TABLE_NAME = '$table' 
		AND K.COLUMN_NAME = '$column' 
		AND C.CONSTRAINT_CATALOG = K.CONSTRAINT_CATALOG
		AND C.CONSTRAINT_SCHEMA = K.CONSTRAINT_SCHEMA
		AND C.CONSTRAINT_NAME = K.CONSTRAINT_NAME";
		$a = $this->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				return array('key'=>$b['CONSTRAINT_TYPE'], 'name'=>$b['CONSTRAINT_NAME']);
			}
		}
		else return array('key'=>null, 'name'=>null);
	}
	
	/**
	 * Verifica se una colonna ha un vincolo CHECK
	 * 
	 * @param string $column
	 * @param string $table
	 * @return string
	 */
	private function getCheckConstraint($column, $table) {
		
		$check_name = 'CK_'.$table.'_'.$column;
		
		$query = "SELECT CHECK_CLAUSE FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS WHERE CONSTRAINT_NAME='$check_name'";
		$a = $this->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$check_clause = $b['CHECK_CLAUSE'];
				
				return $check_clause;
			}
		}
		else return null;
	}
	
	/**
	 * Verifica se un campo è una particolare chiave e, in caso positivo, ne recupera il nome
	 * 
	 * @param string $column
	 * @param string $table
	 * @param string $key nome della chiave da ricercare
	 *   - @a PRI, primary key
	 *   - @a UNI, unique key
	 *   - @a FOR, foreign key
	 * @return mixed
	 */
	private function getInformationKey($column, $table, $key=null) {
		
		if($key == 'FOR')
			$key_name = 'FOREIGN KEY';
		elseif($key == 'UNI')
			$key_name = 'UNIQUE';
		else
			$key_name = 'PRIMARY KEY';
		
		$query = "
			SELECT K.CONSTRAINT_NAME
			FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS C
			JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS K
			ON C.TABLE_NAME = K.TABLE_NAME
			AND C.CONSTRAINT_CATALOG = K.CONSTRAINT_CATALOG
			AND C.CONSTRAINT_SCHEMA = K.CONSTRAINT_SCHEMA
			AND C.CONSTRAINT_NAME = K.CONSTRAINT_NAME
			WHERE C.CONSTRAINT_TYPE = '$key_name'
			AND K.COLUMN_NAME = '$column'
			AND K.TABLE_NAME = '$table'";
		$a = $this->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$name = $b['CONSTRAINT_NAME'];
			}
		}
		else $name = null;
		
		return $name;
	}

	/**
	 * @see DbManager::getFieldsName()
	 * @see freeresult()
	 */
	public function getFieldsName($table) {

		$fields = array();
		
		$query = "SELECT COLUMN_NAME AS Field
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_SCHEMA = '".DB_SCHEMA."'
		AND TABLE_NAME = '$table'";
		
		$res = $this->execQuery($query);
		while($row = odbc_fetch_array($res)) {
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
	public function getNumRecords($table, $where=null, $field='id') {

		$tot = 0;

		$qwhere = $where ? "WHERE ".$where : "";
		$query = "SELECT COUNT($field) AS tot FROM $table $qwhere";
		$res = $this->selectquery($query);
		if($res) {
			$tot = $res[0]['tot'];
		}

		return (int) $tot;
	}
	
	/**
	 * @see DbManager::query()
	 */
	public function query($fields, $tables, $where=null, $options=array()) {

		$order = gOpt('order', $options, null);
		$limit = gOpt('limit', $options, null);
		$debug = gOpt('debug', $options, false);
		
		$qfields = is_array($fields) ? implode(",", $fields) : $fields;
		$qtables = is_array($tables) ? implode(",", $tables) : $tables;
		$qwhere = $where ? "WHERE ".$where : "";
		$qorder = $order ? "ORDER BY $order" : "";
		
		if(is_array($limit) && count($limit))
		{
			$offset = $limit[0];
			$range = $limit[1];
			
			if(int($offset) > 0)
			{
				$query = "SELECT $qfields FROM (
				SELECT row_number() OVER (ORDER BY id) AS rownum, $qfields FROM $qtables $qwhere $qorder
				) AS A
				WHERE A.rownum
				BETWEEN ($offset) AND ($offset + $range)";
				
				if($debug) echo $query;
				
				return $query;
			}
			else
			{
				$top = $range;
			}
		}
		elseif(is_string($limit))
		{
			$top = $limit;
		}
		else $top = '';
		
		$query = "SELECT $top $qfields FROM $qtables $qwhere $qorder";
		
		if($debug) echo $query;
		
		return $query;
	}

	/**
	 * @see DbManager::select()
	 */
	public function select($fields, $tables, $where=null, $options=array()) {

		$query = $this->query($fields, $tables, $where, $options);
		
		return $this->selectquery($query);
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
						$a_fields[] = "[$field]=".$value['sql'];
				}
				else
				{
					$a_values[] = ($value !== null) ? "'$value'" : null;	/////// VERIFICARE
				}
			}
			
			$s_fields = "[".implode('],[', $a_fields)."]";
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
						$a_fields[] = "[$field]=".$value['sql'];
				}
				else
				{
					//$a_fields[] = ($value == 'null') ? "`$field`=$value" : "`$field`='$value'";
					$a_fields[] = "[$field]='$value'";
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
		
		$except_id = gOpt('except_id', $options, null);
		
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
		
		$debug = gOpt('debug', $options, false);
		$instruction = gOpt('instruction', $options, 'UNION');
		
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
	
		$fields = gOpt('fields', $options, null);
		$delim = gOpt('delim', $options, ',');
		$enclosed = gOpt('enclosed', $options, '"');
		$escaped = gOpt('escaped', $options, '\\');
		$lineend = gOpt('lineend', $options, '\\r\\n');
		$hasheader = gOpt('hasheader', $options, false);
		
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
		
		$delim = gOpt('delim', $options, ',');
		$enclosed = gOpt('enclosed', $options, '"');
		
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
		
		$string = str_replace("'", "''", $string);
		$string = str_replace("\0", "[NULL]", $string);
		return $string;
	}
}

?>
