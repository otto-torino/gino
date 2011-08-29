<?php
/*================================================================================
    Gino - a generic CMS framework
    Copyright (C) 2005  Otto Srl - written by Marco Guidotti

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

   For additional information: <opensource@otto.to.it>
================================================================================*/
class DB {

	private $_dbhost;
	private $_db;
	private $_dbuser;
	private $_dbpassword;
	private $_dbschema;
	private $_sql;	// query string
	private $_qry;	// results of query
	private $_numberrows;
	private $_dbconnection;
	private $_rows;
	private $_affected;
	private $_lastid;
	private $_dbresults = array();
	
	function __construct($db='') {
				
		$this->sethost(SERVER_NAME); 
		$this->setdb(DBNAME); 
		$this->setdbuser(DBUSER); 
		$this->setdbpassword(DBPWD);
		$this->setdbschema(DBSCHEMA);
		
		$this->setnumberrows(0);
		$this->setdbconnection(false);
		
		$this->opendbconnection();	// to activate endured the the logon at db
	}
	
	// Property Get & Set

	private function gethost() {
		return $this->_dbhost;
	}
	private function sethost($req_host) {
		$this->_dbhost = $req_host;
	}

	private function getdb() {
		return $this->_db;
	}
	private function setdb($req_db) {
		$this->_db = $req_db;
	}

	private function getdbuser() {
		return $this->_dbuser;
	}
	private function setdbuser($req_user) {
		$this->_dbuser = $req_user;
	}

	private function getdbpassword() {
		return $this->_dbpassword;
	}
	private function setdbpassword($req_password) {
		$this->_dbpassword = $req_password;
	}
	
	private function getdbschema() {
		return $this->_dbschema;
	}
	private function setdbschema($req_schema) {
		$this->_dbschema = $req_schema;
	}

	private function getsql() {
		return $this->_sql;
	}
	private function setsql($req_sql) {
		$this->_sql = $req_sql;
	}

	private function getnumberrows() {
		return $this->_numberrows;
	}
	private function setnumberrows($req_numberresults) {
		$this->_numberrows = $req_numberresults;
	}
	
	private function getdbconnection() {
		return $this->_dbconnection;
	}
	private function setdbconnection($req_dbconnection) {
		$this->_dbconnection = $req_dbconnection;
	}
	
	// controls for the transational query
	public function getInsertSwitch() {
		return $this->InsertSwitch;
	}
	public function setInsertSwitch($switch) {
		$this->InsertSwitch = $switch;
	}
	
	// end Get & Set
	
	private function opendbconnection() {

		if($this->_dbconnection = mysql_connect($this->_dbhost, $this->_dbuser, $this->_dbpassword)) {
			
			$this->setUtf8();
			@mysql_select_db($this->_db, $this->_dbconnection) OR die("ERROR MYSQL: ".mysql_error());
			$this->setdbconnection(true);
			return true;
		
		} else {
			die("ERROR DB: verify the parameters of connection");
			//die("ERROR MYSQL: ".mysql_error());	// debug
			//$this->setdbconnection(false);
			//return false;
		}
	}

	private function setUtf8() {
		$db_charset = mysql_query( "SHOW VARIABLES LIKE 'character_set_database'" );
		$charset_row = mysql_fetch_assoc( $db_charset );
		mysql_query( "SET NAMES '" . $charset_row['Value'] . "'" );
		unset( $db_charset, $charset_row );
	}

	private function closedbconnection() {

		if($this->_dbconnection){
			mysql_close($this->_dbconnection);
		}
	}
	
	/*
	// functions for innodb tables
	*/
	public function begin() {
		if (!$this->_dbconnection){
			$this->opendbconnection();
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
		if (!$this->_dbconnection) {
			$this->opendbconnection();
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
		if (!$this->_dbconnection) {
			$this->opendbconnection();
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
	
	public function table($table){
		
		if(!empty($this->_dbschema)) $table = $this->_dbschema.'.'.$table;
		
		return $table;
	}
	
	public function actionquery($qry) {
		// insert, update, delete
		
		if (!$this->_dbconnection) {
			$this->opendbconnection();
		}
		$this->setsql($qry);
		$this->_qry = mysql_query($this->_sql);

		return $this->_qry ? true:false;
	}

	public function multiActionquery($qry) {
	
		$conn = mysqli_connect( $this->_dbhost, $this->_dbuser, $this->_dbpassword, $this->_db );
		$this->setsql($qry);
		$this->_qry = mysqli_multi_query($conn, $this->_sql);

		return $this->_qry ? true:false;

	}

	public function selectquery($qry) {

		if(!$this->_dbconnection) {
			$this->opendbconnection();
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
		
	// will free all memory associated with the result identifier result
	private function freeresult(){
	
		mysql_free_result($this->_qry);
	}
	
	# number of records results of the select
	public function resultselect($qry)
	{
		if(!$this->_dbconnection) {
			$this->opendbconnection();
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
	
	# number of records changed for INSERT, UPDATE or DELETE
	public function affected() 
	{ 
		$this->_affected = mysql_affected_rows();
		return $this->_affected;
	}
	
	/**
	 * Last Id (INSERT | UPDATE)
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
	 * 
	 * ottiene il valore del campo AUTO_INCREMENT
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
		
		$query = "SHOW TABLES FROM `".$this->_db."`";
		$result = mysql_query($query);
		$data = mysql_num_rows($result);

		for ($i=0; $i<$data; $i++) {
			if(mysql_tablename($result, $i) == $table) return true;
		}
		return false;
	}
	
	public function fieldInformations($table) {
	
		if($this->_dbconnection) {
			$this->opendbconnection();
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
	
	/*
	public function field_insert($name, $value, $comma=true){
		
		if(!empty($value) AND !is_int($value))
		{
			$value = "'$value'";
			
			if($comma){
				$name .= ',';
				$value .= ',';
			}
			return array($name, $value);
		}
		
		if(empty($value))
		{
			$f_name = '';
			$f_value = '';
		}
		else
		{
			$f_name .= ',';
			$f_value .= ',';
		}
		return array($f_name, $f_value);
	}
	*/
	
	/**
	 * Limit Command
	 *
	 * @param integer $range
	 * @param integer $offset
	 * @return string
	 * 
	 * @example $this->_db->limit(1, 0);
	 */
	public function limit($range, $offset){
		
		$limit = "LIMIT $offset, $range";
		//$string = "LIMIT $range OFFSET $offset";	// PostgreSQL
		
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
	 */
	public function concat($sequence){
		
		if(is_array($sequence))
		{
			if(sizeof($sequence) > 1)
			{
				
				$string = implode(',', $sequence);
				$concat = "CONCAT($string)";
				
				//$string = implode(' || ', $sequence);	// PostgreSQL
				//$string = implode(' + ', $sequence);	// SQL Server
				//$concat = $string;
			}
			else $concat = $sequence[0];
		}
		else $concat = $sequence;
		
		return $concat;
	}

	public function dumpDatabase($file) {

		$tables = mysql_list_tables($this->_db);
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
