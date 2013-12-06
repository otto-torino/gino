<?php
/**
 * @file plugin.mysql.php
 * @brief Contiene la classe mysql
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
class mysql implements DbManager {

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
		
		$this->_range = null;
		$this->_offset = null;
		
		$this->setnumberrows(0);
		$this->setconnection(false);
		
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
	 * Esegue la query
	 * 
	 * @param string $query
	 * @return array
	 */
	private function execQuery($query=null) {
		
		if(!$query) $query = $this->_sql;
		
		$exec = mysql_query($query);
		return $exec;
	}
	
	/**
	 * @see DbManager::openConnection()
	 */
	public function openConnection() {

		if($this->_dbconn = mysql_connect($this->_db_host, $this->_db_user, $this->_db_password)) {
			
			@mysql_select_db($this->_db_name, $this->_dbconn) OR die("ERROR DB: ".mysql_error());
			if($this->_db_charset=='utf-8') $this->setUtf8();
			$this->setconnection(true);
			return true;
		} else {
			die("ERROR DB: verify the parameters of connection");	// debug -> die("ERROR MYSQL: ".mysql_error());
		}
	}

	private function setUtf8() {
		$db_charset = mysql_query("SHOW VARIABLES LIKE 'character_set_database'");
		$charset_row = mysql_fetch_assoc($db_charset);
		mysql_query("SET NAMES '" . $charset_row['Value'] . "'");
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
		$this->_qry = mysql_query($this->_sql);
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
		$this->_qry = mysql_query($this->_sql);
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
		$this->_qry = mysql_query($this->_sql);
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
		$this->_qry = mysql_query($this->_sql);

		return $this->_qry ? true:false;
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
		$this->_qry = mysql_query($this->_sql);
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
		$this->_qry = mysql_query($this->_sql);
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
		$a = $this->selectquery($query);
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
		
		$query = "SHOW TABLES FROM `".$this->_db_name."`";
		$result = mysql_query($query);
		$data = mysql_num_rows($result);

		for ($i=0; $i<$data; $i++) {
			if(mysql_tablename($result, $i) == $table) return true;
		}
		return false;
	}
	
	/**
	 * @see DbManager::fieldInformations()
	 * 
	 * Come tipo di dato di un campo, MySQL ritorna: int, blob, string, date
	 */
	public function fieldInformations($table) {
	
		if($this->_connection) {
			$this->openConnection();
		}
		$this->setsql("SELECT * FROM ".$table." LIMIT 0,1");
		$this->_qry = mysql_query($this->_sql);
		
		if(!$this->_qry) {
			return false;
		} else {
			// initialize array results
			$meta = array();
			$i = 0;
			while($i < mysql_num_fields($this->_qry)) {
				$meta[$i] = mysql_fetch_field($this->_qry, $i);
				$meta[$i]->length = mysql_field_len($this->_qry, $i);
				
				if($meta[$i]->type == 'string')
					$meta[$i]->type = 'char';
				elseif($meta[$i]->type == 'blob')
					$meta[$i]->type = 'text';
				
				$i++;
			}
			$this->freeresult();
			return $meta;
		}
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
		
		$alias = gOpt('alias', $options, null);
		
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

		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$this->_db_name."' AND TABLE_NAME = '$table'";
		$res = mysql_query($query);

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
				"extra"=>$row['EXTRA'] ,
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

		$res = mysql_query($query);
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
		$distinct = gOpt('distinct', $options, null);
		$limit = gOpt('limit', $options, null);
		$debug = gOpt('debug', $options, false);
		
		$qfields = is_array($fields) ? implode(",", $fields) : $fields;
		$qtables = is_array($tables) ? implode(",", $tables) : $tables;
		$qwhere = $where ? "WHERE ".$where : "";
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
		
		$query = "SELECT $qfields FROM $qtables $qwhere $qorder $qlimit";
		
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
						$a_fields[] = "`$field`=".$value['sql'];
				}
				else
				{
					$a_values[] = ($value !== null) ? "'$value'" : null;	/////// VERIFICARE
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
					$a_fields[] = "`$field`='$value'";
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
		
		return mysql_real_escape_string($string);
	}
}

?>
