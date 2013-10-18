<?php
/**
 * @file plugin.mssql.php
 * @brief Contiene la classe mssql
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria di connessione ai database MySQL
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class mssql implements DbManager {

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
		
		$this->setnumberrows(0);
		$this->setconnection(false);
		
		if($params["connect"]===true) $this->openConnection();
	}
	
	// query string
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
	 * @see DbManager::openConnection()
	 */
	public function openConnection() {

		if($this->_dbconn = mssql_connect($this->_db_host, $this->_db_user, $this->_db_password)) {
			
			@mssql_select_db($this->_db_name, $this->_dbconn) OR die("ERROR MYSQL: ".mysql_error());
			if($this->_db_charset=='utf-8') $this->setUtf8();
			$this->setconnection(true);
			return true;
		} else {
			die("ERROR DB: verify the parameters of connection");
		}
	}

	private function setUtf8() {
		$db_charset = mssql_query("SHOW VARIABLES LIKE 'character_set_database'");
		$charset_row = mssql_fetch_assoc($db_charset);
		mssql_query("SET NAMES '" . $charset_row['Value'] . "'");
		unset($db_charset, $charset_row);
	}

	/**
	 * Chiude le connessioni non persistenti a un server MySQL
	 * 
	 * @see DbManager::closeConnection()
	 */
	public function closeConnection() {

		if($this->_connection){
			mssql_close($this->_dbconn);
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
		$this->_qry = mssql_query($this->_sql);
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
		$this->_qry = mssql_query($this->_sql);
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
		$this->_qry = mssql_query($this->_sql);
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
		$this->_qry = mssql_query($this->_sql);

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
	public function selectquery($qry) {

		if(!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);
		$this->_qry = mssql_query($this->_sql);
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$this->_dbresults = array();
			
			$this->setnumberrows(mssql_num_rows($this->_qry));
			if($this->_numberrows > 0){
				while($this->_rows=mssql_fetch_assoc($this->_qry))
				{
					$this->_dbresults[]=$this->_rows;
				}
			}
			$this->freeresult();
			return $this->_dbresults;
		}
	}
		
	/**
	 * Libera tutta la memoria utilizzata dal Result Set 
	 */
	private function freeresult(){
	
		mssql_free_result($this->_qry);
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
		$this->_qry = mssql_query($this->_sql);
		if (!$this->_qry) {
			return false;
		} else {
			$this->setnumberrows(mssql_num_rows($this->_qry));
			return $this->_numberrows;
		}
	}
	
	/**
	 * @see DbManager::affected()
	 */
	public function affected() 
	{ 
		$this->_affected = mssql_rows_affected();
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
    		$res = mssql_query("SELECT SCOPE_IDENTITY() AS id"); 
    		if($row = mssql_fetch_array($res, MSSQL_ASSOC)) { 
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

		$res = mssql_query("SELECT IDENT_CURRENT('$table') AS NextId");
		$a = $this->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$auto_increment = $b['NextId'];
			}
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
		$this->_qry = mssql_query($this->_sql);
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$meta = array();
			$i = 0;
			while($i < mssql_num_fields($this->_qry)) {
				$meta[$i] = mssql_fetch_field($this->_qry, $i);
				$meta[$i]->length = mssql_field_length($this->_qry, $i);
				$i++;
			}
			$this->freeresult();
			return $meta;
		}
	}
	
	/**
	 * @see DbManager::limit()
	 */
	public function limit($range, $offset){
		
		// SELECT TOP 20 * ...
		// BETWEEN @offset+1 AND @offset+@count;
		
		if(!$offset)
			$limit = "TOP $range";
		else
			$limit = "";
		
		//$limit = "BETWEEN $offset AND $range";
		return $limit;
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

		$tables = mysql_list_tables($this->_db_name);
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

		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".$this->_db_name."' AND TABLE_NAME='$table'";
		$res = mssql_query($query);

		while($row = mysql_fetch_array($res)) {
			
			$query_key = "SELECT CONSTRAINT_NAME, UNIQUE_CONSTRAINT_NAME FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS";
			$a = $this->selectquery($query_key);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$fk = $b['CONSTRAINT_NAME'];
					$uq = $b['UNIQUE_CONSTRAINT_NAME'];
				}
			}
