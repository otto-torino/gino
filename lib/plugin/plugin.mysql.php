<?php

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
	 * @param $params: array(
	 * "connect"=>true,
	 * "host"=>"localhost",
	 * "user"=>"root",
	 * "password"=>"",
	 * "db_name"=>"db_name",
	 * "charset"=>"utf-8"
	 * )
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
	
	public function openConnection() {

		if($this->_dbconn = mysql_connect($this->_db_host, $this->_db_user, $this->_db_password)) {
			
			@mysql_select_db($this->_db_name, $this->_dbconn) OR die("ERROR MYSQL: ".mysql_error());
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
	 * Chiude connessioni non persistenti
	 */
	public function closeConnection() {

		if($this->_connection){
			mysql_close($this->_dbconn);
		}
	}
	
	/**
	 * Metodi per tabelle innodb
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
	// end
	
	/**
	 * Esecuzione della query (insert, update, delete)
	 * 
	 * @param string $qry	query
	 * @return boolean
	 */
	public function actionquery($qry) {
		
		if (!$this->_connection) {
			$this->openConnection();
		}
		$this->setsql($qry);
		$this->_qry = mysql_query($this->_sql);

		return $this->_qry ? true:false;
	}

	public function multiActionquery($qry) {
	
		$conn = mysqli_connect($this->_db_host, $this->_db_user, $this->_db_password, $this->_db_name);
		$this->setsql($qry);
		$this->_qry = mysqli_multi_query($conn, $this->_sql);

		return $this->_qry ? true:false;
	}

	/**
	 * Esecuzione della query (select)
	 * 
	 * @param string $qry	query
	 * @return array
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
	 * will free all memory associated with the result identifier result
	 */
	private function freeresult(){
	
		mysql_free_result($this->_qry);
	}
	
	/**
	 * Numero di record risultanti da un select
	 * 
	 * @param string $qry	query
	 * @result integer
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
	 * Numero di record interessati da una istruzione INSERT, UPDATE o DELETE
	 */
	public function affected() 
	{ 
		$this->_affected = mysql_affected_rows();
		return $this->_affected;
	}
	
	/**
	 * Last Id (INSERT, UPDATE)
	 * 
	 * mysql_insert_id() ritorna il valore generato da una colonna AUTO_INCREMENT a seguito di una query di INSERT o UPDATE.
	 * Il valore della funzione SQL LAST_INSERT_ID() di MySQL contiene sempre il più recente valore AUTO_INCREMENT generato e non è azzerato dalle query.
	 */
	public function getlastid($table='')
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
	 * Auto Increment Value
	 * ottiene il valore del campo AUTO_INCREMENT
	 * 
	 * @param string $table		nome della tabella
	 * @result integer
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
	
	public function tableexists($table){
		
		$query = "SHOW TABLES FROM `".$this->_db_name."`";
		$result = mysql_query($query);
		$data = mysql_num_rows($result);

		for ($i=0; $i<$data; $i++) {
			if(mysql_tablename($result, $i) == $table) return true;
		}
		return false;
	}
	
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
				$i++;
			}
			$this->freeresult();
			return $meta;
		}
	}
	
	/**
	 * Limit Command
	 *
	 * @param integer $range
	 * @param integer $offset
	 * @return string
	 * 
	 * @example $this->_db->limit(1, 0);
	 * 
	 * -> PostgreSQL:
	 * $string = "LIMIT $range OFFSET $offset";
	 */
	public function limit($range, $offset){
		
		$limit = "LIMIT $offset, $range";
		return $limit;
	}
	
	/**
	 * Concatenazione di campi
	 *
	 * @param array $sequence
	 * @return string
	 * 
	 * @example
	 * concat(array("lastname", "' '", "firstname"))
	 * $this->_db->concat(array("label", "' ('", "server", "')'"));
	 * 
	 * -> PostgreSQL
	 * $string = implode(' || ', $sequence);
	 * -> SQL Server
	 * $string = implode(' + ', $sequence);
	 * $concat = $string;
	 */
	public function concat($sequence){
		
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
}
?>