/*
CONSTRAINT_NAME  UNIQUE_CONSTRAINT_NAME
---------------  ----------------------------
FK_UQ            UQ
*/
					
			//preg_match("#(\w+)\((\'[0-9a-zA-Z-_,.']+\')\)#", $row['COLUMN_TYPE'], $matches_enum);
			//preg_match("#(\w+)\((\d+),?(\d+)?\)#", $row['COLUMN_TYPE'], $matches);
			$fields[$row['COLUMN_NAME']] = array(
				"order"=>$row['ORDINAL_POSITION'],
				"default"=>$row['COLUMN_DEFAULT'],
				"null"=>$row['IS_NULLABLE'],
				"type"=>$row['DATA_TYPE'],
				"max_length"=>$row['CHARACTER_MAXIMUM_LENGTH'],
				//"n_int"=>isset($matches[2]) ? $matches[2] : 0,
				//"n_precision"=>isset($matches[3]) ? $matches[3] : 0,
				"n_int"=>$row['NUMERIC_PRECISION'],
				"n_precision"=>$row['NUMERIC_PRECISION_RADIX'],
				"key"=>$row['COLUMN_KEY'],
				"extra"=>$row['EXTRA'] ,
				"enum"=>isset($matches_enum[2]) ? $matches_enum[2] : null	/////
			);
			
			$primary = $this->getInformationKey($row['COLUMN_NAME'], $table);
			$keys = $this->getInformationKey($row['COLUMN_NAME'], $table, 'foreign');
			
			if($primary) $structure['primary_key'] = $primary;
			if($keys) $structure['keys'][] = $keys;
		}
		$structure['fields'] = $fields;

		return $structure;
	}
	
	/**
	 * 
	 * 
	 * @param string $column
	 * @param string $table
	 * @param string $key nome della chiave da ricercare (primary, foreign, unique)
	 * @return mixed
	 */
	private function getInformationKey($column, $table, $key=null) {
		
		if($key == 'foreign')
			$key_name = 'FOREIGN KEY';
		elseif($key == 'unique')
			$key_name = 'UNIQUE KEY';
		else
			$key_name = 'PRIMARY KEY';
		
		$array = array();
		$query = "
			SELECT K.COLUMN_NAME, K.CONSTRAINT_NAME
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
				$array[] = $b['COLUMN_NAME'];
			}
		}
		
		if($key == 'primary')
			return $array[0];
		else
			return $array;
	}

	/**
	 * @see DbManager::getFieldsName()
	 */
	public function getFieldsName($table) {

		$fields = array();
		
		$query = "SELECT COLUMN_NAME AS Field
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_SCHEMA = '".DB_SCHEMA."'
		AND TABLE_NAME = '$table'";
		
		$res = mssql_query($query);
		while($row = mssql_fetch_assoc($res)) {
			$results[] = $row;
		}
		mssql_free_result($res);

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
	public function query($fields, $tables, $where=null, $order=null, $limit=null, $debug=false) {

		$qfields = is_array($fields) ? implode(",", $fields):$fields;
		$qtables = is_array($tables) ? implode(",", $tables):$tables;
		$qwhere = $where ? "WHERE ".$where : "";
		$qorder = $order ? "ORDER BY $order" : "";
		$qlimit = count($limit) ? $this->limit($limit[1],$limit[0]) : "";

		$query = "SELECT $qfields FROM $qtables $qwhere $qorder $qlimit";
		
		if($debug) echo $query;
		
		return $query;
	}

	/**
	 * @see DbManager::select()
	 */
	public function select($fields, $tables, $where=null, $order=null, $limit=null, $debug=false) {

		$query = $this->query($fields, $tables, $where, $order, $limit, $debug);
		
		if($debug) echo $query;
		
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
				$a_values[] = ($value !== null) ? "'$value'" : null;	/////// VERIFICARE
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
				//$a_fields[] = ($value == 'null') ? "`$field`=$value" : "`$field`='$value'";
				$a_fields[] = "`$field`='$value'";
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
